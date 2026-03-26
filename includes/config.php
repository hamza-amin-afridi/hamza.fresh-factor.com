<?php
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'freshfac_HamzaDb');
if (!defined('DB_USER')) define('DB_USER', 'freshfac_HamzaUser');
if (!defined('DB_PASS')) define('DB_PASS', 'Ha1994181@');

$__host = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : '';
$__is_live_subdomain = ($__host === 'hamza.fresh-factor.com' || str_ends_with($__host, '.hamza.fresh-factor.com'));

if (!defined('SITE_URL')) {
    define('SITE_URL', $__is_live_subdomain ? 'https://hamza.fresh-factor.com' : 'https://hamza.fresh-factor.com');
}

if (!defined('ASSETS_BASE_URL')) {
    define('ASSETS_BASE_URL', $__is_live_subdomain ? 'https://fresh-factor.com' : 'https://fresh-factor.com');
}

if (!defined('ASSETS_BASE')) {
    define('ASSETS_BASE', SITE_URL !== '' ? rtrim(SITE_URL, '/') . '/assets' : '');
}

function site_url($path = '') {
    $base = defined('SITE_URL') ? SITE_URL : '';
    if ($base === '') return $path === '' ? 'index.php' : ltrim($path, '/');
    return rtrim($base, '/') . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function asset_url($path) {
    if ($path === '' || $path === null) return '';

    $path = trim((string)$path);
    if ($path === '') return '';

    $path = str_replace('\\', '/', $path);
    $path = preg_replace('#^\.{1,2}/#', '', $path);

    // If DB contains absolute URL, rebase it to current ASSETS_BASE_URL when it points to /assets/
    if (preg_match('#^https?://#i', $path) || substr($path, 0, 2) === '//') {
        $assets_pos = stripos($path, '/assets/');
        if ($assets_pos === false) return $path; // external URL not under /assets/
        $path = substr($path, $assets_pos + 1); // -> assets/...
    }

    // If DB stored 'products/..' etc, normalize to 'assets/products/..'
    $pos = stripos($path, 'assets/');
    if ($pos !== false) {
        $path = substr($path, $pos);
    } else {
        if (preg_match('#^(products|categories|farms|users|admins)/#i', $path)) {
            $path = 'assets/' . $path;
        }
    }

    $path = ltrim($path, '/');

    $base = defined('ASSETS_BASE_URL') ? ASSETS_BASE_URL : '';
    if ($base === '' && defined('SITE_URL')) $base = SITE_URL;

    if ($base !== '') return rtrim($base, '/') . '/' . $path;
    return $path;
}