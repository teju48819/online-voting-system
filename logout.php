<?php
require_once 'config.php';

// Destroy all sessions
$_SESSION = array();

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect based on user type
if (isset($_GET['admin'])) {
    header("Location: ../login.php?admin=1");
} else {
    header("Location: ../index.php");
}
exit();
?>