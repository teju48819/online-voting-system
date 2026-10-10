<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Start session for authentication
session_start();

// Check if user is logged in (optional - remove if public results)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get requested constituency or show all
$constituency = $_GET['constituency'] ?? null;
$page_title = $constituency ? "Results - $constituency" : "Election Results";

try {
    if ($constituency) {
        // Single constituency results
        $stmt = $conn->prepare("SELECT * FROM candidates WHERE constituency = ? ORDER BY votes DESC");
        $stmt->execute([$constituency]);
        $results = $stmt->fetchAll();
    } else {
        // All results
        $stmt = $conn->query("SELECT * FROM candidates ORDER BY constituency, votes DESC");
        $results = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    die("Error retrieving results: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <img src="assets/images/logo.png" alt="Karnataka Election Commission" class="logo">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <a href="index.php" class="btn">Back to Home</a>
        </div>
    </header>

    <main class="container">
        <?php if (empty($results)): ?>
            <div class="alert">No results available yet.</div>
        <?php else: ?>
            <div class="results-table">
                <table>
                    <thead>
                        <tr>
                            <?php if (!$constituency): ?>
                                <th>Constituency</th>
                            <?php endif; ?>
                            <th>Candidate</th>
                            <th>Party</th>
                            <th>Votes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $candidate): ?>
                            <tr>
                                <?php if (!$constituency): ?>
                                    <td><?php echo htmlspecialchars($candidate['constituency']); ?></td>
                                <?php endif; ?>
                                <td>
                                    <img src="assets/images/candidates/<?php echo htmlspecialchars($candidate['photo']); ?>" 
                                         alt="<?php echo htmlspecialchars($candidate['name']); ?>" width="50">
                                    <?php echo htmlspecialchars($candidate['name']); ?>
                                </td>
                                <td>
                                    <img src="assets/images/symbols/<?php echo htmlspecialchars($candidate['symbol']); ?>" 
                                         alt="<?php echo htmlspecialchars($candidate['party']); ?>" width="30">
                                    <?php echo htmlspecialchars($candidate['party']); ?>
                                </td>
                                <td><?php echo number_format($candidate['votes']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Karnataka Election Commission. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>