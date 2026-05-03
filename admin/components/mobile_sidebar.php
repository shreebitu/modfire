<?php
// Admin Mobile Sidebar Component
?>
<div id="sidebarOverlay"
    class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 opacity-0 invisible transition-all duration-300 md:hidden">
</div>

<aside id="mobileSidebar"
    class="fixed left-0 top-0 bottom-0 w-[280px] bg-white z-50 transform -translate-x-full transition-all duration-300 ease-out md:hidden flex flex-col shadow-2xl">
    <div class="pt-6 pb-6 px-6 flex items-center justify-between border-b border-slate-50">
        <div class="flex items-center">
            <h1 class="text-2xl font-black leading-none flex items-center">
                <span class="text-indigo-950">MOD</span>
                <span class="text-orange-600">FIRE</span>
            </h1>
        </div>
        <button id="closeSidebarBtn"
            class="w-10 h-10 flex items-center justify-center rounded-xl bg-white text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all shadow-sm border border-slate-100">
            <span class="material-symbols-outlined text-[22px]">close</span>
        </button>
    </div>

    <div class="flex-1 px-4 py-6 space-y-6 overflow-y-auto main-scroll">
        <div>
            <div class="text-[10px] text-slate-400 mb-4 px-4 font-black tracking-[0.15em] uppercase opacity-70">Main
                Controls</div>
            <nav class="space-y-1.5">
                <a href="dashboard.php"
                    class="flex items-center gap-4 px-4 py-3.5 rounded-2xl <?php echo $current_page == 'dashboard.php' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 font-semibold'; ?>">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="text-[15px]">Dashboard</span>
                </a>
                <a href="apps.php"
                    class="flex items-center gap-4 px-4 py-3.5 rounded-2xl <?php echo $current_page == 'apps.php' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 font-semibold'; ?>">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <span class="text-[15px]">Manage Content</span>
                </a>
                <a href="users.php"
                    class="flex items-center gap-4 px-4 py-3.5 rounded-2xl <?php echo $current_page == 'users.php' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 font-semibold'; ?>">
                    <span class="material-symbols-outlined">group</span>
                    <span class="text-[15px]">User Control</span>
                </a>
                <a href="reviews.php"
                    class="flex items-center gap-4 px-4 py-3.5 rounded-2xl <?php echo $current_page == 'reviews.php' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 font-semibold'; ?>">
                    <span class="material-symbols-outlined">rate_review</span>
                    <span class="text-[15px]">Community Reviews</span>
                </a>
            </nav>
        </div>

        <div>
            <div class="text-[10px] text-slate-400 mb-4 px-4 font-black tracking-[0.15em] uppercase opacity-70">Settings
                & Logs</div>
            <nav class="space-y-1.5">
                <a href="settings.php"
                    class="flex items-center gap-4 px-4 py-3.5 rounded-2xl <?php echo $current_page == 'settings.php' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 font-semibold'; ?>">
                    <span class="material-symbols-outlined">settings</span>
                    <span class="text-[15px]">Global Settings</span>
                </a>
                <a href="logs.php"
                    class="flex items-center gap-4 px-4 py-3.5 rounded-2xl <?php echo $current_page == 'logs.php' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 font-semibold'; ?>">
                    <span class="material-symbols-outlined">history</span>
                    <span class="text-[15px]">Action Logs</span>
                </a>
            </nav>
        </div>
    </div>

    <div class="p-4 border-t border-slate-50">
        <a href="../auth/logout.php"
            class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-red-600 font-bold bg-red-50/50">
            <span class="material-symbols-outlined">logout</span>
            <span class="text-[15px]">Exit Panel</span>
        </a>
    </div>
</aside>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const menuBtn = document.getElementById('mobileMenuBtn');
        const closeBtn = document.getElementById('closeSidebarBtn');
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if (menuBtn) {
            menuBtn.addEventListener('click', () => {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('opacity-0', 'invisible');
            });
        }
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0', 'invisible');
            });
        }
        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0', 'invisible');
            });
        }
    });
</script>

<style>
    .sidebar-mobile.open {
        transform: translateX(0);
    }

    .sidebar-overlay.open {
        opacity: 1;
        visibility: visible;
    }
</style>