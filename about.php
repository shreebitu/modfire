<?php
require_once 'includes/init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - ShreeBitu</title>
    <meta name="description" content="Learn more about ShreeBitu, our mission, and the team behind the premium cloud app catalog.">
    
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
<body class="flex h-screen overflow-hidden overflow-x-hidden bg-[#f7f9fb] text-[#1d1d1f]">

    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full relative z-10 w-full overflow-hidden">
        <?php 
        $page_title = 'About Us';
        $breadcrumb_html = '
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <a href="index.php" class="hover:text-indigo-600 font-medium transition-colors">Store</a>
                <span class="material-symbols-outlined text-slate-300 text-[18px]">chevron_right</span>
                <span class="font-bold text-slate-900 uppercase tracking-widest text-[11px]">Our Story</span>
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
        <div class="flex-1 overflow-y-auto main-scroll">
            <div class="max-w-4xl mx-auto mt-12 md:mt-16 px-4 md:px-8 lg:px-10 pb-20">
                
                <div class="text-center mb-16">
                    <h1 class="text-4xl md:text-6xl font-black text-slate-900 tracking-tight mb-6">Mission & Vision</h1>
                    <p class="text-slate-500 text-lg md:text-xl leading-relaxed max-w-2xl mx-auto font-medium">SHREEBITU is a community-driven platform dedicated to providing the best software and apps with a focus on safety, speed, and accessibility.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-20">
                    <!-- Vision -->
                    <div class="bg-white p-8 md:p-10 rounded-[40px] border border-slate-100 shadow-sm">
                        <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-3xl flex items-center justify-center mb-8">
                            <span class="material-symbols-outlined text-[32px]">rocket_launch</span>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 mb-4">Our Vision</h3>
                        <p class="text-slate-500 leading-relaxed">To become the world's most trusted independent app catalog, empowering developers to reach their audience without barriers and users to download with complete peace of mind.</p>
                    </div>

                    <!-- Values -->
                    <div class="bg-white p-8 md:p-10 rounded-[40px] border border-slate-100 shadow-sm">
                        <div class="w-16 h-16 bg-green-50 text-green-600 rounded-3xl flex items-center justify-center mb-8">
                            <span class="material-symbols-outlined text-[32px]">security</span>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 mb-4">Core Values</h3>
                        <p class="text-slate-500 leading-relaxed">Safety first, transparency always, and performance without compromise. We believe in a web where software is accessible to everyone, safely and quickly.</p>
                    </div>
                </div>

                <!-- Founder Banner -->
                <div class="bg-indigo-900 rounded-[48px] p-10 md:p-16 text-center relative overflow-hidden shadow-2xl shadow-indigo-100">
                    <div class="relative z-10">
                        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">Built by Bitu Talukdar</h2>
                        <p class="text-indigo-100 text-lg mb-10 opacity-80 max-w-xl mx-auto">SHREEBITU was founded with the goal of creating a clean, ad-free, and straightforward way to access essential software.</p>
                        <div class="flex justify-center gap-4">
                            <a href="index.php" class="bg-white text-indigo-900 px-8 py-4 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-indigo-50 transition-all shadow-xl active:scale-95">Explore Marketplace</a>
                        </div>
                    </div>
                    <!-- Decorative elements -->
                    <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
                </div>
            </div>
        <?php include 'includes/footer.php'; ?>
        </div>
    </main>

</body>
</html>