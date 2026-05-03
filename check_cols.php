<?php
require_once 'db.php';
try {
    $stmt = $pdo->query("DESCRIBE categories");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(", ", $columns);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
