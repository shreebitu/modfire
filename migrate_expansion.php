<?php
require_once 'db.php';

try {
    // 1. Categories Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        slug VARCHAR(50) NOT NULL UNIQUE,
        description TEXT,
        icon VARCHAR(50) DEFAULT 'folder'
    )");

    // Insert default categories if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO categories (name, slug, description, icon) VALUES 
            ('Apps', 'apk', 'Android applications and tools', 'android'),
            ('Notes', 'pdf', 'Educational notes and PDF documents', 'description'),
            ('Presentations', 'ppt', 'PowerPoint and slide presentations', 'present_to_all'),
            ('Windows', 'windows', 'Windows software and utilities', 'desktop_windows')");
    }

    // 2. Downloads Log Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS downloads_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        app_id INT NOT NULL,
        user_id INT DEFAULT NULL,
        ip_address VARCHAR(45),
        downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (app_id) REFERENCES apps(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    // 3. Reports Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        app_id INT NOT NULL,
        user_id INT NOT NULL,
        reason TEXT NOT NULL,
        status ENUM('pending', 'resolved', 'dismissed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (app_id) REFERENCES apps(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // 4. Favorites (Bookmarks) Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS favorites (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        app_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY user_app (user_id, app_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (app_id) REFERENCES apps(id) ON DELETE CASCADE
    )");

    // 5. Notifications Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        type VARCHAR(20) DEFAULT 'system',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    echo "Tables created and initialized successfully.";
} catch (PDOException $e) {
    die("Error expanding database: " . $e->getMessage());
}
?>
