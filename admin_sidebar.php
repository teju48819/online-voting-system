<aside class="admin-sidebar">
    <div class="admin-logo">
        <img src="../assets/images/logo.png" alt="Logo">
        <h2>Admin Panel</h2>
    </div>
    <nav>
        <ul>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php"><i class="icon-dashboard"></i> Dashboard</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_voters.php' ? 'active' : ''; ?>">
                <a href="manage_voters.php"><i class="icon-users"></i> Manage Voters</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_candidates.php' ? 'active' : ''; ?>">
                <a href="manage_candidates.php"><i class="icon-candidate"></i> Manage Candidates</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'election_settings.php' ? 'active' : ''; ?>">
                <a href="election_settings.php"><i class="icon-settings"></i> Election Settings</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'results.php' ? 'active' : ''; ?>">
                <a href="results.php"><i class="icon-results"></i> View Results</a>
            </li>
            <li>
                <a href="../includes/logout.php?admin=1"><i class="icon-logout"></i> Logout</a>
            </li>
        </ul>
    </nav>
</aside>