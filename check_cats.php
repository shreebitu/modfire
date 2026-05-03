<?php
require_once 'db.php';
$stmt = $pdo->query("SELECT id, name, icon FROM categories");
print_r($stmt->fetchAll());
?>
