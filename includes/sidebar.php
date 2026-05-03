<?php
$current_page = basename($_SERVER['PHP_SELF']);
$category_param = $_GET['category'] ?? $_GET['slug'] ?? '';
?>
<style>
    html,
    body {
        overflow-x: hidden;
        width: 100%;
        position: relative;
    }

    .sidebar {
        background: #ffffff;
        border-right: 1px solid rgba(241, 245, 249, 1);
    }

    .sidebar-mobile {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        width: 280px;
        z-index: 10000;
        transform: translateX(-100%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 20px 0 60px rgba(0, 0, 0, 0.15);
        background: #ffffff;
        will-change: transform;
    }

    .sidebar-mobile.open {
        transform: translateX(0);
    }

    .sidebar-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.5);
        z-index: 9999;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.4s ease;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }

    .sidebar-overlay.open {
        opacity: 1;
        pointer-events: all;
    }
</style>

<!-- Sidebar Overlay -->
<div id="sidebarOverlay" class="sidebar-overlay md:hidden"></div>

<!-- Sidebar -->
<aside id="mainSidebar" class="sidebar w-[260px] flex-shrink-0 flex-col h-full hidden md:flex z-20">
    <div class="px-6 pt-2 pb-6 mb-4">
        <a href="<?php echo $base_url; ?>" class="flex items-center gap-0 hover:opacity-80 transition-opacity">
            <div class="w-16 h-16 flex items-center justify-center overflow-hidden">
                <img src="<?php echo $base_url; ?>assets/images/logo.png"
                    class="w-full h-full object-contain mix-blend-multiply" alt="Logo">
            </div>
            <div class="-ml-2 mt-2">
                <h1 class="text-3xl font-black leading-tight flex items-center">
                    <span class="text-indigo-950">MOD</span>
                    <span class="text-orange-600">FIRE</span>
                </h1>
            </div>
        </a>
    </div>

    <div class="flex-1 px-4 space-y-1 overflow-y-auto main-scroll">
        <div class="text-[10px] text-slate-400 mb-3 px-3 font-black tracking-[0.15em] uppercase mt-6 opacity-70">Content
            Library</div>
        <nav class="space-y-1.5">
            <a href="<?php echo $base_url; ?>index.php"
                class="nav-item <?php echo ($current_page == 'index.php' && empty($category_param)) ? 'active' : ''; ?> flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                <span class="material-symbols-outlined text-[22px]">explore</span>
                <span class="text-[14px] font-bold tracking-tight">App Discovery</span>
            </a>
            <?php
            if (!isset($categories_list)) {
                $stmtCat = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
                $categories_list = $stmtCat->fetchAll();
            }
            foreach ($categories_list as $cat): ?>
                <a href="<?php echo $base_url; ?>category/<?php echo urlencode($cat['slug']); ?>"
                    class="nav-item <?php echo $category_param === $cat['slug'] ? 'active' : ''; ?> flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                    <span class="material-symbols-outlined text-[22px]"><?php echo htmlspecialchars($cat['icon']); ?></span>
                    <span class="text-[14px] font-bold tracking-tight"><?php echo htmlspecialchars($cat['name']); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php if (!isLoggedIn()): ?>
            <div class="text-[10px] text-slate-400 mb-3 px-3 font-black tracking-[0.15em] uppercase mt-10 opacity-70">
                Join Community</div>
            <nav class="space-y-1.5">
                <a href="<?php echo $base_url; ?>auth/login.php"
                    class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all">
                    <span class="material-symbols-outlined text-[22px]">login</span>
                    <span class="text-[14px] font-bold tracking-tight">Sign In</span>
                </a>
            </nav>
        <?php endif; ?>

    </div>

    <!-- Desktop Support Bottom -->
    <div class="px-4 pb-6 mt-auto">
        <div class="h-px bg-slate-50 mb-6 mx-2"></div>
        <div class="text-[9px] text-slate-400 mb-2 px-3 font-black tracking-[0.15em] uppercase opacity-60">
            Information</div>
        <nav class="space-y-0.5">
            <a href="<?php echo $base_url; ?>about.php"
                class="nav-item <?php echo $current_page == 'about.php' ? 'active' : ''; ?> flex items-center gap-3 px-3 py-2 rounded-lg transition-all">
                <span class="material-symbols-outlined text-[18px]">info</span>
                <span class="text-[12px] font-bold tracking-tight">About Us</span>
            </a>
            <a href="<?php echo $base_url; ?>contact.php"
                class="nav-item <?php echo $current_page == 'contact.php' ? 'active' : ''; ?> flex items-center gap-3 px-3 py-2 rounded-lg transition-all">
                <span class="material-symbols-outlined text-[18px]">contact_support</span>
                <span class="text-[12px] font-bold tracking-tight">Contact Support</span>
            </a>
            <a href="<?php echo $base_url; ?>privacy.php"
                class="nav-item <?php echo $current_page == 'privacy.php' ? 'active' : ''; ?> flex items-center gap-3 px-3 py-2 rounded-lg transition-all">
                <span class="material-symbols-outlined text-[18px]">policy</span>
                <span class="text-[12px] font-bold tracking-tight">Privacy Policy</span>
            </a>
        </nav>
    </div>
