<?php
/**
 * Local development overrides (used by USER SITE – hamza.fresh-factor.com/includes).
 * Copy this file to config.local.php and set your local MySQL credentials.
 * Do not commit config.local.php or upload it to the live server.
 */

// Same database/user as admin and live host
define('DB_HOST', 'localhost');
define('DB_NAME', 'freshfac_HamzaDb');
define('DB_USER', 'freshfac_HamzaUser');
define('DB_PASS', 'Ha1994181@');

// SITE_URL and ASSETS_BASE_URL stay empty for local (relative paths) unless you use virtual hosts.
