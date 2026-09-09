<?php
/**
 * Layout: primary navigation.
 * Markup only — every style lives in public/css/app.css (tokens in app/config/theme.php).
 */
require_once __DIR__ . '/theme.php';

$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

/** Is the current request inside a section? */
if (!function_exists('nav_has')) {
    function nav_has($needle, $exclude = null)
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if (strpos($uri, $needle) === false) {
            return false;
        }
        if ($exclude !== null && strpos($uri, $exclude) !== false) {
            return false;
        }
        return true;
    }
}
/** "active" class helper */
if (!function_exists('nav_active')) {
    function nav_active($needle, $exclude = null)
    {
        return nav_has($needle, $exclude) ? ' active' : '';
    }
}
/** aria-expanded / show helpers for collapsible groups */
if (!function_exists('nav_open')) {
    function nav_open($needles)
    {
        foreach ((array) $needles as $needle) {
            if (nav_has($needle)) {
                return true;
            }
        }
        return false;
    }
}

$openSettlements = nav_open(['/settlements']);
$openReports     = nav_open(['/reports']);
$openInvoicing   = nav_open(['/parties', '/items', '/invoices', '/creditNotes', '/payments', '/bankAccounts', '/settings']) || !nav_open(['/dashboard', '/reports', '/settlements']);
$openParties     = nav_open(['/parties']);
$openGenerate    = nav_open(['/invoices', '/creditNotes', '/payments']) || $openInvoicing;
$openSettings    = nav_open(['/settings']);
?>
<aside class="app-sidebar" id="appSidebar" aria-label="Primary navigation">

    <a class="sidebar-brand" href="<?php echo APP_URL; ?>/dashboard">
        <span class="sidebar-brand-mark"><i class="bi <?php echo ui('brand-icon'); ?>" aria-hidden="true"></i></span>
        <span>
            <span class="sidebar-brand-name"><?php echo ui('brand-name'); ?></span>
            <span class="sidebar-brand-tag"><?php echo ui('brand-tagline'); ?></span>
        </span>
    </a>

    <div class="sidebar-scroll">

        <div class="sidebar-section">
            <div class="sidebar-section-title">Overview</div>
            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a class="sidebar-link<?php echo nav_active('/dashboard'); ?>" href="<?php echo APP_URL; ?>/dashboard">
                        <i class="bi bi-grid-1x2-fill" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-toggle="collapse" href="#navSettlements" role="button"
                       aria-expanded="<?php echo $openSettlements ? 'true' : 'false'; ?>" aria-controls="navSettlements">
                        <i class="bi bi-wallet2" aria-hidden="true"></i>
                        <span>Settlements</span>
                        <i class="bi bi-chevron-right menu-arrow" aria-hidden="true"></i>
                    </a>
                    <div class="collapse<?php echo $openSettlements ? ' show' : ''; ?>" id="navSettlements">
                        <ul class="sidebar-menu sidebar-submenu">
                            <li class="sidebar-item">
                                <a class="sidebar-link<?php echo nav_active('/settlements'); ?>" href="<?php echo APP_URL; ?>/settlements">
                                    <i class="bi bi-dot" aria-hidden="true"></i>
                                    <span>Seller settlement</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-toggle="collapse" href="#navReports" role="button"
                       aria-expanded="<?php echo $openReports ? 'true' : 'false'; ?>" aria-controls="navReports">
                        <i class="bi bi-bar-chart-line-fill" aria-hidden="true"></i>
                        <span>Reports</span>
                        <i class="bi bi-chevron-right menu-arrow" aria-hidden="true"></i>
                    </a>
                    <div class="collapse<?php echo $openReports ? ' show' : ''; ?>" id="navReports">
                        <ul class="sidebar-menu sidebar-submenu">
                            <?php
                            $reportLinks = [
                              //  'sales'              => 'Sales report',
                              //  'users'              => 'User report',
                              //  'financial'          => 'Financial report',
                              //  'commission'         => 'Commission report',
                              //  'sellerSettlements'  => 'Seller settlement report',
                              //  'subscriptions'      => 'Subscription report',
                                'ads'                => 'Ads report',
                                'reconciliation'     => 'Reconciliation report',
                                'gstr1'              => 'GSTR-1 report',
                            ];
                            foreach ($reportLinks as $slug => $label): ?>
                                <li class="sidebar-item">
                                    <a class="sidebar-link<?php echo nav_active('/reports/' . $slug); ?>"
                                       href="<?php echo APP_URL; ?>/reports/<?php echo $slug; ?>">
                                        <i class="bi bi-dot" aria-hidden="true"></i>
                                        <span><?php echo $label; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-title">Invoicing</div>
            <ul class="sidebar-menu">

                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-toggle="collapse" href="#navParties" role="button"
                       aria-expanded="<?php echo $openParties ? 'true' : 'false'; ?>" aria-controls="navParties">
                        <i class="bi bi-people" aria-hidden="true"></i>
                        <span>Parties</span>
                        <i class="bi bi-chevron-right menu-arrow" aria-hidden="true"></i>
                    </a>
                    <div class="collapse<?php echo $openParties ? ' show' : ''; ?>" id="navParties">
                        <ul class="sidebar-menu sidebar-submenu">
                            <li class="sidebar-item">
                                <a class="sidebar-link<?php echo nav_active('/parties/sellers'); ?>" href="<?php echo APP_URL; ?>/parties/sellers">
                                    <i class="bi bi-dot" aria-hidden="true"></i>
                                    <span>Seller parties</span>
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link<?php echo nav_active('/parties/created'); ?>" href="<?php echo APP_URL; ?>/parties/created">
                                    <i class="bi bi-dot" aria-hidden="true"></i>
                                    <span>Created parties</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link<?php echo nav_active('/items'); ?>" href="<?php echo APP_URL; ?>/items">
                        <i class="bi bi-box-seam" aria-hidden="true"></i>
                        <span>Items</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-toggle="collapse" href="#navGenerate" role="button"
                       aria-expanded="<?php echo $openGenerate ? 'true' : 'false'; ?>" aria-controls="navGenerate">
                        <i class="bi bi-file-earmark-plus" aria-hidden="true"></i>
                        <span>Generate</span>
                        <i class="bi bi-chevron-right menu-arrow" aria-hidden="true"></i>
                    </a>
                    <div class="collapse<?php echo $openGenerate ? ' show' : ''; ?>" id="navGenerate">
                        <ul class="sidebar-menu sidebar-submenu">
                            <li class="sidebar-item">
                                <a class="sidebar-link<?php echo nav_active('/invoices', '/create'); ?>" href="<?php echo APP_URL; ?>/invoices">
                                    <i class="bi bi-dot" aria-hidden="true"></i>
                                    <span>Tax invoices</span>
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link<?php echo nav_active('/creditNotes', '/create'); ?>" href="<?php echo APP_URL; ?>/creditNotes">
                                    <i class="bi bi-dot" aria-hidden="true"></i>
                                    <span>Credit notes</span>
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link<?php echo nav_active('/payments/payIn'); ?>" href="<?php echo APP_URL; ?>/payments/payIn">
                                    <i class="bi bi-dot" aria-hidden="true"></i>
                                    <span>Record pay in</span>
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link<?php echo nav_active('/payments/payOut'); ?>" href="<?php echo APP_URL; ?>/payments/payOut">
                                    <i class="bi bi-dot" aria-hidden="true"></i>
                                    <span>Record pay out</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link<?php echo nav_active('/bankAccounts'); ?>" href="<?php echo APP_URL; ?>/bankAccounts">
                        <i class="bi bi-bank" aria-hidden="true"></i>
                        <span>Bank accounts</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-title">Configuration</div>
            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a class="sidebar-link" data-bs-toggle="collapse" href="#navSettings" role="button"
                       aria-expanded="<?php echo $openSettings ? 'true' : 'false'; ?>" aria-controls="navSettings">
                        <i class="bi bi-sliders" aria-hidden="true"></i>
                        <span>Settings</span>
                        <i class="bi bi-chevron-right menu-arrow" aria-hidden="true"></i>
                    </a>
                    <div class="collapse<?php echo $openSettings ? ' show' : ''; ?>" id="navSettings">
                        <ul class="sidebar-menu sidebar-submenu">
                            <?php
                            $settingLinks = [
                                'taxes'         => 'Taxes',
                                'categories'    => 'Categories',
                                'units'         => 'Units',
                                'currencies'    => 'Currencies',
                                'customization' => 'Customization',
                            ];
                            foreach ($settingLinks as $slug => $label): ?>
                                <li class="sidebar-item">
                                    <a class="sidebar-link<?php echo nav_active('/settings/' . $slug); ?>"
                                       href="<?php echo APP_URL; ?>/settings/<?php echo $slug; ?>">
                                        <i class="bi bi-dot" aria-hidden="true"></i>
                                        <span><?php echo $label; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <div class="sidebar-footer">
        <span class="sidebar-footer-avatar"><i class="bi bi-person-fill" aria-hidden="true"></i></span>
        <span class="sidebar-footer-info">
            <span class="sidebar-footer-name">Administrator</span>
            <span class="sidebar-footer-meta">Accounting module</span>
        </span>
    </div>
</aside>
