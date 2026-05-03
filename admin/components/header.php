<?php
// Admin Top Header Component
?>
<header
    class="h-16 flex items-center justify-between px-6 lg:px-8 shrink-0 z-30 sticky top-0 bg-white/80 backdrop-blur-xl border-b border-slate-200/50">
    <div class="flex items-center gap-1 md:gap-4 flex-1">
        <button id="mobileMenuBtn"
            class="md:hidden text-slate-600 hover:text-indigo-700 focus:outline-none p-1 rounded-xl transition-all flex items-center justify-center">
            <span class="material-symbols-outlined text-[34px]">menu</span>
        </button>
        <div class="flex md:hidden items-center">
            <h1 class="text-2xl font-black leading-none flex items-center">
                <span class="text-indigo-950">MOD</span>
                <span class="text-orange-600">FIRE</span>
                <span
                    class="ml-2 text-[10px] bg-indigo-900 text-white px-2 py-0.5 rounded-full uppercase tracking-widest">Admin</span>
            </h1>
        </div>
        <div class="hidden md:flex items-center gap-2 text-sm text-slate-500">
            <span class="material-symbols-outlined text-[18px]">security</span>
            <span class="font-bold text-slate-900 uppercase tracking-widest text-[11px]">Secure Admin Command
                Center</span>
        </div>
    </div>

    <div class="flex items-center gap-4">
        <div class="relative group">
            <button
                class="flex items-center gap-3 p-1 pr-4 bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-indigo-200 transition-all group">
                <div
                    class="w-8 h-8 rounded-xl bg-indigo-900 text-white flex items-center justify-center text-[12px] font-bold shadow-indigo-100 shadow-lg overflow-hidden shrink-0">
                    <?php if (!empty($_SESSION['profile_pic'])): ?>
                        <img src="../<?php echo htmlspecialchars($_SESSION['profile_pic']); ?>"
                            class="w-full h-full object-cover">
                    <?php else: ?>
                        <?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="text-left hidden sm:block">
                    <p
                        class="text-[11px] font-bold text-slate-700 group-hover:text-indigo-600 transition-colors leading-none">
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                    </p>
                    <p class="text-[9px] text-indigo-600 font-bold uppercase tracking-tighter mt-1">Super Admin</p>
                </div>
                <span class="material-symbols-outlined text-slate-300 text-[18px]">expand_more</span>
            </button>

            <!-- Dropdown Menu -->
            <div
                class="absolute right-0 mt-2 w-64 bg-white rounded-[24px] shadow-[0_20px_50px_rgba(0,0,0,0.15)] border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform scale-95 group-hover:scale-100 origin-top-right z-50 overflow-hidden">
                <div class="p-5 border-b border-slate-50 bg-slate-50/50">
                    <p class="text-sm font-black text-slate-900 leading-none">
                        <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    <p class="text-[10px] text-indigo-600 font-black uppercase tracking-widest mt-2">Platform Root</p>
                </div>
                <div class="p-2 space-y-1">
                    <a href="dashboard.php"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-indigo-700 bg-indigo-50/50 hover:bg-indigo-50 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-[20px]">dashboard</span> Dashboard
                    </a>
                    <div class="h-px bg-slate-50 my-1"></div>
                    <a href="users.php"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-[20px]">group</span> User Management
                    </a>
                    <a href="apps.php"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-[20px]">inventory_2</span> App Moderation
                    </a>
                    <a href="reports.php"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-[20px]">flag</span> Reports
                    </a>
                    <a href="settings.php"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-[20px]">settings</span> System Settings
                    </a>
                    <div class="h-px bg-slate-50 my-1"></div>
                    <a href="../index.php"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-[20px]">explore</span> Visit Marketplace
                    </a>
                    <a href="../user/profile.php"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-[20px]">person</span> Account Details
                    </a>
                    <div class="h-px bg-slate-50 my-1"></div>
                    <a href="../auth/logout.php"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-[20px]">logout</span> Sign Out
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>