<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karnataka Online Voting System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <img src="assets/images/logo.png" alt="Karnataka Election Commission" class="logo">
            <h1>Karnataka State Online Voting System</h1>
        </div>
    </header>

    <main class="container">
        <section class="hero">
            <img src="assets/images/karnataka-map.jpg" alt="Karnataka Map">
            <div class="hero-content">
                <h2>Cast Your Vote Securely Online</h2>
                <p>Participate in Karnataka's democratic process from the comfort of your home</p>
                <div class="cta-buttons">
                    <a href="login.php" class="btn">Voter Login</a>
                    <a href="register.php" class="btn btn-secondary">New Voter Registration</a>
                    <!-- <a href="results.php" class="btn btn-large">View All Results</a> -->
                </div>
                <!-- <div class="view-results-side-button">
                    <a href="results.php" class="btn btn-large">View All Results</a>
                </div> -->
            </div>
        </section>

        <section class="features">
            <div class="feature-card">
                <h3>Secure Voting</h3>
                <p>End-to-end encrypted voting process with OTP verification</p>
            </div>
            <div class="feature-card">
                <h3>Real-time Results</h3>
                <p>View election results as soon as voting ends</p>
            </div>
            <div class="feature-card">
                <h3>Accessible</h3>
                <p>Vote from anywhere in Karnataka with internet access</p>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 Karnataka Election Commission. All rights reserved.</p>
        </div>
    </footer>
    <a href="results.php?constituency=<?php echo urlencode($result['constituency']); ?>" class="btn">
    View Details
</a>

<a href="results.php" class="btn btn-large">View Complete Results</a>
    <script src="assets/js/script.js"></script>
</body>
</html>