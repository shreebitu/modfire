<?php
require_once '../includes/init.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

$success = '';

// Handle Bulk Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $action = $_POST['action_type'];
    $app_ids = $_POST['selected_apps'] ?? [];
    
    if (!empty($app_ids)) {
        $placeholders = implode(',', array_fill(0, count($app_ids), '?'));
        
        if ($action === 'approve') {
            foreach ($app_ids as $app_id) {
                $id = (int)$app_id;
                $stmtSlug = $pdo->prepare("SELECT name, slug FROM apps WHERE id = ?");
                $stmtSlug->execute([$id]);
                $appData = $stmtSlug->fetch();
                
                if ($appData && empty($appData['slug'])) {
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $appData['name'])));
                    $slug = $slug . '-download';
                    $check = $pdo->prepare("SELECT COUNT(*) FROM apps WHERE slug = ?");
                    $check->execute([$slug]);
                    if ($check->fetchColumn() > 0) { $slug = $slug . '-' . $id; }
                    $pdo->prepare("UPDATE apps SET status = 'approved', slug = ? WHERE id = ?")->execute([$slug, $id]);
                } else {
                    $pdo->prepare("UPDATE apps SET status = 'approved' WHERE id = ?")->execute([$id]);
                }
            }
            $success = count($app_ids) . " apps approved.";
            logActivity('bulk_action', "Bulk approved " . count($app_ids) . " apps");
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE apps SET status = 'rejected' WHERE id IN ($placeholders)");
            $stmt->execute($app_ids);
            $success = count($app_ids) . " apps rejected.";
            logActivity('bulk_action', "Bulk rejected " . count($app_ids) . " apps");
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM apps WHERE id IN ($placeholders)");
            $stmt->execute($app_ids);
            $success = count($app_ids) . " apps permanently deleted.";
            logActivity('bulk_action', "Bulk deleted " . count($app_ids) . " apps");
        }
    }
}

// Single delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM apps WHERE id = ?");
    $stmt->execute([$id]);
    logActivity('admin_action', "Deleted app ID: $id");
    $success = "Application deleted.";
}

// Fetch apps
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$query = "SELECT apps.*, users.username FROM apps 
          JOIN users ON apps.user_id = users.id";
if ($status_filter !== 'all') {
    $query .= " WHERE apps.status = " . $pdo->quote($status_filter);
}
$query .= " ORDER BY apps.created_at DESC";

$stmt = $pdo->query($query);
$apps = $stmt->fetchAll();

