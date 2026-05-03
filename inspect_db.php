<?php
require_once 'db.php';
$stmt = $pdo->query("DESCRIBE apps");
echo "Table: apps\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['Field']} - {$row['Type']}\n";
}

$stmt = $pdo->query("DESCRIBE users");
echo "\nTable: users\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['Field']} - {$row['Type']}\n";
}
?>
