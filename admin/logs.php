<?php
require_once '../config.php';
require_once '../db.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';

// Handle Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');
    
    if (isset($_POST['clear_all'])) {
        $stmt = $pdo->prepare("DELETE FROM activity_logs");
        $stmt->execute();
        logActivity('admin_action', "Cleared all activity logs");
        redirect("logs.php?success=All logs have been cleared successfully.");
    } elseif (isset($_POST['delete_selected'])) {
        $log_ids = $_POST['selected_logs'] ?? [];
        if (!empty($log_ids)) {
            $placeholders = implode(',', array_fill(0, count($log_ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE id IN ($placeholders)");
            $stmt->execute($log_ids);
            logActivity('admin_action', "Deleted " . count($log_ids) . " selected logs");
            redirect("logs.php?success=" . count($log_ids) . " logs deleted.");
        }
    }
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

$stmt = $pdo->query("SELECT activity_logs.*, users.username FROM activity_logs LEFT JOIN users ON activity_logs.user_id = users.id ORDER BY activity_logs.created_at DESC LIMIT $limit OFFSET $offset");
$logs = $stmt->fetchAll();

$total_logs = $pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
$total_pages = ceil($total_logs / $limit);

$reportsCount = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Audit - Admin Panel</title>
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
    </style>
</head>
<body class="flex h-screen overflow-hidden">

<?php include 'components/sidebar.php'; ?>
<?php include 'components/mobile_sidebar.php'; ?>

<main class="flex-1 flex flex-col h-full bg-[#f8fafc] relative z-10 w-full overflow-hidden">
    <?php include 'components/header.php'; ?>

    <div class="flex-1 overflow-y-auto p-6 lg:p-10 main-scroll">
        <div class="max-w-[1200px] mx-auto">
        <header class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Action Audit Log</h2>
                <p class="text-slate-500 mt-1 font-medium italic text-sm">Reviewing <span class="text-indigo-600 font-bold"><?php echo number_format($total_logs); ?></span> historical system events.</p>
            </div>
            <div class="flex gap-3">
                <form method="POST" onsubmit="return confirm('WARNING: This will permanently delete ALL activity logs. Proceed?')">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <button type="submit" name="clear_all" class="px-5 py-2.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm border border-red-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">delete_sweep</span> Clear All Logs
                    </button>
                </form>
            </div>
        </header>

        <?php if ($success): ?>
            <div class="bg-indigo-900 text-white px-6 py-4 rounded-3xl mb-8 flex items-center gap-4 shadow-xl shadow-indigo-100">
                <span class="material-symbols-outlined text-sm">history</span>
                <span class="text-xs font-black uppercase tracking-widest"><?php echo $success; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" id="bulkForm">
            <!-- Bulk Action Toolbar -->
            <div id="bulkToolbar" class="hidden mb-6 p-4 bg-indigo-950 rounded-3xl flex items-center justify-between shadow-2xl animate-in slide-in-from-bottom duration-300">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="flex items-center gap-4 px-2">
                    <span class="text-[10px] font-black text-indigo-300 uppercase tracking-[0.2em]">Selected: <span id="selectedCount" class="text-white">0</span></span>
                    <div class="h-4 w-px bg-indigo-800"></div>
                </div>
                <button type="submit" name="delete_selected" onclick="return confirm('Delete selected log entries?')" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Delete Selected</button>
            </div>

            <div class="card-white overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 w-10">
                                <input type="checkbox" onclick="toggleAll(this)" class="w-4 h-4 rounded border-slate-300 text-indigo-600">
                            </th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Timestamp</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">User</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Action</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Details</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach ($logs as $log): ?>
                            <tr class="hover:bg-slate-50/30 transition-all group">
                                <td class="px-6 py-4">
                                    <input type="checkbox" name="selected_logs[]" value="<?php echo $log['id']; ?>" class="log-selector w-4 h-4 rounded border-slate-300 text-indigo-600" onchange="updateBulkUI()">
                                </td>
                                <td class="px-6 py-4 text-[10px] font-bold text-slate-400 whitespace-nowrap">
                                    <?php echo date('M d, H:i:s', strtotime($log['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-[11px] font-black <?php echo $log['username'] ? 'text-indigo-900' : 'text-slate-400'; ?>">
                                        <?php echo htmlspecialchars($log['username'] ?: 'GUEST'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-black border border-slate-200 uppercase tracking-widest
                                        <?php 
                                            if(strpos($log['action'], 'login') !== false) echo 'bg-green-50 text-green-700 border-green-100';
                                            elseif(strpos($log['action'], 'failed') !== false) echo 'bg-red-50 text-red-700 border-red-100';
                                            elseif(strpos($log['action'], 'admin') !== false) echo 'bg-indigo-50 text-indigo-700 border-indigo-100';
                                            else echo 'bg-slate-50 text-slate-600';
                                        ?>">
                                        <?php echo htmlspecialchars($log['action']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-[11px] font-medium text-slate-500 max-w-[300px] truncate" title="<?php echo htmlspecialchars($log['details']); ?>">
                                    <?php echo htmlspecialchars($log['details']); ?>
                                </td>
                                <td class="px-6 py-4 font-mono text-[9px] text-slate-400">
                                    <?php echo htmlspecialchars($log['ip_address']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="mt-8 flex justify-center gap-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition-all">Previous</a>
                <?php endif; ?>
                
                <div class="px-6 py-2 bg-indigo-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center">
                    Page <?php echo $page; ?> / <?php echo $total_pages; ?>
                </div>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition-all">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
    function toggleAll(master) {
        document.querySelectorAll('.log-selector').forEach(cb => {
            cb.checked = master.checked;
        });
        updateBulkUI();
    }

    function updateBulkUI() {
        const checked = document.querySelectorAll('.log-selector:checked');
        const toolbar = document.getElementById('bulkToolbar');
        const count = document.getElementById('selectedCount');
        
        if (checked.length > 0) {
            toolbar.classList.remove('hidden');
            count.innerText = checked.length;
        } else {
            toolbar.classList.add('hidden');
        }
    }
</script>

</body>
</html>
