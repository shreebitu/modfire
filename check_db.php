<?php
require_once 'db.php';
$stmt = $pdo->query("DESCRIBE apps");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Columns in 'apps' table: " . implode(", ", $columns);
?>
