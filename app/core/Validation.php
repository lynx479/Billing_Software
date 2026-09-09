<?php
/**
 * Shared validation helpers used by Invoice / Credit Note item processing.
 * Centralised so frontend (JS) and backend (PHP) apply the exact same rule:
 *   - Weight-type units (KG etc.) may take decimal quantities.
 *   - Every other unit (pieces, numbers, boxes, service, time...) must be a
 *     whole number - decimals are rejected.
 */

/** Map of unit_symbol => unit_type, cached per-request. */
function unit_type_map() {
    static $map = null;
    if ($map !== null) return $map;
    $map = [];
    try {
        $db = (new Model())->getDb();
        foreach ($db->query("SELECT unit_symbol, unit_type FROM acc_units")->fetchAll() as $u) {
            $map[strtoupper(trim($u['unit_symbol']))] = $u['unit_type'];
        }
    } catch (Exception $e) { $map = []; }
    return $map;
}

/** Whether a given unit symbol permits fractional (decimal) quantities. */
function unit_allows_decimal($unitSymbol) {
    $map = unit_type_map();
    $type = $map[strtoupper(trim((string)$unitSymbol))] ?? null;
    // Weight (KG etc.) and Volume based units may be fractional.
    // Everything else (Quantity/Pieces, Service, Time, or unknown units)
    // defaults to whole numbers only.
    return in_array($type, ['Weight', 'Volume'], true);
}

/**
 * Validate a quantity value against its unit's rule.
 * Returns ['ok' => bool, 'value' => float, 'message' => string|null]
 */
function validate_item_quantity($rawQuantity, $unitSymbol) {
    $qty = (float)$rawQuantity;
    if ($qty <= 0) {
        return ['ok' => false, 'value' => $qty, 'message' => 'Quantity must be greater than zero.'];
    }
    if (!unit_allows_decimal($unitSymbol) && abs($qty - round($qty)) > 0.0001) {
        return [
            'ok' => false,
            'value' => $qty,
            'message' => "Quantity for unit \"{$unitSymbol}\" must be a whole number (decimals are only allowed for weight-based units)."
        ];
    }
    return ['ok' => true, 'value' => $qty, 'message' => null];
}

/** Validate an entire items[] array (as posted by the create forms). Throws Exception on first failure. */
function validate_items_quantities(array $items) {
    foreach ($items as $i => $item) {
        if (empty($item['item_name'])) continue;
        $unit = $item['unit'] ?? 'PCS';
        $result = validate_item_quantity($item['quantity'] ?? 0, $unit);
        if (!$result['ok']) {
            throw new Exception('Line ' . ((int)$i + 1) . ': ' . $result['message']);
        }
    }
    return true;
}
