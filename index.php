<?php
require_once 'includes/init.php';

// Fetch categories for sidebar/filters
$stmtCat = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories_list = $stmtCat->fetchAll();

$category_slug = sanitizeInput($_GET['category'] ?? '');
$search = sanitizeInput($_GET['search'] ?? '');
$page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? (int) $_GET['page'] : 1;
$limit = 24;
$offset = ($page - 1) * $limit;

$params = [];
$where = ["apps.status = 'approved'"];

if (isLoggedIn()) {
    $current_user_id = $_SESSION['user_id'];
    $where[] = "(users.shadow_banned = 0 OR apps.user_id = ?)";
    $params[] = $current_user_id;
} else {
    $where[] = "users.shadow_banned = 0";
}

if ($category_slug) {
    $where[] = "categories.slug = ?";
    $params[] = $category_slug;
}
if ($search) {
    $where[] = "apps.name LIKE ?";
    $params[] = "%$search%";
}

$where_clause = implode(" AND ", $where);

$sqlBase = "FROM apps 
            JOIN users ON apps.user_id = users.id
            LEFT JOIN categories ON apps.category = categories.name
            WHERE $where_clause";

$queryFields = "apps.*, users.username as uploader_name, categories.name as category_name,
                (SELECT ROUND(AVG(rating), 1) FROM reviews WHERE app_id = apps.id) as avg_rating,
                (SELECT COUNT(*) FROM downloads_log WHERE app_id = apps.id) as downloads";

if (isLoggedIn()) {
    $queryFields .= ", (SELECT COUNT(*) FROM favorites WHERE user_id = ? AND app_id = apps.id) as is_favorited";
    $finalParams = array_merge([$current_user_id], $params);
} else {
    $queryFields .= ", 0 as is_favorited";
    $finalParams = $params;
}

