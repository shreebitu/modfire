<?php
require_once 'config.php';
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - ShreeBitu</title>
    <meta name="description" content="Learn how ShreeBitu protects your privacy and handles your data with the highest security standards.">
    
    <!-- External Assets -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?php echo $assets_url; ?>images/logo.png">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .main-scroll::-webkit-scrollbar { width: 5px; }
        .main-scroll::-webkit-scrollbar-track { background: transparent; }
        .main-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="flex h-screen overflow-hidden bg-[#f7f9fb] text-[#1d1d1f]">

    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full relative z-10 w-full overflow-hidden">
        <?php 
        $page_title = 'Privacy Policy';
        $breadcrumb_html = '
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <a href="index.php" class="hover:text-indigo-600 font-medium transition-colors">Store</a>
                <span class="material-symbols-outlined text-slate-300 text-[18px]">chevron_right</span>
                <span class="font-bold text-slate-900 uppercase tracking-widest text-[11px]">Privacy & Legal</span>
            </div>';

        ob_start(); ?>
        <form action="index.php" method="GET" class="relative group w-full max-w-md">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-slate-400 text-[20px] group-focus-within:text-indigo-600 transition-colors">search</span>
            </div>
            <input type="text" name="search" placeholder="Search for applications..." 
                class="w-full bg-slate-100 border-none rounded-2xl py-2.5 pl-12 pr-4 text-sm font-medium focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none shadow-sm">
        </form>
        <?php 
        $header_custom_content = ob_get_clean();
        include 'includes/header.php'; 
        ?>

        <!-- Content Container -->
        <div class="flex-1 overflow-y-auto px-4 md:px-8 lg:px-10 pb-20 main-scroll">
            <div class="max-w-4xl mx-auto mt-12 md:mt-16">
                
                <div class="mb-12">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-50 rounded-full text-[10px] font-black text-indigo-600 tracking-widest uppercase mb-6">
                        <span class="material-symbols-outlined text-[16px]">verified_user</span>
                        Secure Platform
                    </div>
                    <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-4">Privacy Policy</h1>
                    <p class="text-slate-500 text-lg font-medium">Last updated: May 2026. Your trust is our most valuable asset.</p>
                </div>

                <div class="bg-white rounded-[48px] border border-slate-100 shadow-sm p-8 md:p-16 space-y-12">
                    <section>
                        <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center gap-4">
                            <span class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center font-black">01</span>
                            Data Transparency
                        </h2>
                        <p class="text-slate-500 leading-relaxed text-lg">At ShreeBitu, accessible from shreebitu.in, we believe in complete transparency. This policy outlines exactly what data we collect and how it helps us provide a safer, faster app marketplace for you.</p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center gap-4">
                            <span class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center font-black">02</span>
                            Secure Logging
                        </h2>
                        <p class="text-slate-500 leading-relaxed text-lg">We use standard log files to maintain platform security. This includes IP addresses, browser types, and access times, which are strictly used for preventing abuse and improving system performance.</p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center gap-4">
                            <span class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center font-black">03</span>
                            Cookie Policy
                        </h2>
                        <p class="text-slate-500 leading-relaxed text-lg">Cookies help us remember your preferences (like dark mode or language settings). We do not use cookies to track you across other websites or to sell your personal information.</p>
                    </section>

                    <div class="pt-10 border-t border-slate-50">
                        <div class="bg-slate-50 rounded-[32px] p-8 md:p-10 border border-slate-100">
                            <h3 class="text-xl font-bold text-slate-900 mb-4">Your Consent</h3>
                            <p class="text-slate-500 leading-relaxed mb-0">By continuing to use our platform, you agree to our processing of information as outlined in this policy. If you have any concerns regarding your data, please contact our privacy officer at <a href="mailto:privacy@shreebitu.in" class="text-indigo-600 font-bold hover:underline">privacy@shreebitu.in</a>.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-12 text-center text-slate-400 text-sm font-medium">
                    &copy; 2026 ShreeBitu Platform. All rights reserved.
                </div>
            </div>
        </div>
    </main>

</body>
</html>