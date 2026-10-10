<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php?admin=1");
    exit();
}

// Handle candidate deletion
if(isset($_POST['delete_candidate'])) {
    $id = $_POST['id'];
    // Delete candidate photo and symbol if they exist
    $stmt = $conn->prepare("SELECT photo, symbol FROM candidates WHERE id = ?");
    $stmt->execute([$id]);
    $candidate = $stmt->fetch();
    
    if($candidate) {
        if(file_exists("../assets/images/candidates/".$candidate['photo'])) {
            unlink("../assets/images/candidates/".$candidate['photo']);
        }
        if(file_exists("../assets/images/symbols/".$candidate['symbol'])) {
            unlink("../assets/images/symbols/".$candidate['symbol']);
        }
    }
    
    // Delete candidate
    $stmt = $conn->prepare("DELETE FROM candidates WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['message'] = "Candidate deleted successfully";
    header("Location: manage_candidates.php");
    exit();
}

// Get all candidates
$candidates = $conn->query("SELECT * FROM candidates ORDER BY constituency, party")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin_sidebar.php'; ?>

        <main class="admin-content">
            <header class="admin-header">
                <h1>Manage Candidates</h1>
                <div class="admin-actions">
                    <a href="add_candidate.php" class="btn">Add New Candidate</a>
                </div>
            </header>

            <?php if(isset($_SESSION['message'])): ?>
                <div class="alert <?php echo $_SESSION['message_type'] === 'success' ? 'success' : 'error'; ?>">
                    <?php echo $_SESSION['message']; ?>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <div class="admin-section">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Party</th>
                                <th>Constituency</th>
                                <th>Votes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($candidates as $candidate): ?>
                            <tr>
                                <td>
                                    <img src="../assets/images/candidates/<?php echo $candidate['photo']; ?>" alt="<?php echo $candidate['name']; ?>" class="candidate-thumb">
                                </td>
                                <td><?php echo htmlspecialchars($candidate['name']); ?></td>
                                <td>
                                    <div class="party-info">
                                        <img src="../assets/images/symbols/<?php echo $candidate['symbol']; ?>" alt="<?php echo $candidate['party']; ?>" class="symbol-thumb">
                                        <?php echo $candidate['party']; ?>
                                    </div>
                                </td>
                                <td><?php echo $candidate['constituency']; ?></td>
                                <td><?php echo $candidate['votes']; ?></td>
                                <td class="actions">
                                    <a href="edit_candidate.php?id=<?php echo $candidate['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    <form action="" method="post" class="inline-form">
                                        <input type="hidden" name="id" value="<?php echo $candidate['id']; ?>">
                                        <button type="submit" name="delete_candidate" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this candidate?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>