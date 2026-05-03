<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar w-[260px] flex-shrink-0 flex flex-col h-full hidden md:flex z-20">
    <div class="px-6 py-8 mb-4">
        <a href="<?php echo $base_url; ?>" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
            <div class="w-9 h-9 bg-indigo-900 rounded-xl flex items-center justify-center shadow-lg">
                <span class="material-symbols-outlined text-white text-lg">shield_person</span>
            </div>
            <div>
                <h1 class="text-lg font-black text-indigo-950 leading-tight">SUPER ADMIN</h1>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Platform Root</p>
            </div>
        </a>
    </div>
    <div class="flex-1 px-3 space-y-1 overflow-y-auto main-scroll">
        <nav class="space-y-1">
            <a href="dashboard.php" class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?> flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all">
                <span class="material-symbols-outlined">dashboard</span> Dashboard
            </a>
            <a href="apps.php" class="nav-item <?php echo $current_page == 'apps.php' ? 'active' : ''; ?> flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all">
                <span class="material-symbols-outlined">inventory_2</span> Manage Content
            </a>
            <a href="users.php" class="nav-item <?php echo $current_page == 'users.php' ? 'active' : ''; ?> flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all">
                <span class="material-symbols-outlined">group</span> User Control
            </a>
            <a href="reviews.php" class="nav-item <?php echo $current_page == 'reviews.php' ? 'active' : ''; ?> flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all">
                <span class="material-symbols-outlined">rate_review</span> Community Reviews
            </a>
            <a href="reports.php" class="nav-item <?php echo $current_page == 'reports.php' ? 'active' : ''; ?> flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all">
                <span class="material-symbols-outlined">flag</span> Reports
                <?php 
                $pendingReports = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();
                if($pendingReports > 0): ?>
                    <span class="ml-auto bg-red-100 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded-full"><?php echo $pendingReports; ?></span>
                <?php endif; ?>
            </a>
        </nav>
        <div class="h-px bg-slate-200 my-4 mx-3"></div>
        <nav class="space-y-1">
            <a href="categories.php" class="nav-item <?php echo $current_page == 'categories.php' ? 'active' : ''; ?> flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all">
                <span class="material-symbols-outlined">category</span> Categories
            </a>
            <a href="settings.php" class="nav-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?> flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all">
                <span class="material-symbols-outlined">settings</span> Global Settings
            </a>
            <a href="security.php" class="nav-item <?php echo $current_page == 'security.php' ? 'active' : ''; ?> flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all">
                <span class="material-symbols-outlined">security</span> IP & Security
            </a>
            <a href="logs.php" class="nav-item <?php echo $current_page == 'logs.php' ? 'active' : ''; ?> flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all">
                <span class="material-symbols-outlined">history</span> Action Logs
            </a>
            <a href="backup.php" class="nav-item <?php echo $current_page == 'backup.php' ? 'active' : ''; ?> flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all">
                <span class="material-symbols-outlined">database</span> Backups
            </a>
        </nav>
        <div class="mt-auto pb-6">
            <a href="../auth/logout.php" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-600 hover:bg-red-50">
                <span class="material-symbols-outlined">logout</span> Exit Panel
            </a>
        </div>
    </div>
</aside>
