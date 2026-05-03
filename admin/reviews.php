<?php
require_once '../config.php';
require_once '../db.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

$success = '';
$error = '';

// Handle Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    verifyCsrfToken($_POST['csrf_token'] ?? '');
    $review_id = (int) $_POST['review_id'];
    
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    if ($stmt->execute([$review_id])) {
        $success = "Review deleted successfully.";
        logActivity('admin_action', "Deleted review ID: $review_id from management panel");
    } else {
        $error = "Failed to delete review.";
    }
}

// Fetch Reviews with App Names and Usernames
$stmt = $pdo->query("SELECT r.*, u.username, a.name as app_name, a.slug as app_slug 
                    FROM reviews r 
                    JOIN users u ON r.user_id = u.id 
                    JOIN apps a ON r.app_id = a.id 
                    ORDER BY r.created_at DESC");
$reviews = $stmt->fetchAll();

$reportsCount = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - Admin Panel</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">

    <style>
        :root {
            --primary: #1f108e;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-900: #0f172a;
        }
        body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; color: #1e293b; }
        .sidebar { background: var(--slate-50); border-right: 1px solid var(--slate-200); }
        .nav-item { transition: all 0.2s; font-weight: 500; font-size: 14px; color: var(--slate-600); }
        .nav-item:hover { background-color: var(--slate-100); color: var(--primary); }
        .nav-item.active { background-color: #ffffff; color: var(--primary); border-right: 3px solid var(--primary); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .card-white { background: #ffffff; border-radius: 24px; border: 1px solid var(--slate-200); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .main-scroll::-webkit-scrollbar { width: 5px; }
        .main-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-[#1d1d1f]">

<?php include 'components/sidebar.php'; ?>
<?php include 'components/mobile_sidebar.php'; ?>

<!-- Main Area -->
<main class="flex-1 flex flex-col h-full bg-[#f7f9fb] relative z-10 w-full overflow-hidden">
    <?php include 'components/header.php'; ?>

    <div class="flex-1 overflow-y-auto px-4 md:px-8 lg:px-10 pb-12 main-scroll">
        <div class="max-w-[1200px] mx-auto mt-6">
            
            <header class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Community Reviews</h2>
                    <p class="text-slate-500 mt-1 font-medium">Monitor and moderate user feedback across all applications.</p>
                </div>
            </header>

            <?php if ($success): ?>
                <div class="bg-indigo-50 text-indigo-700 p-4 rounded-2xl mb-6 text-sm font-bold border border-indigo-100"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-50 text-red-700 p-4 rounded-2xl mb-6 text-sm font-bold border border-red-100"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card-white overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">User</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Application</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Rating</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Comment</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (empty($reviews)): ?>
                            <tr>
                                <td colspan="6" class="py-20 text-center text-slate-400 font-medium text-sm italic">No reviews found in the database.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($reviews as $rev): ?>
                            <tr class="hover:bg-slate-50/30 transition-all">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-xs font-bold">
                                            <?php echo strtoupper(substr($rev['username'], 0, 1)); ?>
                                        </div>
                                        <span class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($rev['username']); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="../<?php echo htmlspecialchars($rev['app_slug']); ?>" target="_blank" class="text-sm font-bold text-indigo-600 hover:underline"><?php echo htmlspecialchars($rev['app_name']); ?></a>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex text-amber-400">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <span class="material-symbols-outlined text-[14px] <?php echo $i <= $rev['rating'] ? 'fill-current' : ''; ?>">star</span>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-slate-600 line-clamp-1 max-w-xs" title="<?php echo htmlspecialchars($rev['comment']); ?>"><?php echo htmlspecialchars($rev['comment']); ?></p>
                                </td>
                                <td class="px-6 py-4 text-xs font-medium text-slate-400">
                                    <?php echo date('M d, Y', strtotime($rev['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form method="POST" onsubmit="return confirm('Delete this review?')">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="review_id" value="<?php echo $rev['id']; ?>">
                                        <button type="submit" name="delete_review" class="p-2 text-slate-300 hover:text-red-500 transition-colors">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

</body>
</html>
