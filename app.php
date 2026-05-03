<?php
require_once 'config.php';
require_once 'db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM apps WHERE id = ?");
$stmt->execute([$id]);
$app = $stmt->fetch();

if (!$app) {
    die("App not found.");
}

// SEO Redirect to slug URL
if (!empty($app['slug'])) {
    header("Location: " . $app['slug'], true, 301);
    exit();
}

$is_owner = isLoggedIn() && $_SESSION['user_id'] == $app['user_id'];
$is_admin = isAdmin();

if ($app['status'] !== 'approved' && !$is_owner && !$is_admin) {
    die("App not found or not approved.");
}

// Review Submission Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review']) && isLoggedIn()) {
    verifyCsrfToken($_POST['csrf_token'] ?? '');
    $rating = (int) $_POST['rating'];
    $comment = sanitizeInput($_POST['comment']);
    $user_id = $_SESSION['user_id'];

    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        // Check if user already reviewed
        $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND app_id = ?");
        $stmt->execute([$user_id, $id]);
        if ($stmt->fetch()) {
            $review_error = "You have already reviewed this app.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO reviews (user_id, app_id, rating, comment) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $id, $rating, $comment])) {
                $review_success = "Review submitted successfully!";
            } else {
                $review_error = "Failed to submit review.";
            }
        }
    } else {
        $review_error = "Please provide both a rating and a comment.";
    }
}

