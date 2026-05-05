<?php
/**
 * ============================================================
 * AJAX: TOGGLE FAVORITE (POST method — CSRF-protected)
 * ============================================================
 * Purpose: Adds or removes an app from the user's favorites.
 *          This is the SECURE version of favorites_action.php
 *          because it uses POST method with CSRF token verification.
 *
 * Method:  POST (requires CSRF token)
 * Input:   POST: app_id, csrf_token
 * Output:  JSON: { success: true/false, action: "added"|"removed" }
 * Security: Login required + CSRF protected
 * ============================================================
 */
require_once '../includes/init.php';

// Set response type to JSON for AJAX consumption
header('Content-Type: application/json');

// ── Authentication Check ──
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// ── Only accept POST requests ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Verify CSRF token to prevent cross-site attacks
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $user_id = $_SESSION['user_id'];
    $app_id = (int)($_POST['app_id'] ?? 0);

    // Step 2: Validate the app ID
    if ($app_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid App ID']);
        exit();
    }

    // Step 3: Check if this app is already in the user's favorites
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND app_id = ?");
    $stmt->execute([$user_id, $app_id]);
    $fav = $stmt->fetch();

    if ($fav) {
        // ── Already favorited → REMOVE it (toggle OFF) ──
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE id = ?");
        $stmt->execute([$fav['id']]);
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        // ── Not favorited → ADD it (toggle ON) ──
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, app_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $app_id]);
        echo json_encode(['success' => true, 'action' => 'added']);
    }
}
?>
