<?php
// Check if tables exist and create them if not
$tables_exist = $conn->query("SHOW TABLES LIKE 'users'")->rowCount() > 0;

if (!$tables_exist) {
    try {
        $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            voter_id VARCHAR(20) UNIQUE NOT NULL,
            name VARCHAR(100) NOT NULL,
            dob DATE NOT NULL,
            gender ENUM('Male', 'Female', 'Other') NOT NULL,
            address TEXT NOT NULL,
            constituency VARCHAR(100) NOT NULL,
            district VARCHAR(50) NOT NULL,
            phone VARCHAR(15) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            is_verified BOOLEAN DEFAULT 0,
            has_voted BOOLEAN DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $conn->exec("
        CREATE TABLE IF NOT EXISTS candidates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            party VARCHAR(100) NOT NULL,
            constituency VARCHAR(100) NOT NULL,
            district VARCHAR(50) NOT NULL,
            symbol VARCHAR(100) NOT NULL,
            photo VARCHAR(100) NOT NULL,
            votes INT DEFAULT 0
        )");
        
        $conn->exec("
        CREATE TABLE IF NOT EXISTS otp_verification (
            id INT AUTO_INCREMENT PRIMARY KEY,
            voter_id VARCHAR(20) NOT NULL,
            otp VARCHAR(6) NOT NULL,
            expires_at DATETIME NOT NULL,
            FOREIGN KEY (voter_id) REFERENCES users(voter_id) ON DELETE CASCADE
        )");
        
        $conn->exec("
        CREATE TABLE IF NOT EXISTS activity_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            activity VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )");
        
        $conn->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('superadmin', 'admin', 'observer') NOT NULL DEFAULT 'admin',
            last_login DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Insert default admin user if not exists
        $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = 'admin'");
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
            $conn->prepare("INSERT INTO admin_users (username, email, password, role) VALUES (?, ?, ?, ?)")
                 ->execute(['admin', 'admin@voting.system', $hashed_password, 'superadmin']);
        }
        
    } catch (PDOException $e) {
        die("Error creating tables: " . $e->getMessage());
    }
}
?>