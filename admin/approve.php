<?php
require_once '../config.php';
require_once '../db.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    
    // Get app details for notification
    $stmt = $pdo->prepare("SELECT name, user_id FROM apps WHERE id = ?");
    $stmt->execute([$id]);
    $app = $stmt->fetch();
    
    if ($app) {
        // Generate slug if it doesn't exist
        $stmtSlug = $pdo->prepare("SELECT slug FROM apps WHERE id = ?");
        $stmtSlug->execute([$id]);
        $existingSlug = $stmtSlug->fetchColumn();
        
        if (empty($existingSlug) && $action === 'approve') {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $app['name'])));
            $slug = $slug . '-download';
            
            // Ensure uniqueness
            $check = $pdo->prepare("SELECT COUNT(*) FROM apps WHERE slug = ?");
            $check->execute([$slug]);
            if ($check->fetchColumn() > 0) {
                $slug = $slug . '-' . $id;
            }
            
            $stmtUpdate = $pdo->prepare("UPDATE apps SET status = ?, slug = ? WHERE id = ?");
            $stmtUpdate->execute([$status, $slug, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE apps SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
        }
        
        // Notify User
        $message = "Your submission '" . $app['name'] . "' has been " . $status . " by the moderator.";
        $notifyStmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
        $notifyStmt->execute([$app['user_id'], $message, 'app_status']);
    }
}

redirect('apps.php');
?>
