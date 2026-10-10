<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check admin authentication
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php?admin=1");
    exit();
}

// Get statistics
$total_voters = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$voted = $conn->query("SELECT COUNT(*) FROM users WHERE has_voted = 1")->fetchColumn();
$pending = $total_voters - $voted;
$candidates = $conn->query("SELECT COUNT(*) FROM candidates")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Karnataka Voting</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <img src="../assets/images/logo.png" alt="Logo">
                <h2>Admin Panel</h2>
            </div>
            <nav>
                <ul>
                    <li class="active"><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="manage_voters.php">Manage Voters</a></li>
                    <li><a href="manage_candidates.php">Manage Candidates</a></li>
                    <li><a href="election_settings.php">Election Settings</a></li>
                    <li><a href="results.php">View Results</a></li>
                    <li><a href="../includes/logout.php?admin=1">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <header class="admin-header">
                <h1>Election Dashboard</h1>
                <div class="admin-user">
                    <span>Welcome, Admin</span>
                </div>
            </header>

            <div class="stats-cards">
                <div class="stat-card">
                    <h3>Total Voters</h3>
                    <p><?php echo $total_voters; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Votes Cast</h3>
                    <p><?php echo $voted; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Pending Votes</h3>
                    <p><?php echo $pending; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Candidates</h3>
                    <p><?php echo $candidates; ?></p>
                </div>
            </div>

            <div class="admin-section">
                <h2>Recent Activities</h2>
                <div class="activity-log">
                    <?php
                    $activities = $conn->query("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 5");
                    while($activity = $activities->fetch()):
                    ?>
                    <div class="activity-item">
                        <p><?php echo $activity['activity']; ?></p>
                        <small><?php echo date('d M Y H:i', strtotime($activity['created_at'])); ?></small>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>