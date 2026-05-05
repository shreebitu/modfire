<?php
/**
 * ============================================================
 * DOWNLOAD HANDLER
 * ============================================================
 * Purpose: Processes download requests for apps.
 *          Validates the app, checks authorization, increments
 *          download counters, logs the download, then redirects
 *          the user to the actual file URL.
 *
 * Input:   GET: id (app ID to download)
 * Output:  302 redirect to the file URL, or styled error page
 * Connects to: includes/init.php, apps table, downloads_log table
 * ============================================================
 */
require_once 'includes/init.php';

try {
    // ── Step 1: Validate the app ID from the URL ──
    // Cast to integer to prevent any injection; reject if <= 0
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        throw new Exception("Invalid application request.");
    }

    // ── Step 2: Fetch the app's download link and status from database ──
    // Only retrieve the columns we need for performance
    $stmt = $pdo->prepare("SELECT name, apk_link, status FROM apps WHERE id = ?");
    $stmt->execute([$id]);
    $app = $stmt->fetch();

    // ── Step 3: Check if the app exists in the database ──
    if (!$app) {
        throw new Exception("The application you are looking for does not exist.");
    }

    // ── Step 4: Authorization check ──
    // Approved apps: anyone can download
    // Pending/rejected apps: only the owner or admins can download (for testing)
    $is_authorized = ($app['status'] === 'approved') || isAdmin() || (isLoggedIn() && isOwner($id));
    if (!$is_authorized) {
        throw new Exception("This application is currently pending review and is not available for download.");
    }

    // ── Step 5: Validate the download link exists ──
    $redirect_url = $app['apk_link'];
    if (empty($redirect_url)) {
        throw new Exception("Download link is currently unavailable for this application.");
    }

    // ── Step 6: Track download statistics ──
    // Wrapped in try/catch so stats failures don't block the download
    try {
        // Increment the download counter on the apps table
        $updateStmt = $pdo->prepare("UPDATE apps SET downloads = downloads + 1 WHERE id = ?");
        $updateStmt->execute([$id]);

        // Insert a detailed download log entry (IP + browser for analytics)
        $logStmt = $pdo->prepare("INSERT INTO downloads_log (app_id, ip_address, user_agent) VALUES (?, ?, ?)");
        $logStmt->execute([$id, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown']);
        
        // Record in the activity log for audit trail
        logActivity('download', "Downloaded application: " . $app['name']);
    } catch (PDOException $e) {
        // Log the error server-side but never block the user's download
        error_log("Download stats error: " . $e->getMessage());
    }

    // ── Step 7: Redirect the user to the actual file URL ──
    // If the link is a relative path (local file), prepend the base URL
    if (strpos($redirect_url, 'http') !== 0) {
        $redirect_url = $base_url . ltrim($redirect_url, '/');
    }

    // Send the browser to the download URL
    header("Location: " . $redirect_url);
    exit();

} catch (Exception $e) {
    // 8. Error Display
    // If it's a critical error, show a polished error page instead of die()
    $error_message = $e->getMessage();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Download Error - ShreeBitu</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
        <style>
            body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; }
            .error-card { background: white; border-radius: 32px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); }
        </style>
    </head>
    <body class="min-h-screen flex items-center justify-center p-6">
        <div class="max-w-md w-full error-card p-10 text-center">
            <div class="w-20 h-20 bg-red-50 text-red-500 rounded-[30px] flex items-center justify-center mx-auto mb-6">
                <span class="material-symbols-outlined text-4xl">error</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mb-3 tracking-tight">Download Failed</h1>
            <p class="text-slate-500 leading-relaxed mb-8"><?php echo htmlspecialchars($error_message); ?></p>
            
            <div class="flex flex-col gap-3">
                <a href="index.php" class="bg-indigo-900 text-white py-3.5 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-indigo-800 transition-all shadow-lg shadow-indigo-100">Back to Home</a>
                <button onclick="history.back()" class="text-slate-400 font-bold text-xs uppercase tracking-widest hover:text-slate-600 transition-colors py-2">Go Back</button>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}
