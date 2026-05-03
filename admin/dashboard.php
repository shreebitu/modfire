<?php
require_once '../config.php';
require_once '../db.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

// Get basic stats
$usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$bannedCount = $pdo->query("SELECT COUNT(*) FROM users WHERE is_banned = 1")->fetchColumn();
$shadowCount = $pdo->query("SELECT COUNT(*) FROM users WHERE shadow_banned = 1")->fetchColumn();

$appsCount = $pdo->query("SELECT COUNT(*) FROM apps")->fetchColumn();
$pendingCount = $pdo->query("SELECT COUNT(*) FROM apps WHERE status = 'pending'")->fetchColumn();

// Detailed content stats
$apkCount = $pdo->query("SELECT COUNT(*) FROM apps WHERE category LIKE '%apk%' OR category LIKE '%Apps%'")->fetchColumn();
$pdfCount = $pdo->query("SELECT COUNT(*) FROM apps WHERE category LIKE '%pdf%' OR category LIKE '%Notes%'")->fetchColumn();
$pptCount = $pdo->query("SELECT COUNT(*) FROM apps WHERE category LIKE '%ppt%' OR category LIKE '%Presentations%'")->fetchColumn();

$downloadsTotal = $pdo->query("SELECT SUM(downloads) FROM apps")->fetchColumn() ?: 0;
$dailyDownloads = $pdo->query("SELECT COUNT(*) FROM downloads_log WHERE downloaded_at >= CURDATE()")->fetchColumn();

$reportsCount = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();

// Recent Activity
$stmtLogs = $pdo->query("SELECT activity_logs.*, users.username FROM activity_logs LEFT JOIN users ON activity_logs.user_id = users.id ORDER BY activity_logs.created_at DESC LIMIT 6");
$recentLogs = $stmtLogs->fetchAll();

