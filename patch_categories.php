<?php
require_once 'db.php';

try {
    // Add 'icon' column to categories table
    $pdo->exec("ALTER TABLE categories ADD COLUMN IF NOT EXISTS icon VARCHAR(50) DEFAULT 'category'");
    echo "Added 'icon' column to categories table.<br>";

    // Update some default icons if they exist
    $updates = [
        'Games' => 'sports_esports',
        'Tools' => 'build',
        'Social' => 'share',
        'Productivity' => 'check_circle',
        'Entertainment' => 'movie',
        'Education' => 'school'
    ];

    foreach ($updates as $name => $icon) {
        $stmt = $pdo->prepare("UPDATE categories SET icon = ? WHERE name = ?");
        $stmt->execute([$icon, $name]);
    }
    echo "Updated default icons.<br>";

    echo "Database patch applied successfully!";
} catch (PDOException $e) {
    die("Error patching database: " . $e->getMessage());
}
?>
