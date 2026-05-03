<?php
require_once '../config.php';
require_once '../db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$app_id = (int)($_GET['app_id'] ?? 0);

if ($app_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid App ID']);
    exit();
}

// Check if already favorited
$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND app_id = ?");
$stmt->execute([$user_id, $app_id]);
$fav = $stmt->fetch();

if ($fav) {
    // Remove from favorites
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE id = ?");
    $stmt->execute([$fav['id']]);
    echo json_encode(['status' => 'removed']);
} else {
    // Add to favorites
    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, app_id, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$user_id, $app_id]);
    echo json_encode(['status' => 'added']);
}
