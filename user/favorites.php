<?php
require_once '../config.php';
require_once '../db.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];

// Fetch user data for the header
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch favorited apps
$stmtFavs = $pdo->prepare("SELECT apps.* FROM apps JOIN favorites ON apps.id = favorites.app_id WHERE favorites.user_id = ? ORDER BY favorites.created_at DESC");
$stmtFavs->execute([$user_id]);
$favorited_apps = $stmtFavs->fetchAll();

$current_page = 'favorites.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites - ShreeBitu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"
        rel="stylesheet">
    <link rel="icon" type="image/png" href="<?php echo $assets_url; ?>images/logo.png">
    <style>
        :root {
            --primary: #1f108e;
            --primary-light: #eef2ff;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fb;
        }

        .header-blur {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(241, 245, 249, 0.5);
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

    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Area -->
    <main class="flex-1 flex flex-col h-full bg-[#f7f9fb] relative z-10 w-full overflow-hidden">
        <!-- Top Nav -->
        <?php
        $page_title = 'My Favorites';
        $breadcrumb_html = '<span class="font-bold text-slate-900 uppercase tracking-widest text-[11px]">Saved Collections</span>';
        include '../includes/header.php';
        ?>

        <div class="flex-1 overflow-y-auto px-4 md:px-8 lg:px-10 pb-12 main-scroll">
            <div class="max-w-[1000px] mx-auto mt-6 md:mt-10">

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">My Favorites</h2>
                        <p class="text-slate-500 text-xs md:text-sm font-medium mt-1">Your curated collection of apps
                            and tools.</p>
                    </div>
                    <div
                        class="inline-flex self-start sm:self-auto bg-pink-50 text-pink-600 px-4 py-1.5 md:px-5 md:py-2 rounded-xl md:rounded-2xl text-[10px] md:text-xs font-black uppercase tracking-widest border border-pink-100 shadow-sm">
                        <?php echo count($favorited_apps); ?> Saved Items
                    </div>
                </div>

                <?php if (empty($favorited_apps)): ?>
                    <div
                        class="bg-white rounded-[32px] md:rounded-[40px] border border-slate-100 p-10 md:p-20 text-center shadow-sm">
                        <div
                            class="w-16 h-16 md:w-20 md:h-20 bg-slate-50 rounded-2xl md:rounded-3xl flex items-center justify-center text-slate-300 mx-auto mb-6 shadow-inner">
                            <span class="material-symbols-outlined text-4xl md:text-5xl">favorite</span>
                        </div>
                        <h3 class="text-lg md:text-xl font-bold text-slate-900 mb-2">No favorites yet</h3>
                        <p class="text-slate-500 text-sm font-medium max-w-[240px] md:max-w-xs mx-auto">Start exploring the
                            marketplace and save the apps you love!</p>
                        <a href="../index.php"
                            class="inline-flex items-center justify-center gap-2 mt-8 bg-indigo-900 text-white px-8 py-4 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-indigo-800 transition-all shadow-xl shadow-indigo-100 active:scale-95 w-full sm:w-auto">
                            <span class="material-symbols-outlined text-lg">explore</span>
                            Explore Marketplace
                        </a>
                    </div>
                <?php else: ?>
                    <div class="space-y-3 md:space-y-4">
                        <?php foreach ($favorited_apps as $app): ?>
                            <div
                                class="bg-white rounded-2xl md:rounded-3xl border border-slate-100 p-3 md:p-4 shadow-sm hover:shadow-md transition-all flex items-center gap-4 group">
                                <div
                                    class="w-12 h-12 md:w-14 md:h-14 rounded-xl md:rounded-2xl bg-slate-50 overflow-hidden flex-shrink-0 shadow-inner">
                                    <img src="../<?php echo htmlspecialchars($app['logo']); ?>"
                                        class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-[13px] md:text-base font-black text-slate-900 truncate leading-tight">
                                        <?php echo htmlspecialchars($app['name']); ?></h4>
                                    <p
                                        class="text-[10px] md:text-[11px] text-slate-500 font-bold uppercase tracking-widest mt-0.5 truncate opacity-70">
                                        <?php echo htmlspecialchars($app['category']); ?></p>
                                </div>
                                <div class="flex items-center gap-2 md:gap-3">
                                    <a href="../app.php?id=<?php echo $app['id']; ?>"
                                        class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                        <span class="material-symbols-outlined text-[18px] md:text-[20px]">visibility</span>
                                    </a>
                                    <button onclick="toggleFavorite(event, <?php echo $app['id']; ?>, this)"
                                        class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm active:scale-90">
                                        <span class="material-symbols-outlined text-[18px] md:text-[20px]">delete</span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        async function toggleFavorite(event, appId, btn) {
            event.preventDefault();
            try {
                const response = await fetch('favorites_action.php?app_id=' + appId);
                const data = await response.json();

                if (data.status === 'removed') {
                    // Refresh page to show updated list on favorites page
                    location.reload();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
    </script>

</body>

</html>