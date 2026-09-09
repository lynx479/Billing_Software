<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lx_accounting');

// Check both local HTTPS and tunnel forwarded proto
$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
    (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
);

$protocol = $isHttps ? 'https' : 'http';

define(
    'APP_URL',
    $protocol . '://' . $_SERVER['HTTP_HOST'] . '/accounting/public'
);