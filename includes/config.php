<?php
/**
 * Configuración central — Catálogo Virtual Anime (MySQL)
 */

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_name('CATALOGO_SESSID');
    // Sesión de 12 horas o hasta cerrar el navegador si lifetime 0 —
    // usamos 12h para que no se pierda al navegar catálogo ↔ admin
    session_set_cookie_params([
        'lifetime' => 60 * 60 * 12,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

define('ROOT_PATH', dirname(__DIR__));
define('DATA_DIR', ROOT_PATH . '/data/');
define('UPLOAD_DIR', ROOT_PATH . '/assets/uploads/');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCK_MINUTES', 15);

require_once __DIR__ . '/modules/Database.php';
require_once __DIR__ . '/modules/Auth.php';
require_once __DIR__ . '/modules/Catalog.php';
require_once __DIR__ . '/modules/Orders.php';
require_once __DIR__ . '/modules/Settings.php';
