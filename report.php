<?php
require_once 'config.php';
require_once 'db.php';

if (!isLoggedIn()) {
    redirect('auth/login.php');
}

$app_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['app_id']) ? (int)$_POST['app_id'] : 0);

if ($app_id <= 0) {
    die("Invalid application ID.");
}

// Fetch app info
$stmt = $pdo->prepare("SELECT name FROM apps WHERE id = ?");
$stmt->execute([$app_id]);
$app = $stmt->fetch();

if (!$app) {
    die("Application not found.");
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $reason = sanitizeInput($_POST['reason'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (empty($reason)) {
        $error = "Please specify a reason for reporting.";
    } else {
        // Rate limiting for reports (max 5 per hour)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stmt->execute([$user_id]);
        if ($stmt->fetchColumn() >= 5) {
            $error = "You have reached the limit of 5 reports per hour. Please try again later.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO reports (app_id, user_id, reason) VALUES (?, ?, ?)");
            if ($stmt->execute([$app_id, $user_id, $reason])) {
                $success = true;
                logActivity('report_app', "Reported application: " . $app['name']);
            } else {
                $error = "Failed to submit report. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Issue - ShreeBitu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?php echo $assets_url; ?>images/logo.png">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; color: #1e293b; }
        .card { background: white; border-radius: 32px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full card p-10">
        <?php if ($success): ?>
            <div class="text-center">
                <div class="w-20 h-20 bg-green-50 text-green-500 rounded-[30px] flex items-center justify-center mx-auto mb-6">
                    <span class="material-symbols-outlined text-4xl">check_circle</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 mb-3 tracking-tight">Report Submitted</h1>
                <p class="text-slate-500 leading-relaxed mb-8">Thank you for helping us keep ShreeBitu safe. Our team will review your report shortly.</p>
                <a href="app.php?id=<?php echo $app_id; ?>" class="w-full bg-indigo-900 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-indigo-800 transition-all shadow-lg shadow-indigo-100 inline-block text-center">Return to App</a>
            </div>
        <?php else: ?>
            <div class="mb-8">
                <h1 class="text-2xl font-black text-slate-900 mb-2 tracking-tight">Report Content</h1>
                <p class="text-slate-500 text-sm">You are reporting: <span class="font-bold text-indigo-600"><?php echo htmlspecialchars($app['name']); ?></span></p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-xl mb-6 text-sm font-medium flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">error</span>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <input type="hidden" name="app_id" value="<?php echo $app_id; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block ml-1">Reason for reporting</label>
                    <select name="reason" class="w-full px-5 py-4 rounded-2xl border border-slate-200 bg-slate-50 outline-none focus:border-indigo-600 transition-all text-sm font-medium">
                        <option value="">Select a reason...</option>
                        <option value="Broken Link / Download failed">Broken Link / Download failed</option>
                        <option value="Malware / Virus detected">Malware / Virus detected</option>
                        <option value="Inappropriate Content">Inappropriate Content</option>
                        <option value="Misleading Information">Misleading Information</option>
                        <option value="Copyright Infringement">Copyright Infringement</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <button type="submit" name="submit_report" class="w-full bg-red-600 text-white py-4 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-red-700 transition-all shadow-lg shadow-red-100 active:scale-95">Submit Report</button>
                <a href="app.php?id=<?php echo $app_id; ?>" class="w-full text-center block text-slate-400 font-bold text-xs uppercase tracking-widest hover:text-slate-600 transition-colors py-2">Cancel</a>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>
