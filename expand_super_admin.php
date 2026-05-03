<?php
require_once 'db.php';

try {
    // 1. Expand settings table with new control keys if they don't exist
    $default_settings = [
        'registration_enabled' => '1',
        'upload_enabled' => '1',
        'download_enabled' => '1',
        'maintenance_mode' => '0',
        'max_file_size' => '100', // MB
        'allowed_extensions' => 'apk,pdf,ppt,pptx,zip,rar',
        'approval_system' => '1', // 1 = Required, 0 = Auto-approve
        'external_links_allowed' => '1',
        'admin_ip_whitelist' => '', // Comma separated IPs
    ];

    foreach ($default_settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_key = setting_key");
        $stmt->execute([$key, $value]);
    }

    // 2. Add shadow_banned and last_active to users table
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS shadow_banned TINYINT(1) DEFAULT 0");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS session_token VARCHAR(255) DEFAULT NULL");

    // 3. Create activity_logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        action VARCHAR(100) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 4. Create blocked_ips table
    $pdo->exec("CREATE TABLE IF NOT EXISTS blocked_ips (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) UNIQUE NOT NULL,
        reason TEXT,
        blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    echo "Super Admin database expansion completed successfully!";
} catch (PDOException $e) {
    die("Expansion failed: " . $e->getMessage());
}
?>
