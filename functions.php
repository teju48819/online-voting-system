<?php
require_once 'config.php';

/**
 * Generate a random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: " . APP_URL . "/" . ltrim($url, '/'));
    exit();
}

/**
 * Generate OTP and store in database
 */
function generateOTP($voter_id) {
    global $conn;
    
    $otp = rand(100000, 999999);
    $expires_at = date('Y-m-d H:i:s', strtotime('+'.OTP_EXPIRY_MINUTES.' minutes'));
    
    // Delete any existing OTP for this voter
    $stmt = $conn->prepare("DELETE FROM otp_verification WHERE voter_id = ?");
    $stmt->execute([$voter_id]);
    
    // Store new OTP
    $stmt = $conn->prepare("INSERT INTO otp_verification (voter_id, otp, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$voter_id, $otp, $expires_at]);
    
    return $otp;
}

/**
 * Verify OTP
 */
function verifyOTP($voter_id, $otp) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM otp_verification WHERE voter_id = ? AND otp = ? AND expires_at > NOW()");
    $stmt->execute([$voter_id, $otp]);
    
    return $stmt->rowCount() > 0;
}

/**
 * Upload file with validation
 */
function uploadFile($file, $target_dir, $allowed_types) {
    $errors = [];
    $file_name = basename($file['name']);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $new_file_name = uniqid() . '.' . $file_ext;
    $target_path = $target_dir . $new_file_name;
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = "File is too large. Maximum allowed size is " . (MAX_FILE_SIZE / (1024 * 1024)) . "MB";
    }
    
    // Check file type
    if (!in_array($file_ext, $allowed_types)) {
        $errors[] = "Only " . implode(', ', $allowed_types) . " files are allowed";
    }
    
    if (empty($errors)) {
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            return $new_file_name;
        } else {
            $errors[] = "Failed to upload file";
        }
    }
    
    return ['errors' => $errors];
}

/**
 * Log activity
 */
function logActivity($user_id, $activity) {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $activity, $ip_address, $user_agent]);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']);
}

/**
 * Get user data
 */
function getUserData($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    return $stmt->fetch();
}

/**
 * Get candidate data
 */
function getCandidateData($candidate_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
    $stmt->execute([$candidate_id]);
    
    return $stmt->fetch();
}
?>