<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data including constituency
$stmt = $conn->prepare("SELECT has_voted, constituency FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found");
}

// Check if user has already voted
if ($user['has_voted']) {
    header("Location: results.php");
    exit();
}

// Get candidates for user's constituency with error handling
try {
    $stmt = $conn->prepare("SELECT * FROM candidates WHERE constituency = ?");
    $stmt->execute([$user['constituency']]);
    $candidates = $stmt->fetchAll();
    
    if (empty($candidates)) {
        $error = "No candidates found for your constituency (" . htmlspecialchars($user['constituency']) . ").";
    }
} catch (PDOException $e) {
    $error = "Error retrieving candidates: " . $e->getMessage();
}

// Handle vote submission

// Verify CSRF token on form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token - Please go back and try voting again.");
    }
    
    // Process the vote if CSRF token is valid
    if (isset($_POST['candidate_id'])) {
        // Generate OTP
        $otp = rand(100000, 999999);
        $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Store OTP in database
        $stmt = $conn->prepare("INSERT INTO otp_verification (voter_id, otp, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['voter_id'], $otp, $expires_at]);
        
        // Store candidate ID in session for verification
        $_SESSION['candidate_id'] = $_POST['candidate_id'];
        
        // Regenerate CSRF token after successful submission
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Redirect to OTP verification
        header("Location: verify_otp.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cast Your Vote - Karnataka Online Voting</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .candidates-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .candidate-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            transition: transform 0.3s;
        }
        .candidate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .candidate-photo img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
        }
        .candidate-info {
            margin-top: 10px;
        }
        .candidate-symbol img {
            height: 50px;
            margin: 10px 0;
        }
        .error-message {
            color: #d9534f;
            padding: 15px;
            border: 1px solid #ebccd1;
            background-color: #f2dede;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <img src="assets/images/logo.png" alt="Karnataka Election Commission" class="logo">
            <h1>Cast Your Vote - <?php echo htmlspecialchars($user['constituency']); ?></h1>
        </div>
    </header>

    <main class="container">
        <section class="voting-section">
            <h2>Select Your Candidate</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php elseif (!empty($candidates)): ?>
                <p>You are voting in <?php echo htmlspecialchars($user['constituency']); ?> constituency</p>
                
                <div class="candidates-list">
                    <?php foreach ($candidates as $candidate): ?>
                        <form action="vote.php" method="post" class="candidate-card">
    <!-- Add this hidden CSRF token field -->
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    
    <div class="candidate-photo">
        <img src="assets/images/candidates/<?php echo $candidate['photo']; ?>" 
             alt="<?php echo $candidate['name']; ?>">
    </div>
    <div class="candidate-info">
        <h3><?php echo $candidate['name']; ?></h3>
        <p><?php echo $candidate['party']; ?></p>
        <div class="candidate-symbol">
            <img src="assets/images/symbols/<?php echo $candidate['symbol']; ?>" 
                 alt="<?php echo $candidate['party']; ?> symbol">
        </div>
        <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
        <button type="submit" class="btn">Vote for this candidate</button>
    </div>
</form>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="error-message">No candidates available for voting in your constituency.</div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 Karnataka Election Commission. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>