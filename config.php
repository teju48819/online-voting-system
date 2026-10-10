<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'online_voting_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application Configuration
define('APP_NAME', 'Karnataka Online Voting System');
define('APP_ROOT', dirname(dirname(__FILE__)));
define('APP_URL', 'http://localhost/online-voting-system');

// Security Configuration
define('SECRET_KEY', 'your-secret-key-here');
define('OTP_EXPIRY_MINUTES', 10);

// File Upload Configuration
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png']);
define('CANDIDATE_PHOTO_DIR', APP_ROOT . '/assets/images/candidates/');
define('PARTY_SYMBOL_DIR', APP_ROOT . '/assets/images/symbols/');

// Error Reporting
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);


// Timezone
date_default_timezone_set('Asia/Kolkata');
// Email Configuration (add to config.php)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-email-password');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');

// SMS Configuration (Twilio)
define('TWILIO_SID', 'your_account_sid');
define('TWILIO_TOKEN', 'your_auth_token');
define('TWILIO_NUMBER', '+1234567890');
// Session Configuration
// In includes/config.php
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']), // Auto-detect HTTPS
    'httponly' => true,
    'samesite' => 'Strict' // Important for CSRF protection
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Generate CSRF token if not exists
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
?>