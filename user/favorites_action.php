<?php
/**
 * ============================================================
 * AJAX: TOGGLE FAVORITE (GET method)
 * ============================================================
 * Purpose: Adds or removes an app from the user's favorites.
 *          Called via JavaScript (AJAX) when the heart icon is clicked.
 *
 * Method:  GET (simpler but less secure — see toggle_favorite.php for POST version)
 * Input:   GET: app_id (the app to toggle)
 * Output:  JSON: { status: "added" | "removed" | "error" }
 * Security: Login required (no CSRF — see security notes)
 * ============================================================
 */
require_once '../includes/init.php';

// Set response type to JSON for AJAX consumption
header('Content-Type: application/json');

// ── Authentication Check ──
if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$app_id = (int)($_GET['app_id'] ?? 0);

// ── Validate the app ID ──
if ($app_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid App ID']);
    exit();
}

// ── Check if this app is already in the user's favorites ──
$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND app_id = ?");
$stmt->execute([$user_id, $app_id]);
$fav = $stmt->fetch();

if ($fav) {
    // ── Already favorited → REMOVE it (toggle OFF) ──
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE id = ?");
    $stmt->execute([$fav['id']]);
    echo json_encode(['status' => 'removed']);
} else {
    // ── Not favorited → ADD it (toggle ON) ──
    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, app_id, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$user_id, $app_id]);
    echo json_encode(['status' => 'added']);
}
