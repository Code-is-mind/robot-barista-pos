<?php
// Application configuration
session_start();

// Auto-detect base URL to work with any folder name
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
$baseDir = dirname(dirname($script));
define('BASE_URL', $protocol . '://' . $host . $baseDir . '/');
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define('UPLOAD_URL', BASE_URL . 'public/uploads/');

// Timezone
date_default_timezone_set('Asia/Phnom_Penh');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/database.php';