// Fetch Reviews
$stmt = $pdo->prepare("SELECT r.*, u.username, u.profile_pic FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.app_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

// Calculate Average Rating
$avg_rating = 0;
if (count($reviews) > 0) {
    $total_rating = 0;
    foreach ($reviews as $rev) {
        $total_rating += $rev['rating'];
    }
    $avg_rating = round($total_rating / count($reviews), 1);
}

// Check if Favorited
$is_favorited = false;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND app_id = ?");
    $stmt->execute([$_SESSION['user_id'], $id]);
    $is_favorited = (bool) $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($app['name']); ?> - Download Latest Version | ShreeBitu</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr(strip_tags($app['description']), 0, 160)); ?>">

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
            font-family: 'Inter', sans-serif !important;
            background-color: #f7f9fb !important;
            color: #1e293b;
        }

        .sidebar {
            background: var(--slate-50) !important;
            border-right: 1px solid var(--slate-200);
        }

        .sidebar-mobile {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 260px;
            z-index: 999 !important;
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex !important;
            flex-direction: column;
            box-shadow: 20px 0 50px rgba(0, 0, 0, 0.1);
            background: var(--slate-50) !important;
        }

        .sidebar-mobile.open {
            transform: translateX(0);
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.3);
            z-index: 998 !important;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
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

        .card-white {
            background: #ffffff;
            border-radius: 32px;
            border: 1px solid var(--slate-200);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
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
        $page_title = $app['name'];
        $breadcrumb_html = '
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <a href="index.php" class="hover:text-indigo-600 font-medium transition-colors">Marketplace</a>
                <span class="material-symbols-outlined text-slate-300">chevron_right</span>
                <span class="font-bold text-slate-900 uppercase tracking-widest text-[11px]">' . htmlspecialchars($app['category']) . '</span>
            </div>';
        include 'includes/header.php'; 
        ?>

        <div class="flex-1 overflow-y-auto px-4 md:px-8 lg:px-10 pb-12 main-scroll">
            <div class="max-w-[1200px] mx-auto mt-6">

                <!-- App Hero Card -->
                <div class="card-white p-8 md:p-12 mb-8 bg-white overflow-hidden">
                    <div class="flex flex-col md:flex-row gap-10 items-center md:items-start text-center md:text-left">
                        <div
                            class="w-36 h-36 md:w-44 md:h-44 rounded-[40px] bg-slate-50 flex items-center justify-center overflow-hidden shadow-sm flex-shrink-0 border border-slate-100">
                            <img src="<?php echo $base_url . htmlspecialchars($app['logo']); ?>"
                                alt="<?php echo htmlspecialchars($app['name']); ?>" class="w-full h-full object-cover"
                                onerror="this.src='<?php echo $assets_url; ?>images/logo.png'">
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-wrap justify-center md:justify-start items-center gap-3 mb-4">
                                <span
                                    class="px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-widest rounded-full border border-indigo-100">APK</span>
                                <span
                                    class="flex items-center gap-1.5 text-[10px] font-black text-green-600 uppercase tracking-widest">
                                    <span class="material-symbols-outlined text-[14px]">verified_user</span> Verified
                                    Safe
                                </span>
                                <span
                                    class="flex items-center gap-1.5 text-[10px] font-black text-amber-500 uppercase tracking-widest">
                                    <span class="material-symbols-outlined text-[14px]">star</span> Featured App
                                </span>
                            </div>
                            <h2 class="text-4xl md:text-6xl font-black text-slate-900 mb-6 tracking-tight">
                                <?php echo htmlspecialchars($app['name']); ?>
                            </h2>

                            <div class="flex flex-wrap justify-center md:justify-start items-center gap-4">
                                <a href="download.php?id=<?php echo $app['id']; ?>"
                                    class="bg-indigo-900 text-white px-8 py-4 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-indigo-800 transition-all shadow-xl shadow-indigo-100 flex items-center gap-3 active:scale-95">
                                    <span class="material-symbols-outlined">download</span>
                                    Download Now
                                </a>
                                <?php if (isLoggedIn()): ?>
                                    <button onclick="toggleFavorite(<?php echo $app['id']; ?>)" id="favBtn"
                                        class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all shadow-lg active:scale-90 <?php echo $is_favorited ? 'bg-pink-500 text-white shadow-pink-100' : 'bg-white text-slate-400 border border-slate-200 hover:border-pink-200 hover:text-pink-500'; ?>">
                                        <span
                                            class="material-symbols-outlined <?php echo $is_favorited ? 'fill-current' : ''; ?> text-xl"><?php echo $is_favorited ? 'favorite' : 'favorite_border'; ?></span>
                                    </button>
                                <?php endif; ?>
                                <div class="flex items-center gap-1 text-amber-400">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span
                                            class="material-symbols-outlined text-[20px] <?php echo $i <= $avg_rating ? 'fill-current' : ''; ?>">star</span>
                                    <?php endfor; ?>
                                    <span
                                        class="text-slate-400 font-bold ml-1 text-sm"><?php echo $avg_rating; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column: Content -->
                    <div class="lg:col-span-2 space-y-8">
                        <div class="card-white p-8 md:p-12">
                            <h3 class="text-2xl font-black text-slate-900 mb-8 flex items-center gap-4">
                                <span class="w-2 h-8 bg-indigo-600 rounded-full"></span>
                                App Overview
                            </h3>
                            <div class="prose max-w-none text-slate-600 leading-relaxed text-lg">
                                <?php echo $app['description']; ?>
                            </div>
                        </div>

                        <!-- Reviews Section -->
                        <div class="card-white p-8 md:p-12">
                            <h3 class="text-2xl font-black text-slate-900 mb-8 flex items-center gap-4">
                                <span class="w-2 h-8 bg-indigo-600 rounded-full"></span>
                                User Reviews
                            </h3>

                            <?php if (isLoggedIn()): ?>
                                <div class="bg-slate-50 rounded-3xl p-8 mb-10 border border-slate-100">
                                    <h4 class="font-bold text-slate-900 mb-6">Write a review</h4>
                                    <?php if (isset($review_error)): ?>
                                        <p class="text-red-500 text-sm mb-4 font-bold"><?php echo $review_error; ?></p>
                                    <?php endif; ?>
                                    <?php if (isset($review_success)): ?>
                                        <p class="text-green-600 text-sm mb-4 font-bold"><?php echo $review_success; ?></p>
                                    <?php endif; ?>

                                    <form action="" method="POST" class="space-y-4">
                                        <input type="hidden" name="submit_review" value="1">
                                        <input type="hidden" name="csrf_token"
                                            value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <div class="flex items-center gap-4 mb-2">
                                            <label class="text-sm font-bold text-slate-700">Your Rating:</label>
                                            <div class="flex gap-1" id="starRating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <button type="button" onclick="setRating(<?php echo $i; ?>)"
                                                        class="material-symbols-outlined text-slate-300 hover:text-amber-400 transition-colors rating-star"
                                                        data-value="<?php echo $i; ?>">star</button>
                                                <?php endfor; ?>
                                            </div>
                                            <input type="hidden" name="rating" id="ratingInput" value="5" required>
                                        </div>
                                        <textarea name="comment" rows="4"
                                            class="w-full px-6 py-4 rounded-2xl border border-slate-200 outline-none focus:border-indigo-600 transition-all text-sm leading-relaxed"
                                            placeholder="Tell us what you think about this app..." required></textarea>
                                        <button type="submit"
                                            class="bg-indigo-900 text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-800 transition-all">Submit
                                            Review</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="bg-indigo-50 border border-indigo-100 rounded-3xl p-8 mb-10 text-center">
                                    <p class="text-indigo-900 font-bold mb-4">You must be logged in to leave a review.</p>
                                    <a href="auth/login.php"
                                        class="bg-indigo-900 text-white px-6 py-2 rounded-xl text-xs font-black uppercase tracking-widest inline-block">Login
                                        Now</a>
                                </div>
                            <?php endif; ?>

                            <div class="space-y-8">
                                <?php if (!empty($reviews)): ?>
                                    <?php foreach ($reviews as $rev): ?>
                                        <div class="flex gap-4 pb-8 border-b border-slate-50 last:border-0">
                                            <div
                                                class="w-10 h-10 rounded-full bg-indigo-100 flex-shrink-0 flex items-center justify-center text-indigo-700 font-bold text-sm overflow-hidden border border-white shadow-sm">
                                                <?php if (!empty($rev['profile_pic'])): ?>
                                                    <img src="<?php echo htmlspecialchars($rev['profile_pic']); ?>"
                                                        class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <?php echo strtoupper(substr($rev['username'], 0, 1)); ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between mb-1">
                                                    <h5 class="font-bold text-slate-900 text-sm">
                                                        <?php echo htmlspecialchars($rev['username']); ?>
                                                    </h5>
                                                    <span
                                                        class="text-[11px] text-slate-400"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></span>
                                                </div>
                                                <div class="flex text-amber-400 mb-2">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <span
                                                            class="material-symbols-outlined text-[14px] <?php echo $i <= $rev['rating'] ? 'fill-current' : ''; ?>">star</span>
                                                    <?php endfor; ?>
                                                </div>
                                                <p class="text-[14px] text-slate-600 leading-relaxed">
                                                    <?php echo nl2br(htmlspecialchars($rev['comment'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-10 opacity-50">
                                        <span class="material-symbols-outlined text-4xl mb-2">rate_review</span>
                                        <p class="text-sm">No reviews yet. Be the first to share your thoughts!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Info & Specs -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="card-white p-8">
                            <h3 class="text-lg font-bold text-slate-900 mb-6 border-b border-slate-100 pb-4">
                                Specifications</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-500 font-medium">Version</span>
                                    <span class="text-sm text-slate-900 font-bold">Latest Official</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-500 font-medium">File Size</span>
                                    <span
                                        class="text-sm text-slate-900 font-bold"><?php echo htmlspecialchars($app['file_size'] ?? 'Varies'); ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-500 font-medium">OS</span>
                                    <span
                                        class="text-sm text-slate-900 font-bold"><?php echo htmlspecialchars($app['os_compatible'] ?? 'Multi-platform'); ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-500 font-medium">License</span>
                                    <span
                                        class="text-sm text-indigo-600 font-bold"><?php echo htmlspecialchars($app['license_type'] ?? 'Free'); ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-500 font-medium">Developer</span>
                                    <span
                                        class="text-sm text-slate-900 font-bold"><?php echo htmlspecialchars($app['developer'] ?? 'Independent'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-indigo-50 rounded-3xl p-8 border border-indigo-100">
                            <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm mb-4">
                                <span class="material-symbols-outlined text-indigo-600">security</span>
                            </div>
                            <h4 class="font-bold text-indigo-900 mb-2">Safety Guaranteed</h4>
                            <p class="text-sm text-indigo-700 leading-relaxed opacity-80">Our team manually verifies
                                every app to ensure it's free from malware and follows our community guidelines.</p>
                        </div>

                        <div class="p-4 flex flex-col gap-3">
                            <a href="report.php?id=<?php echo $app['id']; ?>"
                                class="flex items-center justify-center gap-2 text-slate-400 hover:text-red-500 text-xs font-bold uppercase tracking-widest transition-colors">
                                <span class="material-symbols-outlined text-[16px]">flag</span>
                                Report Issue
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function setRating(val) {
            document.getElementById('ratingInput').value = val;
            const stars = document.querySelectorAll('.rating-star');
            stars.forEach((star, index) => {
                if (index < val) {
                    star.classList.add('fill-current', 'text-amber-400');
                    star.classList.remove('text-slate-300');
                } else {
                    star.classList.remove('fill-current', 'text-amber-400');
                    star.classList.add('text-slate-300');
                }
            });
        }

        async function toggleFavorite(appId) {
            const btn = document.getElementById('favBtn');
            const icon = btn.querySelector('.material-symbols-outlined');

            try {
                const response = await fetch('user/favorites_action.php?app_id=' + appId);
                const data = await response.json();

                if (data.status === 'added') {
                    btn.classList.add('bg-pink-500', 'text-white', 'shadow-pink-100');
                    btn.classList.remove('bg-white', 'text-slate-400', 'border', 'border-slate-200');
                    icon.innerText = 'favorite';
                    icon.classList.add('fill-current');
                } else {
                    btn.classList.remove('bg-pink-500', 'text-white', 'shadow-pink-100');
                    btn.classList.add('bg-white', 'text-slate-400', 'border', 'border-slate-200');
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