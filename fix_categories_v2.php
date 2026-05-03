<?php
require_once 'db.php';

try {
    $columns_to_check = [
        'slug' => "ALTER TABLE categories ADD COLUMN slug varchar(50) UNIQUE AFTER name",
        'icon' => "ALTER TABLE categories ADD COLUMN icon varchar(50) DEFAULT 'folder' AFTER slug",
        'description' => "ALTER TABLE categories ADD COLUMN description text DEFAULT NULL AFTER icon"
    ];

    foreach ($columns_to_check as $col => $sql) {
        $stmt = $pdo->query("SHOW COLUMNS FROM categories LIKE '$col'");
        if (!$stmt->fetch()) {
            $pdo->exec($sql);
            echo "Added $col column to categories table.\n";
        }
    }

    // Ensure slugs are populated
    $stmt = $pdo->query("SELECT id, name FROM categories WHERE slug IS NULL OR slug = ''");
    $cats = $stmt->fetchAll();
    foreach ($cats as $cat) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $cat['name'])));
        $pdo->prepare("UPDATE categories SET slug = ? WHERE id = ?")->execute([$slug, $cat['id']]);
        echo "Updated slug for category: " . $cat['name'] . "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
