<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php?admin=1");
    exit();
}

// Search functionality
$search = '';
$where = '';
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
    $where = "WHERE name LIKE :search OR voter_id LIKE :search OR email LIKE :search";
}

// Pagination
$per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

// Get voters
$query = "SELECT * FROM users $where ORDER BY created_at DESC LIMIT :offset, :per_page";
$stmt = $conn->prepare($query);

if(!empty($where)) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();
$voters = $stmt->fetchAll();

// Total count for pagination
$count_query = "SELECT COUNT(*) FROM users $where";
$stmt = $conn->prepare($count_query);
if(!empty($where)) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->execute();
$total_voters = $stmt->fetchColumn();
$total_pages = ceil($total_voters / $per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Voters - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin_sidebar.php'; ?>

        <main class="admin-content">
            <header class="admin-header">
                <h1>Manage Voters</h1>
                <div class="admin-actions">
                    <a href="add_voter.php" class="btn">Add New Voter</a>
                </div>
            </header>

            <div class="admin-section">
                <form method="get" class="search-form">
                    <input type="text" name="search" placeholder="Search voters..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn">Search</button>
                    <?php if(!empty($search)): ?>
                        <a href="manage_voters.php" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Voter ID</th>
                                <th>Name</th>
                                <th>Constituency</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($voters as $voter): ?>
                            <tr>
                                <td><?php echo $voter['voter_id']; ?></td>
                                <td><?php echo htmlspecialchars($voter['name']); ?></td>
                                <td><?php echo $voter['constituency']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $voter['has_voted'] ? 'success' : 'warning'; ?>">
                                        <?php echo $voter['has_voted'] ? 'Voted' : 'Pending'; ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="view_voter.php?id=<?php echo $voter['id']; ?>" class="btn btn-sm">View</a>
                                    <a href="edit_voter.php?id=<?php echo $voter['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    <form action="delete_voter.php" method="post" class="inline-form">
                                        <input type="hidden" name="id" value="<?php echo $voter['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <?php if($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" class="page-link">Previous</a>
                    <?php endif; ?>

                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" class="page-link">Next</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>