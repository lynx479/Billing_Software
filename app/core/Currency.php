<?php
/**
 * Global helpers: dynamic currency (Settings > Customization) + system date/time.
 * Active currency = acc_settings.default_currency (currency_code), else
 * the acc_currencies row flagged is_default.
 */

function app_settings_map() {
    static $settings = null;
    if ($settings !== null) return $settings;
    $settings = [];
    try {
        $db = (new Model())->getDb();
        foreach ($db->query("SELECT setting_key, setting_value FROM acc_settings")->fetchAll() as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
    } catch (Exception $e) { $settings = []; }
    return $settings;
}

function app_currency() {
    static $currency = null;
    if ($currency !== null) return $currency;
    $currency = ['currency_code' => 'INR', 'currency_name' => 'Indian Rupee', 'symbol' => "\u{20B9}", 'exchange_rate' => 1];
    try {
        $db = (new Model())->getDb();
        $settings = app_settings_map();
        $code = trim($settings['default_currency'] ?? '');
        if ($code !== '') {
            $stmt = $db->prepare("SELECT * FROM acc_currencies WHERE currency_code = ? AND status = 1 LIMIT 1");
            $stmt->execute([$code]);
            $row = $stmt->fetch();
            if ($row) { $currency = $row; return $currency; }
        }
        $row = $db->query("SELECT * FROM acc_currencies WHERE status = 1 ORDER BY is_default DESC, currency_id ASC LIMIT 1")->fetch();
        if ($row) $currency = $row;
    } catch (Exception $e) { /* fallback */ }
    return $currency;
}

/** Active currency symbol. */
function cur_symbol() { $c = app_currency(); return $c['symbol'] ?: ($c['currency_code'] ?? ''); }

/** Active currency code. */
function cur_code() { $c = app_currency(); return $c['currency_code'] ?? 'INR'; }

/** Amount formatted with the active currency symbol. */
function money($amount, $decimals = 2) { return cur_symbol() . ' ' . number_format((float)$amount, $decimals); }

/**
 * Configured application timezone (Settings > Customization > Timezone).
 * Falls back to Asia/Kolkata (the software targets Indian GST filings).
 * public/index.php calls date_default_timezone_set(app_timezone()) on every
 * request, so date()/time()/strtotime() are correct everywhere by default -
 * this was previously unset, which caused all "current time" displays to
 * silently render in the server's default (often UTC) timezone.
 */
function app_timezone() {
    static $tz = null;
    if ($tz !== null) return $tz;
    $settings = app_settings_map();
    $configured = trim($settings['timezone'] ?? '');
    if ($configured !== '' && in_array($configured, timezone_identifiers_list(), true)) {
        $tz = $configured;
    } else {
        $tz = 'Asia/Kolkata';
    }
    return $tz;
}

/** 24-hour system date/time string, e.g. "07 Sep 2026, 14:05:30". */
function sys_datetime($format = 'd M Y, H:i:s') { return date($format); }
function sys_timezone() { return date_default_timezone_get(); }

/** Just the date part, 'd M Y'. */
function sys_date($timestamp = null) { return date('d M Y', $timestamp ? strtotime($timestamp) : time()); }

/** Just the 24-hour time part, 'H:i'. */
function sys_time($timestamp = null) { return date('H:i', $timestamp ? strtotime($timestamp) : time()); }

/**
 * System date + time badge (24-hour clock). Live-ticking on create screens;
 * on previews pass the stored document timestamp to show when it was recorded.
 */
function sys_time_badge($label = 'System Date & Time', $timestamp = null) {
    $ts = $timestamp ? strtotime($timestamp) : time();
    $offset = (int)date('Z');
    $live = $timestamp ? 'false' : 'true';
    $id = 'sysClock_' . substr(md5(uniqid('', true)), 0, 8);
    $html  = '<div class="small text-muted mt-1 d-flex align-items-center gap-1 flex-wrap">';
    $html .= '<i class="bi bi-clock-history"></i> <span class="fw-semibold">' . htmlspecialchars($label) . ':</span> ';
    $html .= '<span id="' . $id . '" data-ts="' . ($ts + $offset) . '" data-live="' . $live . '">' . date('d M Y, H:i:s', $ts) . '</span> ';
    $html .= '<span class="text-muted">(' . htmlspecialchars(sys_timezone()) . ')</span></div>';
    $html .= '<script>(function(){var el=document.getElementById("' . $id . '");if(!el||el.dataset.live!=="true")return;'
           . 'var t=parseInt(el.dataset.ts,10)*1000;setInterval(function(){t+=1000;var d=new Date(t);'
           . 'var mn=["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];'
           . 'var h=d.getUTCHours();var p=function(n){return n<10?"0"+n:n;};'
           . 'el.textContent=p(d.getUTCDate())+" "+mn[d.getUTCMonth()]+" "+d.getUTCFullYear()+", "+p(h)+":"+p(d.getUTCMinutes())+":"+p(d.getUTCSeconds());},1000);})();</script>';
    return $html;
}

/**
 * Balance colour convention used across the app:
 *   - green  -> customer owes the company (company will receive money)
 *   - red    -> the company owes the customer (shown WITHOUT a minus sign)
 *   - blue   -> settled / zero balance
 * Pass a signed amount where positive = customer owes company (standard
 * "balance due" direction) and negative = company owes customer (e.g. an
 * unadjusted credit note, or a refundable advance).
 */
function balance_color_class($signedAmount) {
    $signedAmount = (float)$signedAmount;
    if (abs($signedAmount) < 0.005) return 'text-primary-balance';
    return $signedAmount > 0 ? 'text-success-balance' : 'text-danger-balance';
}

function balance_badge($signedAmount, $decimals = 2) {
    $signedAmount = (float)$signedAmount;
    $class = balance_color_class($signedAmount);
    $display = number_format(abs($signedAmount), $decimals);
    return '<span class="fw-bold ' . $class . '">' . cur_symbol() . ' ' . $display . '</span>';
}
