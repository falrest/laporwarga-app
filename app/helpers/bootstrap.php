<?php
/**
 * Bootstrap: di-include di paling atas setiap entry point (halaman & API).
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/sanitize.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/logger.php';

send_security_headers();
secure_session_start();