$reportsCount = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Command - ShreeBitu Admin</title>
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
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Content Moderation</h2>
                <p class="text-slate-500 mt-1 font-medium">Review, approve, or purge submissions across the entire catalog.</p>
            </div>
            <div class="flex bg-slate-100 rounded-2xl p-1 shadow-inner">
                <a href="apps.php?status=all" class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all <?php echo $status_filter === 'all' ? 'bg-white text-indigo-900 shadow-sm' : 'text-slate-500 hover:text-indigo-600'; ?>">All</a>
                <a href="apps.php?status=pending" class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all <?php echo $status_filter === 'pending' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-500 hover:text-indigo-600'; ?>">Pending</a>
                <a href="apps.php?status=approved" class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all <?php echo $status_filter === 'approved' ? 'bg-indigo-900 text-white shadow-sm' : 'text-slate-500 hover:text-indigo-600'; ?>">Approved</a>
            </div>
        </header>

        <?php if ($success): ?>
            <div class="bg-indigo-900 text-white px-6 py-4 rounded-3xl mb-8 flex items-center gap-4 shadow-xl shadow-indigo-100">
                <span class="material-symbols-outlined text-sm">inventory</span>
                <span class="text-xs font-black uppercase tracking-widest"><?php echo $success; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" id="bulkForm">
            <!-- Bulk Action Toolbar -->
            <div id="bulkToolbar" class="hidden mb-6 p-4 bg-indigo-900 rounded-3xl flex items-center justify-between shadow-2xl shadow-indigo-200 animate-in slide-in-from-bottom duration-300">
                <div class="flex items-center gap-4 px-2">
                    <span class="text-[10px] font-black text-indigo-200 uppercase tracking-[0.2em]">Selected: <span id="selectedCount" class="text-white">0</span></span>
                    <div class="h-4 w-px bg-indigo-700"></div>
                </div>
                <div class="flex gap-2">
                    <input type="hidden" name="action_type" id="bulkActionType">
                    <button type="button" onclick="runBulk('approve')" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Approve</button>
                    <button type="button" onclick="runBulk('reject')" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Reject</button>
                    <button type="button" onclick="runBulk('delete')" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Delete Forever</button>
                    <button type="submit" name="bulk_action" id="submitBulk" class="hidden"></button>
                </div>
            </div>

            <div class="card-white overflow-hidden">
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left whitespace-nowrap min-w-[800px]">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-4 w-10">
                                <input type="checkbox" onclick="toggleAll(this)" class="w-4 h-4 rounded border-slate-300 text-indigo-600">
                            </th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Application</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Uploader</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Category</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (empty($apps)): ?>
                            <tr>
                                <td colspan="6" class="py-20 text-center">
                                    <span class="material-symbols-outlined text-4xl text-slate-200 mb-2">inventory_2</span>
                                    <p class="text-sm text-slate-400 font-medium">No applications found in this category.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($apps as $app): ?>
                            <tr class="hover:bg-slate-50/30 transition-all group">
                                <td class="px-6 py-4">
                                    <input type="checkbox" name="selected_apps[]" value="<?php echo $app['id']; ?>" class="app-selector w-4 h-4 rounded border-slate-300 text-indigo-600" onchange="updateBulkUI()">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-11 h-11 rounded-2xl bg-white border border-slate-200 p-1.5 flex-shrink-0 shadow-sm">
                                            <?php 
                                            $logo_path = $app['logo'];
                                            if (strpos($logo_path, 'http') !== 0) {
                                                $logo_path = ltrim(str_replace('../', '', $logo_path), '/');
                                                $logo_path = $base_url . $logo_path;
                                            }
                                            $logo_src = $logo_path;
                                            ?>
                                            <img src="<?php echo htmlspecialchars($logo_src); ?>" onerror="this.src='<?php echo $base_url; ?>assets/images/logo.png';" class="w-full h-full object-contain">
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($app['name']); ?></p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter mt-0.5">ID: #<?php echo $app['id']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs font-bold text-slate-600"><?php echo htmlspecialchars($app['username']); ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-[9px] font-black text-indigo-600 bg-indigo-50 px-2 py-1 rounded-lg uppercase tracking-widest border border-indigo-100">
                                        <?php echo htmlspecialchars($app['category_name'] ?? $app['category']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border
                                        <?php echo $app['status'] === 'approved' ? 'bg-green-50 text-green-700 border-green-200' : 
                                            ($app['status'] === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-red-50 text-red-700 border-red-200'); ?>">
                                        <?php echo $app['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="edit.php?id=<?php echo $app['id']; ?>" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Edit Metadata">
                                            <span class="material-symbols-outlined text-[18px]">edit_note</span>
                                        </a>
                                        <a href="apps.php?delete=<?php echo $app['id']; ?>" onclick="return confirm('Delete permanently?')" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-all" title="Purge">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
    function toggleAll(master) {
        document.querySelectorAll('.app-selector').forEach(cb => {
            cb.checked = master.checked;
        });
        updateBulkUI();
    }

    function updateBulkUI() {
        const checked = document.querySelectorAll('.app-selector:checked');
        const toolbar = document.getElementById('bulkToolbar');
        const count = document.getElementById('selectedCount');
        
        if (checked.length > 0) {
            toolbar.classList.remove('hidden');
            count.innerText = checked.length;
        } else {
            toolbar.classList.add('hidden');
        }
    }

    function runBulk(type) {
        if (type === 'delete' && !confirm('Are you sure you want to delete these applications permanently?')) return;
        document.getElementById('bulkActionType').value = type;
        document.getElementById('submitBulk').click();
    }
</script>

</body>
</html>
