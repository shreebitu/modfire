<?php
require_once 'db.php';
$stmt = $pdo->query("DESCRIBE apps");
while ($row = $stmt->fetch()) {
    echo $row['Field'] . ", ";
}
?>
