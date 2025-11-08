<?php
/**
 * Configuration File
 * Contains database credentials and application settings
 */

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'grading_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', 'Grading System');
define('BASE_URL', '/grading-system/');
define('SITE_URL', 'http://localhost/grading-system/');

// File Upload Settings
define('MAX_FILE_SIZE', 2097152); // 2MB in bytes
define('ALLOWED_FILE_TYPES', ['text/csv', 'application/vnd.ms-excel']);
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// Pagination Settings
define('RECORDS_PER_PAGE', 10);

// Grade Scale Configuration
define('GRADE_SCALE', [
    'A' => ['min' => 90, 'max' => 100],
    'B' => ['min' => 80, 'max' => 89.99],
    'C' => ['min' => 70, 'max' => 79.99],
    'D' => ['min' => 60, 'max' => 69.99],
    'F' => ['min' => 0, 'max' => 59.99]
]);

define('PASSING_GRADE', 60);

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.use_strict_mode', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID to prevent session fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Timezone
date_default_timezone_set('Asia/Manila');
?>