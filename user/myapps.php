<?php
require_once '../includes/init.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM apps WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$apps = $stmt->fetchAll();

// Optional: Verify user exists to avoid errors
$stmtUser = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
if (!$stmtUser->fetch()) {
    session_destroy();
    redirect('../auth/login.php');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Apps - ShreeBitu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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

        body {
            font-family: 'Inter', sans-serif !important;
            background-color: #f7f9fb !important;
            color: #1e293b;
        }

        .sidebar-overlay.open {
            opacity: 1;
            pointer-events: all;
        }

        .nav-item {
            transition: all 0.2s;
            font-weight: 500;
            font-size: 14px;
            color: var(--slate-600);
        }

        .nav-item:hover {
            background-color: var(--slate-100);
            color: var(--primary);
        }

        .nav-item.active {
            background-color: #ffffff;
            color: var(--primary);
            border-right: 3px solid var(--primary);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .header-blur {
            background: #ffffff !important;
            border-bottom: 1px solid var(--slate-200);
        }

        .material-symbols-outlined {
            font-size: 20px;
        }

        .table-container::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }

        .table-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }

        .main-scroll::-webkit-scrollbar {
            width: 5px;
        }

        .main-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .main-scroll::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
    </style>
</head>

<body class="flex h-screen overflow-hidden text-[#1d1d1f]">

    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Area -->
    <main class="flex-1 flex flex-col h-full bg-[#f7f9fb] relative z-10 w-full overflow-hidden">
        <header
            class="h-16 header-blur flex items-center justify-between px-6 lg:px-8 shrink-0 z-30 sticky top-0 bg-white">
            <div class="flex items-center gap-1 md:gap-4 flex-1">
                <button id="mobileMenuBtn"
                    class="md:hidden text-slate-600 hover:text-indigo-700 focus:outline-none p-1 rounded-xl transition-all flex items-center justify-center">
                    <span class="material-symbols-outlined text-[34px]">menu</span>
                </button>
                <div class="flex md:hidden items-center">
                    <h1 class="text-2xl font-black text-indigo-900 tracking-tighter leading-none">MODFIRE</h1>
                </div>

                <!-- Breadcrumbs / Title -->
                <div class="hidden md:flex items-center gap-2 text-sm text-slate-500">
                    <a href="../index.php" class="hover:text-indigo-600 font-medium transition-colors">Home</a>
                    <span class="material-symbols-outlined text-slate-300">chevron_right</span>
                    <span class="font-bold text-slate-900 uppercase tracking-widest text-[11px]">My Applications</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <?php if (isLoggedIn()): ?>
                    <div class="relative group">
                        <button
                            class="flex items-center gap-3 p-1 pr-4 bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-indigo-200 transition-all group">
                            <div
                                class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-[12px] font-bold shadow-indigo-100 shadow-lg overflow-hidden shrink-0">
                                <?php if (!empty($_SESSION['profile_pic'])): ?>
                                    <img src="../<?php echo htmlspecialchars($_SESSION['profile_pic']); ?>"
                                        class="w-full h-full object-cover">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <div class="text-left hidden sm:block">
                                <p
                                    class="text-[11px] font-bold text-slate-700 group-hover:text-indigo-600 transition-colors leading-none">
                                    <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                                </p>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter mt-1">Profile Hub
                                </p>
                            </div>
                            <span class="material-symbols-outlined text-slate-300 text-[18px]">expand_more</span>
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            class="absolute right-0 mt-2 w-64 bg-white rounded-[24px] shadow-[0_20px_50px_rgba(0,0,0,0.15)] border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform scale-95 group-hover:scale-100 origin-top-right z-50 overflow-hidden">
                            <div class="p-5 border-b border-slate-50 bg-slate-50/50">
                                <p class="text-sm font-black text-slate-900 leading-none">
                                    <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                                </p>
                                <p class="text-[10px] text-indigo-600 font-black uppercase tracking-widest mt-2">
                                    <?php echo isAdmin() ? 'Super Admin' : 'Premium Member'; ?>
                                </p>
                            </div>
                            <div class="p-2 space-y-1">
                                <a href="profile.php"
                                    class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                                    <span class="material-symbols-outlined text-[20px]">person</span> Account Details
                                </a>
                                <?php if (isAdmin()): ?>
                                    <a href="../admin/dashboard.php"
                                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-indigo-700 bg-indigo-50/50 hover:bg-indigo-50 rounded-xl transition-all">
                                        <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span> Admin Panel
                                    </a>
                                    <div class="h-px bg-slate-50 my-1"></div>
                                <?php endif; ?>

                                <a href="submit.php"
                                    class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                                    <span class="material-symbols-outlined text-[20px]">publish</span> Submit New App
                                </a>
                                <a href="myapps.php"
                                    class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-indigo-700 bg-indigo-50/50 rounded-xl transition-all">
                                    <span class="material-symbols-outlined text-[20px]">cloud_upload</span> My Uploads
                                </a>
                                <a href="favorites.php"
                                    class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                                    <span class="material-symbols-outlined text-[20px]">favorite</span> My Favorites
                                </a>
                                <div class="h-px bg-slate-50 my-1"></div>
                                <a href="../auth/logout.php"
                                    class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 rounded-xl transition-all">
                                    <span class="material-symbols-outlined text-[20px]">logout</span> Sign Out
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto px-4 sm:px-6 lg:px-8 pb-12 main-scroll">
            <div class="max-w-[1500px] mx-auto mt-6 sm:mt-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 leading-tight">My Submitted Apps</h1>
                        <p class="text-[13px] text-gray-500 mt-1">Manage and track the status of applications you have
                            uploaded.</p>
                    </div>
                    <a href="submit.php"
                        class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-5 py-2.5 text-[14px] font-semibold flex items-center justify-center gap-2 transition-all shadow-sm w-full sm:w-auto">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Submit New App
                    </a>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="table-container overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="py-4 px-6 text-[12px] font-bold text-gray-500 uppercase tracking-wider">
                                        App Details</th>
                                    <th class="py-4 px-6 text-[12px] font-bold text-gray-500 uppercase tracking-wider">
                                        Category</th>
                                    <th class="py-4 px-6 text-[12px] font-bold text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th class="py-4 px-6 text-[12px] font-bold text-gray-500 uppercase tracking-wider">
                                        Date Submitted</th>
                                    <th
                                        class="py-4 px-6 text-[12px] font-bold text-gray-500 uppercase tracking-wider text-right">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (count($apps) > 0): ?>
                                    <?php foreach ($apps as $app): ?>
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="py-4 px-6">
                                                <div class="flex items-center gap-4">
                                                    <div
                                                        class="w-12 h-12 rounded-xl bg-white border border-gray-200 shadow-sm flex items-center justify-center flex-shrink-0 overflow-hidden">
                                                        <img src="<?php echo $base_url . htmlspecialchars($app['logo']); ?>" alt="Logo"
                                                            class="w-full h-full object-cover">
                                                    </div>
                                                    <div>
                                                        <div class="font-bold text-[14px] text-gray-900">
                                                            <?php echo htmlspecialchars($app['name']); ?>
                                                        </div>
                                                        <div class="text-[12px] text-gray-500 mt-0.5">
                                                            <?php echo htmlspecialchars($app['developer'] ?? 'Independent Developer'); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-md text-[12px] font-medium bg-gray-100 text-gray-800 capitalize border border-gray-200">
                                                    <?php echo htmlspecialchars($app['category']); ?>
                                                </span>
                                            </td>
                                            <td class="py-4 px-6">
                                                <?php if ($app['status'] === 'approved'): ?>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-1 rounded-md text-[12px] font-bold bg-green-50 text-green-700 border border-green-200 uppercase tracking-wider">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>Approved
                                                    </span>
                                                <?php elseif ($app['status'] === 'pending'): ?>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-1 rounded-md text-[12px] font-bold bg-yellow-50 text-yellow-700 border border-yellow-200 uppercase tracking-wider">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 mr-1.5"></span>Pending
                                                    </span>
                                                <?php else: ?>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-1 rounded-md text-[12px] font-bold bg-red-50 text-red-700 border border-red-200 uppercase tracking-wider">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>Rejected
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-4 px-6 text-[13px] font-medium text-gray-500">
                                                <?php echo date('M d, Y', strtotime($app['created_at'])); ?>
                                            </td>
                                            <td class="py-4 px-6 text-right space-x-2">
                                                <a href="../app.php?id=<?php echo $app['id']; ?>" target="_blank"
                                                    class="inline-block p-1.5 text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors"
                                                    title="Preview App">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>
                                                <a href="edit.php?id=<?php echo $app['id']; ?>"
                                                    class="inline-block p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors"
                                                    title="Edit App">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500">
                                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                                <p class="text-[14px] font-medium">You haven't submitted any apps yet.</p>
                                                <a href="submit.php"
                                                    class="mt-3 text-blue-600 hover:underline font-semibold text-[13px]">Submit
                                                    your first app</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const btn = document.getElementById('mobileMenuBtn');
            const closeBtn = document.getElementById('closeSidebarBtn');
            const sidebar = document.getElementById('mobileSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (btn && sidebar && overlay) {
                const openMenu = () => {
                    sidebar.classList.add('open');
                    overlay.classList.add('open');
                    document.body.style.overflow = 'hidden';
                };
                const closeMenu = () => {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('open');
                    document.body.style.overflow = '';
                };
                btn.addEventListener('click', openMenu);
                if (closeBtn) closeBtn.addEventListener('click', closeMenu);
                overlay.addEventListener('click', closeMenu);
                document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMenu(); });
            }
        });
    </script>
</body>

</html>