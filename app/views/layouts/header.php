<?php
/**
 * Layout: document head + topbar.
 * All styling comes from the central theme (app/config/theme.php) plus
 * public/css/app.css. Do NOT add <style> blocks to views.
 */
require_once __DIR__ . '/theme.php';

/**
 * Page titles are resolved centrally from the request path so no view has to
 * repeat header markup. A controller may still override with $pageTitle.
 */
$ui_titles = [
    '/dashboard'      => ['Dashboard', 'Live accounting overview'],
    '/settlements'    => ['Seller settlements', 'Payables and settlement runs'],
    '/reports'        => ['Reports', 'Analytics and statutory reports'],
    '/parties'        => ['Parties', 'Customers, vendors and sellers'],
    '/items'          => ['Items', 'Products, services and pricing'],
    '/invoices'       => ['Tax invoices', 'Issue and track sales invoices'],
    '/creditNotes'    => ['Credit notes', 'Returns and adjustments'],
    '/payments/payIn' => ['Pay in', 'Money received from parties'],
    '/payments/payOut'=> ['Pay out', 'Money paid to parties and sellers'],
    '/payments'       => ['Payments', 'Receipts and disbursements'],
    '/bankAccounts'   => ['Bank accounts', 'Accounts and balances'],
    '/settings'       => ['Settings', 'Taxes, units, currency and branding'],
];
$ui_request  = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$ui_resolved = ['Accounting workspace', 'Billing, invoicing and settlements'];
foreach ($ui_titles as $ui_path => $ui_meta) {
    if (strpos($ui_request, $ui_path) !== false) { $ui_resolved = $ui_meta; break; }
}
$ui_page_title    = isset($pageTitle) ? $pageTitle : $ui_resolved[0];
$ui_page_subtitle = isset($pageSubtitle) ? $pageSubtitle : $ui_resolved[1];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="<?php echo ui('dark'); ?>">
    <title><?php echo htmlspecialchars($ui_page_title); ?> &middot; <?php echo ui('brand-name'); ?></title>

    <link rel="icon" href="<?php echo APP_URL; ?>/assets/images/favicon.ico">

       <!-- Third-party libraries (bundled locally with the template) -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/libs/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/libs/apexcharts/apexcharts.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/libs/flatpickr/flatpickr.min.css">
    <!-- ApexCharts JS is loaded here (head), not in footer.php, so it is
         guaranteed to be defined before any page's inline <script> runs -
         e.g. the Dashboard's chart-init code, which used to sit earlier in
         the document than footer.php's <script> tag and could fire before
         `ApexCharts` existed. -->
    <script src="<?php echo APP_URL; ?>/assets/libs/apexcharts/apexcharts.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Central design tokens (generated from app/config/theme.php) -->
    <?php ui_theme_css(); ?>

    <!-- Single global stylesheet for the whole application -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/app.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/print-document.css">
</head>
<body>

<?php require_once __DIR__ . '/sidebar.php'; ?>

<div class="app-overlay" id="appOverlay" aria-hidden="true"></div>

<div class="app-main">

    <header class="app-navbar" aria-label="Application header">
        <div class="navbar-left">
            <button class="icon-btn d-lg-none" id="sidebarToggle" type="button"
                    aria-label="Open navigation" aria-controls="appSidebar" aria-expanded="false">
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>
            <button class="icon-btn d-none d-lg-inline-flex" id="sidebarMini" type="button" aria-label="Collapse navigation">
                <i class="bi bi-chevron-bar-left" aria-hidden="true"></i>
            </button>
            <div class="navbar-heading ms-1">
                <p class="navbar-title"><?php echo htmlspecialchars($ui_page_title); ?></p>
                <p class="navbar-subtitle"><?php echo htmlspecialchars($ui_page_subtitle); ?></p>
            </div>
        </div>

        <div class="navbar-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" id="globalSearch" placeholder="Search invoices, parties, items&hellip;" aria-label="Search">
        </div>

        <div class="navbar-right">
            <div class="dropdown">
                <button class="icon-btn is-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Create new">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-header">Quick actions</li>
                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/invoices/create"><i class="bi bi-receipt"></i> New tax invoice</a></li>
                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/creditNotes/create"><i class="bi bi-journal-minus"></i> New credit note</a></li>
                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/parties/create"><i class="bi bi-person-plus"></i> New party</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/payments/payIn"><i class="bi bi-box-arrow-in-down"></i> Record pay in</a></li>
                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/payments/payOut"><i class="bi bi-box-arrow-up"></i> Record pay out</a></li>
                </ul>
            </div>

            <a class="icon-btn d-none d-sm-inline-flex" href="<?php echo APP_URL; ?>/settings/customization" aria-label="Settings" title="Settings">
                <i class="bi bi-gear" aria-hidden="true"></i>
            </a>

            <div class="dropdown">
                <button class="navbar-user dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="navbar-user-avatar">AD</span>
                    <span class="navbar-user-name">Administrator</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-header">Signed in</li>
                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/settings/customization"><i class="bi bi-sliders"></i> Preferences</a></li>
                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/settings/currencies"><i class="bi bi-currency-exchange"></i> Currency</a></li>
                </ul>
            </div>
        </div>
    </header>

    <main class="app-content" id="mainContent">
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger d-flex align-items-start gap-2 shadow-sm">
                <i class="bi bi-shield-exclamation fs-5"></i>
                <div><?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success d-flex align-items-start gap-2 shadow-sm">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div><?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
            </div>
        <?php endif; ?>
