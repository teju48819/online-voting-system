<?php
require_once 'config.php';
require_once 'db_connect.php';
require_once 'functions.php';

if (!isAdminLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: ../login.php?admin=1");
    exit();
}

// Check if admin user still exists in database
$stmt = $conn->prepare("SELECT id FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);

if ($stmt->rowCount() === 0) {
    session_destroy();
    header("Location: ../login.php?admin=1&error=Account no longer exists");
    exit();
}

// Log admin activity
logActivity($_SESSION['admin_id'], 'Accessed ' . basename($_SERVER['PHP_SELF']));
?>