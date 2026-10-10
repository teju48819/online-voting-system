<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php?admin=1");
    exit();
}

// Get all constituencies
$constituencies = $conn->query("SELECT DISTINCT constituency FROM candidates ORDER BY constituency")->fetchAll(PDO::FETCH_COLUMN);

// Get selected constituency or default to first one
$selected_constituency = isset($_GET['constituency']) ? $_GET['constituency'] : ($constituencies[0] ?? '');

// Get results for selected constituency
$results = [];
if(!empty($selected_constituency)) {
    $stmt = $conn->prepare("SELECT * FROM candidates WHERE constituency = ? ORDER BY votes DESC");
    $stmt->execute([$selected_constituency]);
    $results = $stmt->fetchAll();
}

// Get overall statistics
$total_votes = $conn->query("SELECT SUM(votes) FROM candidates")->fetchColumn();
$total_candidates = $conn->query("SELECT COUNT(*) FROM candidates")->fetchColumn();
$total_constituencies = $conn->query("SELECT COUNT(DISTINCT constituency) FROM candidates")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin_sidebar.php'; ?>

        <main class="admin-content">
            <header class="admin-header">
                <h1>Election Results</h1>
                <div class="admin-actions">
                    <form method="get" class="constituency-selector">
                        <select name="constituency" onchange="this.form.submit()">
                            <?php foreach($constituencies as $constituency): ?>
                                <option value="<?php echo $constituency; ?>" <?php echo $selected_constituency == $constituency ? 'selected' : ''; ?>>
                                    <?php echo $constituency; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </header>

            <div class="stats-cards">
                <div class="stat-card">
                    <h3>Total Votes Cast</h3>
                    <p><?php echo $total_votes; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Constituencies</h3>
                    <p><?php echo $total_constituencies; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Candidates</h3>
                    <p><?php echo $total_candidates; ?></p>
                </div>
            </div>

            <div class="admin-section">
                <h2>Results for <?php echo $selected_constituency; ?></h2>
                
                <?php if(!empty($results)): ?>
                <div class="results-container">
                    <div class="results-chart">
                        <canvas id="resultsChart"></canvas>
                    </div>
                    
                    <div class="results-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Candidate</th>
                                    <th>Party</th>
                                    <th>Votes</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($results as $index => $candidate): 
                                    $percentage = $total_votes > 0 ? round(($candidate['votes'] / $total_votes) * 100, 2) : 0;
                                ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <div class="candidate-info">
                                            <img src="../assets/images/candidates/<?php echo $candidate['photo']; ?>" alt="<?php echo $candidate['name']; ?>" class="candidate-thumb">
                                            <?php echo htmlspecialchars($candidate['name']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="party-info">
                                            <img src="../assets/images/symbols/<?php echo $candidate['symbol']; ?>" alt="<?php echo $candidate['party']; ?>" class="symbol-thumb">
                                            <?php echo $candidate['party']; ?>
                                        </div>
                                    </td>
                                    <td><?php echo $candidate['votes']; ?></td>
                                    <td>
                                        <div class="percentage-bar">
                                            <div class="bar" style="width: <?php echo $percentage; ?>%"></div>
                                            <span><?php echo $percentage; ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php else: ?>
                    <p>No results available for this constituency.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Prepare chart data
        const candidates = <?php echo json_encode(array_column($results, 'name')); ?>;
        const votes = <?php echo json_encode(array_column($results, 'votes')); ?>;
        const parties = <?php echo json_encode(array_column($results, 'party')); ?>;
        const colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];

        // Create chart
        const ctx = document.getElementById('resultsChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: candidates,
                datasets: [{
                    data: votes,
                    backgroundColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const index = context.dataIndex;
                                return 'Party: ' + parties[index];
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>