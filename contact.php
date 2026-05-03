<?php
require_once 'config.php';
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Support - ShreeBitu</title>
    <meta name="description" content="Get in touch with ShreeBitu support team for technical assistance or business inquiries.">
    
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
        $page_title = 'Contact Support';
        $breadcrumb_html = '
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <a href="index.php" class="hover:text-indigo-600 font-medium transition-colors">Store</a>
                <span class="material-symbols-outlined text-slate-300 text-[18px]">chevron_right</span>
                <span class="font-bold text-slate-900 uppercase tracking-widest text-[11px]">Support Center</span>
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
                
                <div class="text-center mb-16">
                    <h1 class="text-4xl md:text-6xl font-black text-slate-900 tracking-tight mb-6">Get in Touch</h1>
                    <p class="text-slate-500 text-lg md:text-xl leading-relaxed max-w-2xl mx-auto font-medium">Have questions or need technical help? Our team is available 24/7 to assist you.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
                    <!-- Tech Support -->
                    <div class="bg-white p-8 md:p-10 rounded-[40px] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-3xl flex items-center justify-center mb-8 group-hover:rotate-12 transition-transform">
                            <span class="material-symbols-outlined text-[32px]">support_agent</span>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 mb-4">Technical Support</h3>
                        <p class="text-slate-500 leading-relaxed mb-8">Facing issues with an app installation or performance? Our experts are here to troubleshoot.</p>
                        <a href="mailto:support@shreebitu.in" class="inline-flex items-center gap-3 text-indigo-600 font-black text-sm uppercase tracking-widest hover:gap-5 transition-all">
                            support@shreebitu.in
                            <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                        </a>
                    </div>

                    <!-- General Inquiries -->
                    <div class="bg-white p-8 md:p-10 rounded-[40px] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-3xl flex items-center justify-center mb-8 group-hover:rotate-12 transition-transform">
                            <span class="material-symbols-outlined text-[32px]">handshake</span>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 mb-4">General Inquiries</h3>
                        <p class="text-slate-500 leading-relaxed mb-8">For business partnerships, advertising opportunities, or general platform questions.</p>
                        <a href="mailto:info@shreebitu.in" class="inline-flex items-center gap-3 text-indigo-600 font-black text-sm uppercase tracking-widest hover:gap-5 transition-all">
                            info@shreebitu.in
                            <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                        </a>
                    </div>
                </div>

                <!-- Footer Banner -->
                <div class="bg-slate-900 rounded-[48px] p-10 md:p-16 text-center relative overflow-hidden shadow-2xl shadow-indigo-100">
                    <div class="relative z-10">
                        <h4 class="text-2xl md:text-3xl font-bold text-white mb-6">Stay Connected</h4>
                        <p class="text-slate-400 mb-10 max-w-md mx-auto">Follow our official channels for the latest app updates and platform news.</p>
                        <div class="flex justify-center gap-4 md:gap-6">
                            <a href="#" class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center hover:bg-blue-600 transition-all border border-white/5 hover:scale-110">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                            </a>
                            <a href="#" class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center hover:bg-pink-600 transition-all border border-white/5 hover:scale-110">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                        </div>
                    </div>
                    <!-- Background Decorative Element -->
                    <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-indigo-500/10 rounded-full blur-[100px]"></div>
                    <div class="absolute -left-20 -top-20 w-80 h-80 bg-blue-500/10 rounded-full blur-[100px]"></div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>