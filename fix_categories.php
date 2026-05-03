<?php
require_once 'db.php';

try {
    // Check if slug column exists in categories
    $stmt = $pdo->query("SHOW COLUMNS FROM categories LIKE 'slug'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE categories ADD COLUMN slug varchar(50) UNIQUE AFTER name");
        echo "Added slug column to categories table.\n";
    } else {
        echo "Slug column already exists in categories table.\n";
    }

    // Populate slugs if empty
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
