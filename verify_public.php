<?php
require_once 'db.php';
$stmt = $pdo->query("SELECT id, logo FROM apps WHERE logo LIKE '%public%'");
print_r($stmt->fetchAll());
?>
