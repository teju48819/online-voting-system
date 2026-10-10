<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

if(!isset($_SESSION['user_id']) || !isset($_SESSION['candidate_id'])) {
    header("Location: login.php");
    exit();
}

// Handle OTP submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['otp'])) {
    $otp = $_POST['otp'];
    
    // Verify OTP
    $stmt = $conn->prepare("SELECT * FROM otp_verification WHERE voter_id = ? AND otp = ? AND expires_at > NOW()");
    $stmt->execute([$_SESSION['voter_id'], $otp]);
    $otp_record = $stmt->fetch();
    
    if($otp_record) {
        // Record the vote
        $stmt = $conn->prepare("UPDATE candidates SET votes = votes + 1 WHERE id = ?");
        $stmt->execute([$_SESSION['candidate_id']]);
        
        // Mark user as voted
        $stmt = $conn->prepare("UPDATE users SET has_voted = 1 WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        // Delete used OTP
        $stmt = $conn->prepare("DELETE FROM otp_verification WHERE voter_id = ?");
        $stmt->execute([$_SESSION['voter_id']]);
        
        // Redirect to results
        header("Location: results.php");
        exit();
    } else {
        $error = "Invalid OTP or OTP has expired";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - Karnataka Online Voting</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <img src="assets/images/logo.png" alt="Karnataka Election Commission" class="logo">
            <h1>OTP Verification</h1>
        </div>
    </header>

    <main class="container">
        <section class="otp-verification">
            <h2>Verify Your Identity</h2>
            <p>An OTP has been sent to your registered mobile number and email.</p>
            
            <?php if(isset($error)): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="verify_otp.php" method="post">
                <div class="form-group">
                    <label for="otp">Enter 6-digit OTP</label>
                    <input type="text" id="otp" name="otp" maxlength="6" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">Verify & Submit Vote</button>
                </div>
            </form>
            
            <p>Didn't receive OTP? <a href="#" id="resend-otp">Resend OTP</a></p>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 Karnataka Election Commission. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
    <script>
       // In your verify_otp.php JavaScript
document.getElementById('resend-otp').addEventListener('click', function(e) {
    e.preventDefault();
    
    fetch('api/send_otp.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ voter_id: '<?php echo $_SESSION["voter_id"]; ?>' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`OTP resent successfully via ${data.via.join(' and ')}`);
        } else {
            alert('Failed to resend OTP: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while resending OTP');
    });
});
    </script>
</body>
</html>