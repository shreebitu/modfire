<?php
require_once 'config.php';
require_once 'db.php';

$category = ''; // For sidebar highlighting

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found - ShreeBitu</title>
    <!-- Standard SEO -->
    <meta name="description" content="Page not found on ShreeBitu.">
    <base href="<?php echo $base_url; ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?php echo isset($assets_url) ? $assets_url : ''; ?>images/logo.png">
    
    <style>
        .main-scroll::-webkit-scrollbar { width: 5px; }
        .main-scroll::-webkit-scrollbar-track { background: transparent; }
        .main-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .hero-404 {
            background-color: #0b1120;
            background-image: 
                radial-gradient(ellipse at top right, rgba(14, 165, 233, 0.2), transparent 60%),
                radial-gradient(ellipse at bottom left, rgba(59, 130, 246, 0.2), transparent 60%);
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-[#1d1d1f]">

    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Area -->
    <main class="flex-1 flex flex-col h-full bg-[#f7f9fb] relative z-10 w-full overflow-hidden">
        <?php 
        $page_title = 'Page Not Found';
        $breadcrumb_html = '
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <a href="index.php" class="hover:text-indigo-600 font-medium transition-colors">Store</a>
                <span class="material-symbols-outlined text-slate-300">chevron_right</span>
                <span class="font-bold text-slate-900 uppercase tracking-widest text-[11px]">Error 404</span>
            </div>';
        include 'includes/header.php'; 
        ?>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto px-4 md:px-8 lg:px-10 py-10 main-scroll flex items-center justify-center">
        
        <div class="hero-404 w-full max-w-4xl rounded-[24px] overflow-hidden flex flex-col items-center justify-center p-12 sm:p-20 relative text-center shadow-xl">
            <div class="relative z-10">
                <div class="text-[100px] sm:text-[140px] font-bold text-white leading-none tracking-tighter mb-2 opacity-90 drop-shadow-lg">404</div>
                <div class="bg-[#0066cc] text-white text-[12px] font-bold uppercase tracking-widest px-4 py-1.5 rounded-full mb-6 inline-block shadow-md">Page Not Found</div>
                
                <h1 class="text-[24px] sm:text-[32px] font-bold text-white mb-4 tracking-tight">Oops! Looks like you're lost.</h1>
                <p class="text-gray-300 text-sm md:text-[16px] leading-relaxed mb-10 max-w-lg mx-auto font-light">
                    The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
                </p>
                
                <a href="index.php" class="bg-white hover:bg-gray-50 text-gray-900 rounded-lg px-8 py-3.5 text-[15px] font-semibold inline-flex items-center gap-2 transition-all shadow-lg hover:shadow-xl group">
                    <svg class="w-5 h-5 text-gray-700 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Return to Home
                </a>
            </div>
            
            <!-- Decorative Elements -->
            <div class="absolute top-10 left-10 w-24 h-24 bg-blue-500 rounded-full mix-blend-multiply filter blur-2xl opacity-20 animate-pulse"></div>
            <div class="absolute bottom-10 right-10 w-32 h-32 bg-sky-500 rounded-full mix-blend-multiply filter blur-2xl opacity-20 animate-pulse" style="animation-delay: 2s;"></div>
        </div>
        
    </div>

</main>

</body>
</html>
