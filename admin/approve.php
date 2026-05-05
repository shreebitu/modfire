<?php
/**
 * ============================================================
 * ADMIN: APPROVE / REJECT APP HANDLER
 * ============================================================
 * Purpose: Processes admin actions to approve or reject submitted apps.
 *          On approval, auto-generates an SEO-friendly URL slug.
 *          Sends a notification to the app's submitter.
 *
 * Input:   GET: id (app ID), action ('approve' or 'reject')
 * Output:  Redirect back to admin/apps.php
 * Security: Admin-only access, no CSRF (should be added — see docs)
 * Connects to: apps table, notifications table
 * ============================================================
 */
require_once '../includes/init.php';

// ── Admin-Only Access Gate ──
if (!isAdmin()) {
    redirect('../auth/login.php');
}

// ── Process the approve/reject action ──
if (isset($_GET['id']) && isset($_GET['action'])) {
    // Sanitize inputs
    $id = (int)$_GET['id'];                    // App ID
    $action = $_GET['action'];                 // 'approve' or 'reject'
    
    // Map the action to a database status value
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    
    // ── Step 1: Get the app details for notification and slug generation ──
    $stmt = $pdo->prepare("SELECT name, user_id FROM apps WHERE id = ?");
    $stmt->execute([$id]);
    $app = $stmt->fetch();
    
    if ($app) {
        // ── Step 2: Generate SEO slug on approval ──
        // Slugs are URL-friendly versions of the app name (e.g., "WhatsApp Mod" → "whatsapp-mod-download")
        $stmtSlug = $pdo->prepare("SELECT slug FROM apps WHERE id = ?");
        $stmtSlug->execute([$id]);
        $existingSlug = $stmtSlug->fetchColumn();
        
        // Only generate a slug if one doesn't already exist AND the app is being approved
        if (empty($existingSlug) && $action === 'approve') {
            // Convert name to lowercase, replace non-alphanumeric chars with hyphens
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $app['name'])));
            // Append '-download' for SEO (targets "X download" search queries)
            $slug = $slug . '-download';
            
            // ── Ensure slug uniqueness ──
            // If another app already has this slug, append the ID to make it unique
            $check = $pdo->prepare("SELECT COUNT(*) FROM apps WHERE slug = ?");
            $check->execute([$slug]);
            if ($check->fetchColumn() > 0) {
                $slug = $slug . '-' . $id;
            }
            
            // Update both status and slug in a single query
            $stmtUpdate = $pdo->prepare("UPDATE apps SET status = ?, slug = ? WHERE id = ?");
            $stmtUpdate->execute([$status, $slug, $id]);
        } else {
            // App already has a slug or is being rejected — just update status
            $stmt = $pdo->prepare("UPDATE apps SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
        }
        
        // ── Step 3: Notify the submitting user ──
        // Creates an in-app notification that appears in their account
        $message = "Your submission '" . $app['name'] . "' has been " . $status . " by the moderator.";
        $notifyStmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
        $notifyStmt->execute([$app['user_id'], $message, 'app_status']);
    }
}

// ── Redirect back to the apps management page ──
redirect('apps.php');
?>
