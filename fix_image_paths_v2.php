<?php
require_once 'db.php';
try {
    // Fix app logos
    $stmt = $pdo->prepare("UPDATE apps SET logo = REPLACE(logo, 'public/', '') WHERE logo LIKE 'public/%'");
    $stmt->execute();
    $affected_apps = $stmt->rowCount();

    echo "Fixed $affected_apps app logos.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
