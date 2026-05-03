<?php
require_once '../config.php';
require_once '../db.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

// Handle dismiss
if (isset($_GET['dismiss'])) {
    $id = (int)$_GET['dismiss'];
    $stmt = $pdo->prepare("DELETE FROM reports WHERE id = ?");
    $stmt->execute([$id]);
    redirect('reports.php');
}

// Fetch reports
$query = "SELECT reports.*, apps.name as app_name, users.username as reporter_name 
          FROM reports 
          JOIN apps ON reports.app_id = apps.id 
          JOIN users ON reports.user_id = users.id 
          ORDER BY reports.created_at DESC";
$stmt = $pdo->query($query);
$reports = $stmt->fetchAll();

// Reports count for badge
$reportsCount = count($reports);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reports - Admin Panel</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">

    <style>
        :root {
            --primary: #1f108e;
            --primary-light: #eef2ff;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-900: #0f172a;
        }
        body { font-family: 'Inter', sans-serif !important; background-color: #f7f9fb !important; color: #1e293b; }
        .sidebar { background: var(--slate-50) !important; border-right: 1px solid var(--slate-200); }
        .sidebar-mobile { position:fixed; top:0; left:0; bottom:0; width:260px; z-index:999 !important; transform:translateX(-100%); transition:transform 0.3s cubic-bezier(0.4,0,0.2,1); display:flex !important; flex-direction:column; box-shadow:20px 0 50px rgba(0,0,0,0.1); background: var(--slate-50) !important; }
        .sidebar-mobile.open { transform:translateX(0); }
        .sidebar-overlay { position:fixed; inset:0; background:rgba(15, 23, 42, 0.3); z-index:998 !important; opacity:0; pointer-events:none; transition:opacity 0.3s ease; }
        .sidebar-overlay.open { opacity:1; pointer-events:all; }
        .nav-item { transition: all 0.2s; font-weight: 500; font-size: 14px; color: var(--slate-600); }
        .nav-item:hover { background-color: var(--slate-100); color: var(--primary); }
        .nav-item.active { background-color: #ffffff; color: var(--primary); border-right: 3px solid var(--primary); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .header-blur { background:#ffffff !important; border-bottom:1px solid var(--slate-200); }
        .material-symbols-outlined { font-size: 20px; }
        .card-white { background: #ffffff; border-radius: 24px; border: 1px solid var(--slate-200); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .main-scroll::-webkit-scrollbar { width: 5px; }
        .main-scroll::-webkit-scrollbar-track { background: transparent; }
        .main-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-[#1d1d1f]">

<?php include 'components/sidebar.php'; ?>
<?php include 'components/mobile_sidebar.php'; ?>

<!-- Main Area -->
<main class="flex-1 flex flex-col h-full bg-[#f7f9fb] relative z-10 w-full overflow-hidden">
    <?php include 'components/header.php'; ?>

    <div class="flex-1 overflow-y-auto px-4 md:px-8 lg:px-10 pb-12 main-scroll">
        <div class="max-w-[1200px] mx-auto mt-6">
            
            <div class="card-white overflow-hidden bg-white">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Reported Asset</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Reporter Details</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Issue Description</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (count($reports) > 0): ?>
                            <?php foreach ($reports as $r): ?>
                                <tr class="hover:bg-slate-50/50 transition-all group">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center text-red-500">
                                                <span class="material-symbols-outlined">warning</span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($r['app_name']); ?></p>
                                                <p class="text-[10px] text-indigo-600 font-bold uppercase tracking-widest mt-0.5">Asset ID: #<?php echo $r['app_id']; ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="text-[13px] font-bold text-slate-700"><?php echo htmlspecialchars($r['reporter_name']); ?></p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase mt-1"><?php echo date('M d, Y • H:i', strtotime($r['created_at'])); ?></p>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="max-w-md">
                                            <p class="text-[13px] text-slate-600 leading-relaxed italic">"<?php echo htmlspecialchars($r['reason']); ?>"</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="apps.php?delete=<?php echo $r['app_id']; ?>" onclick="return confirm('CRITICAL: Delete reported application?')" class="p-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Delete App">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </a>
                                            <a href="reports.php?dismiss=<?php echo $r['id']; ?>" class="p-2.5 bg-slate-50 text-slate-400 rounded-xl hover:bg-indigo-600 hover:text-white transition-all shadow-sm" title="Dismiss Report">
                                                <span class="material-symbols-outlined text-[18px]">done_all</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-24 text-center">
                                    <div class="w-20 h-20 bg-green-50 text-green-500 rounded-[30px] flex items-center justify-center mx-auto mb-6">
                                        <span class="material-symbols-outlined text-4xl">verified</span>
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900 mb-1">Clear Horizon</h3>
                                    <p class="text-slate-400 text-sm">All user reports have been resolved. Excellent work!</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const btn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('mainSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (btn && sidebar && overlay) {
            btn.addEventListener('click', () => {
                sidebar.classList.remove('hidden');
                sidebar.classList.add('fixed', 'inset-y-0', 'left-0', 'z-[120]', 'w-[260px]', 'shadow-2xl', 'flex');
                overlay.classList.add('open');
            });
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('fixed', 'inset-y-0', 'left-0', 'z-[120]', 'w-[260px]', 'shadow-2xl', 'flex');
                sidebar.classList.add('hidden');
                overlay.classList.remove('open');
            });
        }
    });
</script>

</body>
</html>
