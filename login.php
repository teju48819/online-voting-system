<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if($user && password_verify($password, $user['password'])) {
        if($user['is_verified']) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['voter_id'] = $user['voter_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['constituency'] = $user['constituency'];
            
            if($user['has_voted']) {
                header("Location: results.php");
            } else {
                header("Location: vote.php");
            }
            exit();
        } else {
            $error = "Your account is not verified. Please check your email for verification link.";
        }
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Login - Karnataka Online Voting</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <img src="assets/images/logo.png" alt="Karnataka Election Commission" class="logo">
            <h1>Voter Login</h1>
        </div>
    </header>

    <main class="container">
        <section class="login-form">
            <h2>Login to Your Voting Account</h2>
            
            <?php if(isset($_GET['registered'])): ?>
                <div class="alert success">Registration successful! You can now login.</div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">Login</button>
                </div>
            </form>
            
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p>Forgot password? <a href="forgot_password.php">Reset it here</a></p>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 Karnataka Election Commission. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>