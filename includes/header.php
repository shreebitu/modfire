<?php
$breadcrumb_html = $breadcrumb_html ?? '';
?>
<header class="h-16 header-blur flex items-center justify-between px-6 lg:px-8 shrink-0 z-30 sticky top-0 bg-white">
    <div class="flex items-center gap-1 md:gap-4 flex-1">
        <button id="mobileMenuBtn"
            class="md:hidden text-slate-900 focus:outline-none transition-colors">
            <span class="material-symbols-outlined text-[32px]">menu</span>
        </button>
        <div class="hidden md:flex items-center gap-2 text-sm text-slate-500">
            <?php if ($breadcrumb_html): ?>
                <?php echo $breadcrumb_html; ?>
            <?php else: ?>
                <span
                    class="font-bold text-slate-900 uppercase tracking-widest text-[11px]"><?php echo $page_title ?? 'Marketplace'; ?></span>
            <?php endif; ?>
        </div>

        <div class="hidden md:flex flex-1 max-w-lg mx-4">
            <?php echo $header_custom_content ?? ''; ?>
        </div>

        <div class="flex md:hidden items-center">
            <h1 class="text-xl sm:text-2xl font-black leading-none flex items-center">
                <span class="text-indigo-950">MOD</span>
                <span class="text-orange-600">FIRE</span>
            </h1>
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
                            <?php
                            $pic = $_SESSION['profile_pic'];
                            $pic_url = (strpos($pic, 'http') === 0) ? $pic : $base_url . $pic;
                            ?>
                            <img src="<?php echo htmlspecialchars($pic_url); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div class="text-left hidden sm:block">
                        <p
                            class="text-[11px] font-bold text-slate-700 group-hover:text-indigo-600 transition-colors leading-none">
                            <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                        </p>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter mt-1">
                            <?php echo isAdmin() ? 'Super Admin' : 'Member'; ?></p>
                    </div>
                    <span class="material-symbols-outlined text-slate-300 text-[18px]">expand_more</span>
                </button>

                <!-- Dropdown Menu -->
                <div
                    class="absolute right-0 mt-2 w-64 bg-white rounded-[24px] shadow-[0_20px_50px_rgba(0,0,0,0.15)] border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform scale-95 group-hover:scale-100 origin-top-right z-50 overflow-hidden">
                    <div class="p-5 border-b border-slate-50 bg-slate-50/50">
                        <p class="text-sm font-black text-slate-900 leading-none">
                            <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></p>
                        <p class="text-[10px] text-indigo-600 font-black uppercase tracking-widest mt-2">
                            <?php echo isAdmin() ? 'Super Admin' : 'Premium Member'; ?></p>
                    </div>
                    <div class="p-2 space-y-1">
                        <?php if (isAdmin()): ?>
                            <a href="<?php echo $base_url; ?>admin/dashboard.php"
                                class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-indigo-700 bg-indigo-50/50 hover:bg-indigo-50 rounded-xl transition-all">
                                <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span> Admin Panel
                            </a>
                            <div class="h-px bg-slate-50 my-1"></div>
                        <?php endif; ?>
                        <a href="<?php echo $base_url; ?>user/profile.php"
                            class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                            <span class="material-symbols-outlined text-[20px]">person</span> Account Details
                        </a>
                        <a href="<?php echo $base_url; ?>user/submit.php"
                            class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                            <span class="material-symbols-outlined text-[20px]">publish</span> Submit New App
                        </a>
                        <a href="<?php echo $base_url; ?>user/myapps.php"
                            class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                            <span class="material-symbols-outlined text-[20px]">cloud_upload</span> My Uploads
                        </a>
                        <a href="<?php echo $base_url; ?>user/favorites.php"
                            class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                            <span class="material-symbols-outlined text-[20px]">favorite</span> My Favorites
                        </a>
                        <div class="h-px bg-slate-50 my-1"></div>
                        <a href="<?php echo $base_url; ?>auth/logout.php"
                            class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 rounded-xl transition-all">
                            <span class="material-symbols-outlined text-[20px]">logout</span> Sign Out
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <a href="<?php echo $base_url; ?>auth/login.php"
                class="text-[12px] sm:text-sm font-bold text-slate-600 hover:text-indigo-600 px-2 sm:px-3 py-2 transition-colors">Login</a>
            <a href="<?php echo $base_url; ?>auth/register.php"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 sm:px-5 py-2 rounded-xl text-[12px] sm:text-sm font-bold shadow-lg shadow-indigo-200 transition-all active:scale-95 whitespace-nowrap">Sign
                Up</a>
        <?php endif; ?>
    </div>
</header>