$stmt = $pdo->prepare("SELECT $queryFields $sqlBase ORDER BY apps.created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($finalParams);
$apps = $stmt->fetchAll();

$stmtCount = $pdo->prepare("SELECT COUNT(*) $sqlBase");
$stmtCount->execute($params);
$total_apps = $stmtCount->fetchColumn();
$total_pages = ceil($total_apps / $limit);

// Fetch Featured Sections (Only on first page without filters)
$featured_apps = ['top' => [], 'popular' => [], 'recent' => []];
if (empty($category_slug) && empty($search) && $page == 1) {
    // Auto-fix: Check if columns exist, if not, create them
    try {
        $pdo->query("SELECT is_top FROM apps LIMIT 1");
    } catch (PDOException $e) {
        try {
            $pdo->exec("ALTER TABLE apps ADD COLUMN is_top TINYINT(1) DEFAULT 0, ADD COLUMN is_popular TINYINT(1) DEFAULT 0, ADD COLUMN is_recent TINYINT(1) DEFAULT 0");
        } catch (PDOException $e2) {
            // Fail silently or log
        }
    }

    // Build base fields for shared queries
    $fFields = "apps.*, users.username as uploader_name, categories.name as category_name,
                (SELECT ROUND(AVG(rating), 1) FROM reviews WHERE app_id = apps.id) as avg_rating,
                (SELECT COUNT(*) FROM downloads_log WHERE app_id = apps.id) as downloads";
    if (isLoggedIn()) { $fFields .= ", (SELECT COUNT(*) FROM favorites WHERE user_id = ? AND app_id = apps.id) as is_favorited"; }
    else { $fFields .= ", 0 as is_favorited"; }

    // 1. Featured Flags
    foreach (['is_top' => 'top', 'is_popular' => 'popular', 'is_recent' => 'recent'] as $col => $key) {
        $stmtF = $pdo->prepare("SELECT $fFields FROM apps 
                                JOIN users ON apps.user_id = users.id
                                LEFT JOIN categories ON apps.category = categories.name
                                WHERE apps.status = 'approved' AND apps.$col = 1 
                                ORDER BY apps.created_at DESC LIMIT 6");
        if (isLoggedIn()) { $stmtF->execute([$current_user_id]); }
        else { $stmtF->execute(); }
        $featured_apps[$key] = $stmtF->fetchAll();
    }

    // 2. Category Based Sections
    $cat_sections = ['windows' => [], 'apk' => []];
    foreach (['windows', 'apk'] as $slug) {
        $stmtC = $pdo->prepare("SELECT $fFields FROM apps 
                                JOIN users ON apps.user_id = users.id
                                LEFT JOIN categories ON apps.category = categories.name
                                WHERE apps.status = 'approved' AND categories.slug = ? 
                                ORDER BY apps.created_at DESC LIMIT 6");
        $paramsC = isLoggedIn() ? [$current_user_id, $slug] : [$slug];
        $stmtC->execute($paramsC);
        $cat_sections[$slug] = $stmtC->fetchAll();
    }
}

// Dynamic SEO Helpers
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$canonical_url = $protocol . "://" . $host . $base_url . ($category_slug ? 'category/' . $category_slug : '');
$og_image = $protocol . "://" . $host . $assets_url . 'images/logo.png';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?php echo $base_url; ?>">
    <title>MODFIRE - Premium Cloud App Catalog</title>
    <meta name="description" content="Discover and download premium software and apps for Windows and Android.">
    <link rel="canonical" href="<?php echo $canonical_url; ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $canonical_url; ?>">
    <meta property="og:title" content="MODFIRE - Premium Cloud App Catalog">
    <meta property="og:description" content="Discover and download premium software and apps for Windows and Android.">
    <meta property="og:image" content="<?php echo $og_image; ?>">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="<?php echo $assets_url; ?>images/logo.png">

    <style>
        :root {
            --primary: #1f108e;
            --primary-light: #eef2ff;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-900: #0f172a;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fb;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }

        .sidebar {
            background: var(--slate-50);
            border-right: 1px solid var(--slate-200);
            transition: all 0.3s ease;
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

        .nav-item {
            transition: all 0.2s;
            font-weight: 500;
            font-size: 14px;
            color: var(--slate-600);
        }

        .nav-item:hover {
            background-color: var(--slate-100);
            color: var(--primary);
        }

        .nav-item.active {
            background-color: #ffffff;
            color: var(--primary);
            border-right: 3px solid var(--primary);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .header-blur {
            background: #ffffff !important;
            border-bottom: 1px solid var(--slate-200);
        }

        .material-symbols-outlined {
            font-size: 20px;
        }

        .search-input:focus {
            background: white;
            box-shadow: 0 4px 20px -5px rgba(31, 16, 142, 0.1);
        }

        .hero-gradient {
            background: linear-gradient(135deg, #1f108e 0%, #312e81 100%);
        }

        .app-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--slate-200);
        }

        .app-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
            border-color: var(--primary);
        }

        .main-scroll::-webkit-scrollbar {
            width: 5px;
        }

        .main-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .main-scroll::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
    </style>
</head>

<body class="flex h-screen overflow-hidden text-[#1d1d1f]">

    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Area -->
    <main class="flex-1 flex flex-col h-full bg-[#f7f9fb] relative z-10 w-full overflow-hidden">
        <!-- Header -->
        <?php
        $page_title = 'Marketplace';
        $breadcrumb_html = '';

        ob_start(); ?>
        <form action="index.php" method="GET" class="relative group w-full">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search premium applications..."
                class="search-input w-full bg-slate-50 border border-slate-200 px-12 py-2.5 rounded-2xl text-sm transition-all focus:ring-4 focus:ring-indigo-600/5 focus:border-indigo-600/30 outline-none shadow-inner group-hover:border-slate-300">
            <span
                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-indigo-600 transition-colors">search</span>
            <?php if ($search): ?>
                <a href="index.php"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 hover:text-red-500 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </a>
            <?php endif; ?>
        </form>
        <?php
        $header_custom_content = ob_get_clean();
        include 'includes/header.php';
        ?>

        <div class="flex-1 overflow-y-auto main-scroll">
            <div class="max-w-[1400px] mx-auto px-4 md:px-8 lg:px-10 pb-12">

                <!-- Hero / Featured -->
                <?php if (empty($category_slug) && empty($search) && $page == 1): ?>
                    <div
                        class="hero-gradient rounded-[32px] p-8 md:p-12 mb-10 relative overflow-hidden shadow-2xl shadow-indigo-100 group mt-6">
                        <div class="relative z-10 max-w-2xl">
                            <span
                                class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black text-white uppercase tracking-widest mb-4">Official
                                Release</span>
                            <h2 class="text-4xl md:text-5xl font-black text-white mb-4 tracking-tight leading-tight">Your
                                gateway to <br>premium software.</h2>
                            <p class="text-indigo-100 text-lg mb-8 leading-relaxed opacity-90 font-medium">Secure, verified,
                                and high-speed downloads for all your APK and Windows tool requirements.</p>
                            <div class="flex flex-wrap gap-4">
                                <a href="#marketplace"
                                    class="bg-white text-indigo-900 px-8 py-3.5 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-indigo-50 transition-all shadow-xl active:scale-95">Browse
                                    Library</a>
                            </div>
                        </div>
                        <div
                            class="absolute -right-20 -bottom-20 w-96 h-96 bg-white/10 rounded-full blur-3xl group-hover:bg-white/20 transition-all duration-700">
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (empty($category_slug) && empty($search) && $page == 1): ?>
                    <!-- Featured Sections -->
                    <?php 
                    $sections = [
                        ['title' => 'Top Downloads', 'icon' => 'download_for_offline', 'data' => $featured_apps['top'], 'color' => 'indigo', 'id' => 'top'],
                        ['title' => 'Popular Applications', 'icon' => 'trending_up', 'data' => $featured_apps['popular'], 'color' => 'orange', 'id' => 'popular'],
                        ['title' => 'Recently Added', 'icon' => 'new_releases', 'data' => $featured_apps['recent'], 'color' => 'green', 'id' => 'recent']
                    ];
                    
                    foreach ($sections as $sec): 
                        if (empty($sec['data'])) continue;
                    ?>
                        <div class="mb-12" id="<?php echo $sec['id']; ?>">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-2xl bg-<?php echo $sec['color']; ?>-50 flex items-center justify-center text-<?php echo $sec['color']; ?>-600 shadow-sm border border-<?php echo $sec['color']; ?>-100">
                                        <span class="material-symbols-outlined"><?php echo $sec['icon']; ?></span>
                                    </div>
                                    <h3 class="text-xl font-black text-slate-900 tracking-tight"><?php echo $sec['title']; ?></h3>
                                </div>
                                <a href="index.php" class="text-[10px] font-black text-slate-400 hover:text-indigo-600 uppercase tracking-widest flex items-center gap-2">View All <span class="material-symbols-outlined text-[14px]">arrow_forward</span></a>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
                                <?php foreach ($sec['data'] as $app) {
                                    echo renderAppCard($app, $base_url, $assets_url);
                                } ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Category Sections (Windows & Android) -->
                    <?php 
                    $cat_display = [
                        ['title' => 'Windows Essentials', 'icon' => 'desktop_windows', 'data' => $cat_sections['windows'], 'color' => 'blue', 'slug' => 'windows', 'id' => 'windows-sec'],
                        ['title' => 'Android Apps', 'icon' => 'android', 'data' => $cat_sections['apk'], 'color' => 'emerald', 'slug' => 'apk', 'id' => 'android-sec']
                    ];
                    
                    foreach ($cat_display as $sec): 
                        if (empty($sec['data'])) continue;
                    ?>
                        <div class="mb-12" id="<?php echo $sec['id']; ?>">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-2xl bg-<?php echo $sec['color']; ?>-50 flex items-center justify-center text-<?php echo $sec['color']; ?>-600 shadow-sm border border-<?php echo $sec['color']; ?>-100">
                                        <span class="material-symbols-outlined"><?php echo $sec['icon']; ?></span>
                                    </div>
                                    <h3 class="text-xl font-black text-slate-900 tracking-tight"><?php echo $sec['title']; ?></h3>
                                </div>
                                <a href="category/<?php echo $sec['slug']; ?>" class="text-[10px] font-black text-slate-400 hover:text-indigo-600 uppercase tracking-widest flex items-center gap-2">View All <span class="material-symbols-outlined text-[14px]">arrow_forward</span></a>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
                                <?php foreach ($sec['data'] as $app) {
                                    echo renderAppCard($app, $base_url, $assets_url);
                                } ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div id="marketplace"
                    class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 mt-6">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">
                            <?php
                            if ($search)
                                echo "Results for \"$search\"";
                            elseif ($category_slug)
                                echo "Category: " . htmlspecialchars($category_slug);
                            else
                                echo "Trending Applications";
                            ?>
                        </h3>
                        <p class="text-slate-500 font-medium mt-1 text-sm italic"><?php echo $total_apps; ?> verified
                            items available</p>
                    </div>
                </div>

                <!-- App Grid -->
                <?php if (empty($apps)): ?>
                    <div class="py-20 flex flex-col items-center justify-center text-center">
                        <div
                            class="w-24 h-24 bg-slate-100 rounded-[40px] flex items-center justify-center text-slate-300 mb-6">
                            <span class="material-symbols-outlined text-5xl">search_off</span>
                        </div>
                        <h4 class="text-xl font-bold text-slate-900">No applications found</h4>
                        <p class="text-slate-500 mt-2">Try adjusting your filters or search terms.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
                        <?php foreach ($apps as $app) {
                            echo renderAppCard($app, $base_url, $assets_url);
                        } ?>
                    </div>
                <?php endif; ?>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="mt-16 flex justify-center items-center gap-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&category=<?php echo urlencode($category_slug); ?>&search=<?php echo urlencode($search); ?>"
                                class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:border-indigo-600 hover:text-indigo-600 transition-all shadow-sm">
                                <span class="material-symbols-outlined">chevron_left</span>
                            </a>
                        <?php endif; ?>

                        <div
                            class="px-6 py-2.5 bg-indigo-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-indigo-100">
                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                        </div>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&category=<?php echo urlencode($category_slug); ?>&search=<?php echo urlencode($search); ?>"
                                class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:border-indigo-600 hover:text-indigo-600 transition-all shadow-sm">
                                <span class="material-symbols-outlined">chevron_right</span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

        </div>
        <?php include 'includes/footer.php'; ?>
        </div>
    </main>
    <script>
        async function toggleFavorite(event, appId) {
            event.preventDefault();
            event.stopPropagation();

            const btn = document.getElementById('favBtn-' + appId);
            const icon = btn.querySelector('.material-symbols-outlined');

            try {
                const response = await fetch('user/favorites_action.php?app_id=' + appId);
                const data = await response.json();

                if (data.status === 'added') {
                    btn.classList.add('bg-pink-500', 'text-white');
                    btn.classList.remove('bg-white/80', 'text-slate-400');
                    icon.innerText = 'favorite';
                    icon.classList.add('fill-current');
                } else {
                    btn.classList.remove('bg-pink-500', 'text-white');
                    btn.classList.add('bg-white/80', 'text-slate-400');
                    icon.innerText = 'favorite_border';
                    icon.classList.remove('fill-current');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
    </script>
</body>

</html>