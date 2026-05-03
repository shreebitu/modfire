<?php
require_once '../config.php';
require_once '../db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $user_id = $_SESSION['user_id'];
    $app_id = (int)($_POST['app_id'] ?? 0);

    if ($app_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid App ID']);
        exit();
    }

    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND app_id = ?");
    $stmt->execute([$user_id, $app_id]);
    $fav = $stmt->fetch();

    if ($fav) {
        // Remove
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE id = ?");
        $stmt->execute([$fav['id']]);
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        // Add
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, app_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $app_id]);
        echo json_encode(['success' => true, 'action' => 'added']);
    }
}
?>
