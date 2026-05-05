<?php
$current_page = $_SERVER['SCRIPT_NAME'];
$category_param = $_GET['category'] ?? $_GET['slug'] ?? '';

// Icon mapping for categories
$category_icons = [
    'Games'        => 'sports_esports',
    'Productivity' => 'check_circle',
    'Social'       => 'share',
    'Tools'        => 'build',
    'Apps'         => 'android',
    'Windows'      => 'desktop_windows',
    'Notes'        => 'description',
    'Presentations'=> 'present_to_all'
];
?>
<style>
    html, body {
        overflow-x: hidden;
        width: 100%;
        position: relative;
    }

    /* ──── Desktop Sidebar ──── */
    .sidebar {
        background: #ffffff;
        border-right: 1px solid #f1f5f9;
    }

    /* ──── Mobile Drawer ──── */
    .sidebar-mobile {
        position: fixed;
        top: 0; left: 0; bottom: 0;
        width: 290px;
        z-index: 10000;
        transform: translateX(-100%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 24px 0 80px rgba(0,0,0,0.18);
        background: #ffffff;
        will-change: transform;
    }
    .sidebar-mobile.open { transform: translateX(0); }

    .sidebar-overlay {
        position: fixed; inset: 0;
        background: rgba(15, 23, 42, 0.55);
        z-index: 9999; opacity: 0; pointer-events: none;
        transition: opacity 0.35s ease;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }
    .sidebar-overlay.open { opacity: 1; pointer-events: all; }

    /* ──── Smooth Scrollbar ──── */
    .main-scroll { scroll-behavior: smooth; }
    .main-scroll::-webkit-scrollbar { width: 3px; }
    .main-scroll::-webkit-scrollbar-track { background: transparent; }
    .main-scroll::-webkit-scrollbar-thumb { background: #e8edf2; border-radius: 20px; }
    .sidebar:hover .main-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; }

    /* ──── Navigation Items ──── */
    .nav-item {
        color: #64748b;
        transition: background 0.25s ease, color 0.25s ease, transform 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    .nav-item::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, #eef2ff, transparent);
        opacity: 0;
        transition: opacity 0.25s ease;
        border-radius: 12px;
    }
    .nav-item:hover::before { opacity: 1; }
    .nav-item:hover {
        color: #4338ca;
        transform: translateX(3px);
    }
    .nav-item.active {
        background: linear-gradient(90deg, #eef2ff, #f5f3ff);
        color: #4338ca;
        font-weight: 800;
    }
    .nav-item.active .nav-indicator {
        position: absolute;
        left: 0; top: 20%; bottom: 20%;
        width: 3px;
        background: linear-gradient(180deg, #6366f1, #4338ca);
        border-radius: 0 3px 3px 0;
        display: block;
    }
    .nav-item .nav-icon {
        transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.2s;
    }
    .nav-item:hover .nav-icon { transform: scale(1.15); }
    .nav-item.active .nav-icon { color: #4338ca; }

    /* ──── Section Label ──── */
    .nav-section-label {
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: #94a3b8;
        padding: 0 16px;
        margin-top: 28px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .nav-section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, #e2e8f0, transparent);
    }
</style>

<!-- Sidebar Overlay -->
<div id="sidebarOverlay" class="sidebar-overlay md:hidden"></div>

<!-- ═══ Desktop Sidebar ═══ -->
<aside id="mainSidebar" class="sidebar w-[260px] flex-shrink-0 flex-col h-full hidden md:flex z-20">

    <!-- Logo -->
    <div class="px-5 pt-4 pb-3 mb-1 border-b border-slate-50">
        <a href="<?php echo $base_url; ?>" class="flex items-center gap-2 group">
            <div class="w-10 h-10 flex items-center justify-center overflow-hidden rounded-2xl bg-slate-50 shadow-sm">
                <img src="<?php echo $base_url; ?>assets/images/logo.png" class="w-full h-full object-contain mix-blend-multiply" alt="Logo">
            </div>
            <div>
                <h1 class="text-xl font-black leading-none flex items-center">
                    <span class="text-indigo-950">MOD</span><span class="text-orange-500">FIRE</span>
                </h1>
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-0.5">App Marketplace</p>
            </div>
        </a>
    </div>

    <!-- Nav Items -->
    <div class="flex-1 px-3 overflow-y-auto main-scroll pb-6">

        <div class="nav-section-label">Discover</div>
        <nav class="space-y-0.5">
            <a href="<?php echo $base_url; ?>index.php"
                class="nav-item <?php echo (strpos($current_page, 'index.php') !== false && empty($category_param)) ? 'active' : ''; ?> flex items-center gap-3 px-3 py-3 rounded-xl">
                <span class="nav-indicator"></span>
                <span class="material-symbols-outlined nav-icon text-[22px]">explore</span>
                <span class="text-[14.5px] font-bold">All Apps</span>
            </a>
            <?php
            if (!isset($categories_list)) {
                $stmtCat = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
                $categories_list = $stmtCat->fetchAll();
            }
            foreach ($categories_list as $cat):
                $icon = $category_icons[$cat['name']] ?? $cat['icon'] ?? 'folder';
            ?>
                <a href="<?php echo $base_url; ?>category/<?php echo urlencode($cat['slug']); ?>"
                    class="nav-item <?php echo $category_param === $cat['slug'] ? 'active' : ''; ?> flex items-center gap-3 px-3 py-3 rounded-xl">
                    <span class="nav-indicator"></span>
                    <span class="material-symbols-outlined nav-icon text-[22px]"><?php echo htmlspecialchars($icon); ?></span>
                    <span class="text-[14.5px] font-bold"><?php echo htmlspecialchars($cat['name']); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="nav-section-label">Featured</div>
        <nav class="space-y-0.5">
            <a href="<?php echo $base_url; ?>index.php#top" class="nav-item flex items-center gap-3 px-3 py-3 rounded-xl">
                <span class="nav-indicator"></span>
                <span class="material-symbols-outlined nav-icon text-[22px] text-indigo-500">download_for_offline</span>
                <span class="text-[14.5px] font-bold">Top Downloads</span>
                <span class="ml-auto text-[8px] font-black text-indigo-400 bg-indigo-50 px-2 py-0.5 rounded-full uppercase tracking-wide">HOT</span>
            </a>
            <a href="<?php echo $base_url; ?>index.php#popular" class="nav-item flex items-center gap-3 px-3 py-3 rounded-xl">
                <span class="nav-indicator"></span>
                <span class="material-symbols-outlined nav-icon text-[22px] text-orange-500">trending_up</span>
                <span class="text-[14.5px] font-bold">Popular Now</span>
            </a>
            <a href="<?php echo $base_url; ?>index.php#recent" class="nav-item flex items-center gap-3 px-3 py-3 rounded-xl">
                <span class="nav-indicator"></span>
                <span class="material-symbols-outlined nav-icon text-[22px] text-green-500">new_releases</span>
                <span class="text-[14.5px] font-bold">Recently Added</span>
                <span class="ml-auto text-[8px] font-black text-green-400 bg-green-50 px-2 py-0.5 rounded-full uppercase tracking-wide">NEW</span>
            </a>
            <a href="<?php echo $base_url; ?>index.php#windows-sec" class="nav-item flex items-center gap-3 px-3 py-3 rounded-xl">
                <span class="nav-indicator"></span>
                <span class="material-symbols-outlined nav-icon text-[22px] text-blue-500">desktop_windows</span>
                <span class="text-[14.5px] font-bold">Windows PC</span>
            </a>
            <a href="<?php echo $base_url; ?>index.php#android-sec" class="nav-item flex items-center gap-3 px-3 py-3 rounded-xl">
                <span class="nav-indicator"></span>
                <span class="material-symbols-outlined nav-icon text-[22px] text-emerald-500">android</span>
                <span class="text-[14.5px] font-bold">Android APKs</span>
            </a>
        </nav>

        <?php if (!isLoggedIn()): ?>
        <div class="nav-section-label">Account</div>
        <nav class="space-y-0.5">
            <a href="<?php echo $base_url; ?>auth/login.php" class="nav-item flex items-center gap-3 px-3 py-3 rounded-xl">
                <span class="nav-indicator"></span>
                <span class="material-symbols-outlined nav-icon text-[22px]">login</span>
                <span class="text-[14.5px] font-bold">Sign In</span>
            </a>
            <a href="<?php echo $base_url; ?>auth/register.php" class="nav-item flex items-center gap-3 px-3 py-3 rounded-xl">
                <span class="nav-indicator"></span>
                <span class="material-symbols-outlined nav-icon text-[22px]">person_add</span>
                <span class="text-[14.5px] font-bold">Create Account</span>
            </a>
        </nav>
        <?php endif; ?>


    </div>
</aside>

<!-- ═══ Mobile Sidebar ═══ -->
<aside id="mobileSidebar" class="sidebar-mobile md:hidden flex flex-col">
    <!-- Header -->
    <div class="pt-6 pb-4 px-5 flex items-center justify-between border-b border-slate-50">
        <div class="flex items-center gap-2">
            <div class="w-9 h-9 flex items-center justify-center overflow-hidden rounded-xl bg-slate-50">
                <img src="<?php echo $base_url; ?>assets/images/logo.png" class="w-full h-full object-contain mix-blend-multiply" alt="Logo">
            </div>
            <h1 class="text-lg font-black leading-none flex items-center">
                <span class="text-indigo-950">MOD</span><span class="text-orange-500">FIRE</span>
            </h1>
        </div>
        <button id="closeSidebarBtn"
            class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all border border-slate-100">
            <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
    </div>

    <!-- Mobile Nav -->
    <div class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto main-scroll">
        <div class="nav-section-label">Discover</div>
        <a href="<?php echo $base_url; ?>index.php"
            class="nav-item <?php echo (strpos($current_page, 'index.php') !== false && empty($category_param)) ? 'active' : ''; ?> flex items-center gap-4 px-4 py-3.5 rounded-xl">
            <span class="nav-indicator"></span>
            <span class="material-symbols-outlined nav-icon text-[24px]">explore</span>
            <span class="text-[16px] font-bold">All Apps</span>
        </a>
        <?php foreach ($categories_list as $cat):
            $icon = $category_icons[$cat['name']] ?? $cat['icon'] ?? 'folder';
        ?>
            <a href="<?php echo $base_url; ?>category/<?php echo urlencode($cat['slug']); ?>"
                class="nav-item <?php echo $category_param === $cat['slug'] ? 'active' : ''; ?> flex items-center gap-4 px-4 py-3.5 rounded-xl">
                <span class="nav-indicator"></span>
                <span class="material-symbols-outlined nav-icon text-[24px]"><?php echo htmlspecialchars($icon); ?></span>
                <span class="text-[16px] font-bold"><?php echo htmlspecialchars($cat['name']); ?></span>
            </a>
        <?php endforeach; ?>

        <div class="nav-section-label">Featured</div>
        <a href="<?php echo $base_url; ?>index.php#top" class="nav-item flex items-center gap-4 px-4 py-3.5 rounded-xl">
            <span class="nav-indicator"></span>
            <span class="material-symbols-outlined nav-icon text-[24px] text-indigo-500">download_for_offline</span>
            <span class="text-[16px] font-bold">Top Downloads</span>
            <span class="ml-auto text-[8px] font-black text-indigo-400 bg-indigo-50 px-2 py-0.5 rounded-full">HOT</span>
        </a>
        <a href="<?php echo $base_url; ?>index.php#popular" class="nav-item flex items-center gap-4 px-4 py-3.5 rounded-xl">
            <span class="nav-indicator"></span>
            <span class="material-symbols-outlined nav-icon text-[24px] text-orange-500">trending_up</span>
            <span class="text-[16px] font-bold">Popular Now</span>
        </a>
        <a href="<?php echo $base_url; ?>index.php#recent" class="nav-item flex items-center gap-4 px-4 py-3.5 rounded-xl">
            <span class="nav-indicator"></span>
            <span class="material-symbols-outlined nav-icon text-[24px] text-green-500">new_releases</span>
            <span class="text-[16px] font-bold">Recently Added</span>
            <span class="ml-auto text-[8px] font-black text-green-400 bg-green-50 px-2 py-0.5 rounded-full">NEW</span>
        </a>

        <?php if (!isLoggedIn()): ?>
        <div class="nav-section-label">Account</div>
        <a href="<?php echo $base_url; ?>auth/login.php" class="nav-item flex items-center gap-4 px-4 py-3.5 rounded-xl">
            <span class="material-symbols-outlined nav-icon text-[24px]">login</span>
            <span class="text-[16px] font-bold">Sign In</span>
        </a>
        <a href="<?php echo $base_url; ?>auth/register.php" class="nav-item flex items-center gap-4 px-4 py-3.5 rounded-xl">
            <span class="material-symbols-outlined nav-icon text-[24px]">person_add</span>
            <span class="text-[16px] font-bold">Create Account</span>
        </a>
        <?php endif; ?>


    </div>
</aside>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const menuBtn = document.getElementById('mobileMenuBtn');
        const closeBtn = document.getElementById('closeSidebarBtn');
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            sidebar.classList.add('open');
            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
            document.body.style.overflow = '';
        }

        if (menuBtn) menuBtn.addEventListener('click', openSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);

        // Close on Escape key
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeSidebar(); });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href*="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                const hashIdx = href.indexOf('#');
                const hash = href.substring(hashIdx + 1);
                const isSamePage = href.substring(0, hashIdx) === '' || window.location.pathname.endsWith(href.substring(0, hashIdx));
                if (isSamePage && hash) {
                    const el = document.getElementById(hash);
                    if (el) {
                        e.preventDefault();
                        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        closeSidebar();
                    }
                }
            });
        });
    });
</script>