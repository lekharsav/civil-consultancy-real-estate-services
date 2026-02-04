<?php
// admin/setup_admin.php
// ONE-TIME script: creates admins table and a default admin account.
// After running once, delete this file for security!

require_once __DIR__ . '/../config/config.php';

try {
    // Create table if not exists
    $ddl = "CREATE TABLE IF NOT EXISTS `admins` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `name` VARCHAR(100) DEFAULT NULL,
      `username` VARCHAR(150) NOT NULL UNIQUE,
      `password` VARCHAR(255) NOT NULL,
      `last_login` DATETIME DEFAULT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->query($ddl);

    // Default credentials - CHANGE after first login
    $default_username = 'admin@bbc.com';
    $default_password_plain = 'Admin@123'; // change this after first login
    $default_name = 'Super Admin';

    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->bind_param('s', $default_username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $hash = password_hash($default_password_plain, PASSWORD_BCRYPT);
        $ins = $conn->prepare("INSERT INTO admins (name, username, password) VALUES (?,?,?)");
        $ins->bind_param('sss', $default_name, $default_username, $hash);
        $ins->execute();
        echo "<h2>Setup complete</h2>";
        echo "<p>Admin created: <strong>{$default_username}</strong></p>";
        echo "<p>Temporary password: <strong>{$default_password_plain}</strong></p>";
        echo "<p><strong>Important:</strong> Login and change the password immediately. Then delete this setup file.</p>";
    } else {
        echo "<h2>Admin user already exists</h2>";
        echo "<p>If you need to reset the password, remove the user and run again (or update password via SQL/PHP).</p>";
    }

} catch (Exception $e) {
    echo "<h2>Error</h2><pre>" . $e->getMessage() . "</pre>";
}
