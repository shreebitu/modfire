<?php
require_once 'config.php';
require_once 'db.php';

$cat_slug = isset($_GET['slug']) ? sanitizeInput($_GET['slug']) : '';

if (empty($cat_slug)) {
    header("Location: index.php");
    exit();
}

// Fetch category details
$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
$stmt->execute([$cat_slug]);
$category_details = $stmt->fetch();

if (!$category_details) {
    header("Location: 404.php");
    exit();
}

$category_id = $category_details['id'];
$category_name = $category_details['name'];

$page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? (int) $_GET['page'] : 1;
$limit = 24;
$offset = ($page - 1) * $limit;

$params = [$category_id];
$where = ["apps.status = 'approved'", "apps.category_id = ?"];

if (isLoggedIn()) {
    $current_user_id = $_SESSION['user_id'];
    $where[] = "(users.shadow_banned = 0 OR apps.user_id = ?)";
    $params[] = $current_user_id;
} else {
    $where[] = "users.shadow_banned = 0";
}

$where_clause = implode(" AND ", $where);

if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT apps.*, users.username as uploader_name, (SELECT ROUND(AVG(rating), 1) FROM reviews WHERE app_id = apps.id) as avg_rating, 
                           (SELECT COUNT(*) FROM favorites WHERE user_id = ? AND app_id = apps.id) as is_favorited
                           FROM apps 
                           JOIN users ON apps.user_id = users.id
                           WHERE $where_clause 
                           ORDER BY apps.created_at DESC 
                           LIMIT $limit OFFSET $offset");
    $stmt->execute(array_merge([$current_user_id], $params));
} else {
    $stmt = $pdo->prepare("SELECT apps.*, users.username as uploader_name, (SELECT ROUND(AVG(rating), 1) FROM reviews WHERE app_id = apps.id) as avg_rating, 
                           0 as is_favorited
                           FROM apps 
                           JOIN users ON apps.user_id = users.id
                           WHERE $where_clause 
                           ORDER BY apps.created_at DESC 
                           LIMIT $limit OFFSET $offset");
    $stmt->execute($params);
}
$apps = $stmt->fetchAll();

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM apps JOIN users ON apps.user_id = users.id WHERE $where_clause");
$stmtCount->execute($params);
$total_apps = $stmtCount->fetchColumn();
$total_pages = ceil($total_apps / $limit);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?php echo $base_url; ?>">
    <title><?php echo htmlspecialchars($category_name); ?> - Browse Apps | ShreeBitu</title>
    <meta name="description" content="Discover and download premium apps in the <?php echo htmlspecialchars($category_name); ?> category.">

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
        $page_title = $category_name;
        $breadcrumb_html = '
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-3 py-1.5 bg-white rounded-full border border-slate-200 shadow-sm">
                    <a href="index.php" class="text-[10px] font-black text-slate-400 hover:text-indigo-600 transition-colors uppercase tracking-widest">Marketplace</a>
                    <span class="material-symbols-outlined text-slate-300 text-[14px]">chevron_right</span>
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">' . htmlspecialchars($category_name) . '</span>
                </div>
            </div>';
        include 'includes/header.php';
        ?>

        <div class="flex-1 overflow-y-auto px-4 md:px-8 lg:px-10 pb-12 main-scroll">
            <div class="max-w-[1400px] mx-auto">

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 mt-6">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">
                            Category: <?php echo htmlspecialchars($category_name); ?>
                        </h3>
                        <p class="text-slate-500 font-medium mt-1 text-sm italic"><?php echo $total_apps; ?> apps in this category</p>
                    </div>
                </div>

                <!-- App Grid -->
                <?php if (empty($apps)): ?>
                    <div class="py-20 flex flex-col items-center justify-center text-center">
                        <div
                            class="w-24 h-24 bg-slate-100 rounded-[40px] flex items-center justify-center text-slate-300 mb-6">
                            <span class="material-symbols-outlined text-5xl">inventory_2</span>
                        </div>
                        <h4 class="text-xl font-bold text-slate-900">No applications in this category</h4>
                        <p class="text-slate-500 mt-2">Check back later for new updates.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
                        <?php foreach ($apps as $app): ?>
                            <a href="<?php echo htmlspecialchars($app['slug']); ?>"
                                class="app-card bg-white p-4 rounded-[28px] flex flex-col group h-full">
                                <div
                                    class="aspect-square w-full rounded-2xl bg-slate-50 mb-4 overflow-hidden relative shadow-inner">
                                    <img src="<?php echo $base_url . htmlspecialchars($app['logo']); ?>"
                                        alt="<?php echo htmlspecialchars($app['name']); ?>"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                        onerror="this.src='<?php echo $assets_url; ?>images/logo.png'">

                                    <!-- Favorite Button -->
                                    <?php if (isLoggedIn()): ?>
                                        <button onclick="toggleFavorite(event, <?php echo $app['id']; ?>)"
                                            id="favBtn-<?php echo $app['id']; ?>"
                                            class="absolute top-2 left-2 w-7 h-7 rounded-lg flex items-center justify-center transition-all shadow-sm backdrop-blur-md active:scale-90 z-20 <?php echo $app['is_favorited'] ? 'bg-pink-500 text-white' : 'bg-white/80 text-slate-400 hover:text-pink-500'; ?>">
                                            <span
                                                class="material-symbols-outlined <?php echo $app['is_favorited'] ? 'fill-current' : ''; ?> text-[16px]"><?php echo $app['is_favorited'] ? 'favorite' : 'favorite_border'; ?></span>
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($app['avg_rating']): ?>
                                        <div
                                            class="absolute bottom-2 right-2 px-2 py-1 bg-white/90 backdrop-blur rounded-lg shadow-sm flex items-center gap-1">
                                            <span
                                                class="material-symbols-outlined text-amber-400 text-[12px] fill-current">star</span>
                                            <span
                                                class="text-[10px] font-bold text-slate-700"><?php echo $app['avg_rating']; ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <span
                                        class="text-[9px] font-black text-indigo-600 uppercase tracking-widest mb-1 block"><?php echo htmlspecialchars($category_name); ?></span>
                                    <h4
                                        class="text-[14px] font-bold text-slate-900 leading-tight line-clamp-2 group-hover:text-indigo-600 transition-colors">
                                        <?php echo htmlspecialchars($app['name']); ?>
                                    </h4>
                                </div>
                                <div class="mt-4 pt-4 border-t border-slate-50 flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="material-symbols-outlined text-slate-400 text-[14px]">download_for_offline</span>
                                        <span
                                            class="text-[10px] font-black text-slate-500"><?php echo number_format($app['downloads']); ?></span>
                                    </div>
                                    <span
                                        class="material-symbols-outlined text-slate-300 group-hover:text-indigo-600 transition-colors translate-x-2 opacity-0 group-hover:opacity-100 group-hover:translate-x-0 transition-all">arrow_forward</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="mt-16 flex justify-center items-center gap-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>"
                                class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:border-indigo-600 hover:text-indigo-600 transition-all shadow-sm">
                                <span class="material-symbols-outlined">chevron_left</span>
                            </a>
                        <?php endif; ?>

                        <div
                            class="px-6 py-2.5 bg-indigo-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-indigo-100">
                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                        </div>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>"
                                class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:border-indigo-600 hover:text-indigo-600 transition-all shadow-sm">
                                <span class="material-symbols-outlined">chevron_right</span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
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