</aside>

<!-- Mobile Sidebar -->
<aside id="mobileSidebar" class="sidebar-mobile md:hidden">
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
    <div class="flex-1 px-4 py-8 space-y-8 overflow-y-auto">
        <div>
            <div class="text-[10px] text-slate-400 mb-4 px-4 font-black tracking-[0.15em] uppercase opacity-70">Content
                Library</div>
            <nav class="space-y-1.5">
                <a href="<?php echo $base_url; ?>index.php"
                    class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-slate-600 <?php echo ($current_page == 'index.php' && empty($category_param)) ? 'bg-indigo-50 text-indigo-600 font-bold' : 'font-semibold'; ?>">
                    <span class="material-symbols-outlined">explore</span>
                    <span class="text-[15px]">App Discovery</span>
                </a>
                <?php foreach ($categories_list as $cat): ?>
                    <a href="<?php echo $base_url; ?>category/<?php echo urlencode($cat['slug']); ?>"
                        class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-slate-600 <?php echo $category_param === $cat['slug'] ? 'bg-indigo-50 text-indigo-600 font-bold' : 'font-semibold'; ?>">
                        <span class="material-symbols-outlined"><?php echo htmlspecialchars($cat['icon']); ?></span>
                        <span class="text-[15px]"><?php echo htmlspecialchars($cat['name']); ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <?php if (!isLoggedIn()): ?>
            <div>
                <div class="text-[10px] text-slate-400 mb-4 px-4 font-black tracking-[0.15em] uppercase opacity-70">
                    Join Community</div>
                <nav class="space-y-1.5">
                    <a href="<?php echo $base_url; ?>auth/login.php"
                        class="flex items-center gap-4 px-4 py-3.5 rounded-2xl bg-indigo-900 text-white font-bold shadow-lg shadow-indigo-100">
                        <span class="material-symbols-outlined">login</span>
                        <span class="text-[15px]">Sign In</span>
                    </a>
                </nav>
            </div>
        <?php endif; ?>

        <div>
            <div class="text-[9px] text-slate-400 mb-3 px-4 font-black tracking-[0.15em] uppercase opacity-60">
                Information</div>
            <nav class="space-y-1 pb-10">
                <a href="<?php echo $base_url; ?>about.php"
                    class="flex items-center gap-4 px-4 py-2.5 rounded-xl text-slate-600 <?php echo $current_page == 'about.php' ? 'bg-indigo-50 text-indigo-600 font-bold' : 'font-semibold'; ?>">
                    <span class="material-symbols-outlined text-[20px]">info</span>
                    <span class="text-[13px]">About Us</span>
                </a>
                <a href="<?php echo $base_url; ?>contact.php"
                    class="flex items-center gap-4 px-4 py-2.5 rounded-xl text-slate-600 <?php echo $current_page == 'contact.php' ? 'bg-indigo-50 text-indigo-600 font-bold' : 'font-semibold'; ?>">
                    <span class="material-symbols-outlined text-[20px]">contact_support</span>
                    <span class="text-[13px]">Contact Support</span>
                </a>
                <a href="<?php echo $base_url; ?>privacy.php"
                    class="flex items-center gap-4 px-4 py-2.5 rounded-xl text-slate-600 <?php echo $current_page == 'privacy.php' ? 'bg-indigo-50 text-indigo-600 font-bold' : 'font-semibold'; ?>">
                    <span class="material-symbols-outlined text-[20px]">policy</span>
                    <span class="text-[13px]">Privacy Policy</span>
                </a>
            </nav>
        </div>
    </div>
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
                sidebar.classList.add('open');
                overlay.classList.add('open');
            });
        }
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
            });
        }
        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
            });
        }
    });
</script>