// System Statuses
$maintenanceMode = ($site_settings['maintenance_mode'] ?? '0') == '1';
$regEnabled = ($site_settings['registration_enabled'] ?? '1') == '1';
$uploadEnabled = ($site_settings['upload_enabled'] ?? '1') == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Control Dashboard - ShreeBitu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --primary: #1f108e; --slate-50: #f8fafc; --slate-100: #f1f5f9; --slate-200: #e2e8f0; }
        body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; color: #1e293b; }
        .sidebar { background: var(--slate-50); border-right: 1px solid var(--slate-200); }
        .nav-item { transition: all 0.2s; font-weight: 500; font-size: 14px; color: #64748b; }
        .nav-item:hover { background-color: var(--slate-100); color: var(--primary); }
        .nav-item.active { background-color: #ffffff; color: var(--primary); border-right: 3px solid var(--primary); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .card-white { background: #ffffff; border-radius: 24px; border: 1px solid var(--slate-200); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .status-dot { width: 8px; h: 8px; border-radius: 50%; }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

<?php include 'components/sidebar.php'; ?>
<?php include 'components/mobile_sidebar.php'; ?>

<main class="flex-1 flex flex-col h-full bg-[#f8fafc] relative z-10 w-full overflow-hidden">
    <?php include 'components/header.php'; ?>

    <div class="flex-1 overflow-y-auto p-6 lg:p-10 main-scroll">
        <div class="max-w-[1200px] mx-auto">
        
        <header class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">System Command Center</h2>
                <p class="text-slate-500 mt-1 font-medium italic">Welcome back, Super Admin. Everything is under your control.</p>
            </div>
            
            <!-- Quick System Status Badges -->
            <div class="flex flex-wrap gap-2">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full border <?php echo $maintenanceMode ? 'bg-red-50 border-red-100 text-red-600' : 'bg-green-50 border-green-100 text-green-600'; ?>">
                    <span class="status-dot <?php echo $maintenanceMode ? 'bg-red-500' : 'bg-green-500'; ?>"></span>
                    <span class="text-[10px] font-black uppercase tracking-widest"><?php echo $maintenanceMode ? 'Maintenance ON' : 'System Live'; ?></span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full border <?php echo $regEnabled ? 'bg-indigo-50 border-indigo-100 text-indigo-600' : 'bg-slate-100 border-slate-200 text-slate-500'; ?>">
                    <span class="status-dot <?php echo $regEnabled ? 'bg-indigo-500' : 'bg-slate-400'; ?>"></span>
                    <span class="text-[10px] font-black uppercase tracking-widest">Reg: <?php echo $regEnabled ? 'Enabled' : 'Closed'; ?></span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full border <?php echo $uploadEnabled ? 'bg-indigo-50 border-indigo-100 text-indigo-600' : 'bg-slate-100 border-slate-200 text-slate-500'; ?>">
                    <span class="status-dot <?php echo $uploadEnabled ? 'bg-indigo-500' : 'bg-slate-400'; ?>"></span>
                    <span class="text-[10px] font-black uppercase tracking-widest">Uploads: <?php echo $uploadEnabled ? 'Open' : 'Locked'; ?></span>
                </div>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="card-white p-6 border-l-4 border-indigo-600">
                <div class="flex justify-between items-start mb-4">
                    <span class="material-symbols-outlined text-indigo-600 bg-indigo-50 p-2 rounded-xl">person_outline</span>
                    <div class="text-right">
                        <span class="text-[10px] font-black text-red-600 bg-red-50 px-2 py-0.5 rounded-lg"><?php echo $bannedCount; ?> Banned</span>
                        <?php if($shadowCount > 0): ?>
                            <span class="text-[10px] font-black text-amber-600 bg-amber-50 px-2 py-0.5 rounded-lg block mt-1"><?php echo $shadowCount; ?> Shadow</span>
                        <?php endif; ?>
                    </div>
                </div>
                <p class="text-3xl font-black text-slate-900"><?php echo number_format($usersCount); ?></p>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-1">Platform Users</p>
            </div>

            <div class="card-white p-6 border-l-4 border-purple-600">
                <div class="flex justify-between items-start mb-4">
                    <span class="material-symbols-outlined text-purple-600 bg-purple-50 p-2 rounded-xl">inventory_2</span>
                    <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-lg"><?php echo $pendingCount; ?> Pending Review</span>
                </div>
                <p class="text-3xl font-black text-slate-900"><?php echo number_format($appsCount); ?></p>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-1">Shared Contents</p>
            </div>

            <div class="card-white p-6 border-l-4 border-pink-600">
                <div class="flex justify-between items-start mb-4">
                    <span class="material-symbols-outlined text-pink-600 bg-pink-50 p-2 rounded-xl">monitoring</span>
                    <span class="text-[10px] font-black text-green-600 bg-green-50 px-2 py-0.5 rounded-lg">+<?php echo $dailyDownloads; ?> Today</span>
                </div>
                <p class="text-3xl font-black text-slate-900"><?php echo number_format($downloadsTotal); ?></p>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-1">Total Deliveries</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Activity Stream -->
            <div class="lg:col-span-8">
                <div class="card-white overflow-hidden h-full">
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-3">
                            <span class="material-symbols-outlined text-indigo-600">pulse</span>
                            Live System Activity
                        </h3>
                        <a href="logs.php" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:underline">View All Logs</a>
                    </div>
                    <div class="p-2">
                        <?php foreach($recentLogs as $log): ?>
                            <div class="flex items-center gap-4 p-4 hover:bg-slate-50 rounded-2xl transition-all">
                                <div class="w-2 h-2 rounded-full <?php echo strpos($log['action'], 'failed') !== false ? 'bg-red-500' : 'bg-indigo-500'; ?>"></div>
                                <div class="flex-1">
                                    <p class="text-xs font-bold text-slate-900">
                                        <span class="text-indigo-600"><?php echo htmlspecialchars($log['username'] ?: 'Guest'); ?></span>
                                        <?php echo str_replace('_', ' ', htmlspecialchars($log['action'])); ?>
                                    </p>
                                    <p class="text-[10px] text-slate-400 font-medium truncate max-w-[400px]"><?php echo htmlspecialchars($log['details']); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-tighter"><?php echo date('H:i', strtotime($log['created_at'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Global Distribution & Health -->
            <div class="lg:col-span-4 space-y-6">
                <div class="card-white p-6">
                    <h3 class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-6">Content Mix</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-[11px] font-black uppercase mb-1.5">
                                <span>Android Apps</span>
                                <span class="text-indigo-600"><?php echo $apkCount; ?></span>
                            </div>
                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-indigo-600 h-full" style="width: <?php echo $appsCount > 0 ? ($apkCount/$appsCount)*100 : 0; ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-[11px] font-black uppercase mb-1.5">
                                <span>PDF Documents</span>
                                <span class="text-red-600"><?php echo $pdfCount; ?></span>
                            </div>
                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-red-500 h-full" style="width: <?php echo $appsCount > 0 ? ($pdfCount/$appsCount)*100 : 0; ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-[11px] font-black uppercase mb-1.5">
                                <span>Presentations</span>
                                <span class="text-amber-600"><?php echo $pptCount; ?></span>
                            </div>
                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-amber-500 h-full" style="width: <?php echo $appsCount > 0 ? ($pptCount/$appsCount)*100 : 0; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-white p-6 bg-slate-900 border-slate-800">
                    <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4">Infrastructure Health</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-500">Database Status</span>
                            <span class="text-[10px] font-black text-green-400 uppercase tracking-widest">Optimized</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-500">Storage Usage</span>
                            <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">1.4 GB / 10 GB</span>
                        </div>
                        <div class="w-full bg-slate-800 h-1 rounded-full overflow-hidden">
                            <div class="bg-indigo-500 h-full w-[14%]"></div>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-[11px] font-bold text-slate-500">Node Load</span>
                            <span class="text-[10px] font-black text-amber-400 uppercase tracking-widest">Normal (0.12)</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</main>

</body>
</html>
