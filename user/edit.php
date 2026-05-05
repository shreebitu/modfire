<?php
require_once '../includes/init.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM apps WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$app = $stmt->fetch();

if (!$app) {
    die("App not found or you do not have permission to edit it.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $apk_link = sanitizeInput($_POST['apk_link'] ?? '');
    
    // Duplicate URL Detection (exclude current app)
    if (!empty($apk_link)) {
        $stmt = $pdo->prepare("SELECT id FROM apps WHERE apk_link = ? AND id != ?");
        $stmt->execute([$apk_link, $id]);
        if ($stmt->fetch()) {
            $error = "This URL has already been submitted by another app.";
        }
    }

    $name = sanitizeInput($_POST['name'] ?? $app['name']);
    
    // Allow basic HTML tags
    $allowed_tags = '<p><br><b><strong><i><em><u><ul><ol><li><a href title target><h1><h2><h3><h4><h5><h6><img><blockquote><span><div><hr><table><tbody><tr><td><th>';
    
    // Support both mobile/desktop inputs if they exist
    $description = isset($_POST['description_desktop']) ? strip_tags(trim($_POST['description_desktop']), $allowed_tags) : $app['description'];
    $category = sanitizeInput($_POST['category_desktop'] ?? $app['category']);
    $file_size = sanitizeInput($_POST['file_size_desktop'] ?? $app['file_size']);
    $os_compatible = sanitizeInput($_POST['os_compatible_desktop'] ?? $app['os_compatible']);
    $language = sanitizeInput($_POST['language_desktop'] ?? $app['language']);
    $license_type = sanitizeInput($_POST['license_type_desktop'] ?? $app['license_type']);
    $developer = sanitizeInput($_POST['developer_desktop'] ?? $app['developer']);

    // Handle logo upload
    $logo_path = $app['logo']; // Default to old logo
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $file_tmp = $_FILES['logo']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['logo']['name']);
        
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $logo_path = 'uploads/' . $file_name;
        } else {
            $error = "Failed to upload logo.";
        }
    }

    if (empty($error)) {
        $stmt = $pdo->prepare("UPDATE apps SET name = ?, description = ?, logo = ?, apk_link = ?, category = ?, file_size = ?, os_compatible = ?, language = ?, license_type = ?, developer = ?, status = 'pending' WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$name, $description, $logo_path, $apk_link, $category, $file_size, $os_compatible, $language, $license_type, $developer, $id, $user_id])) {
            $success = "App updated successfully! Changes are pending review.";
            // Refresh app data
            $stmt = $pdo->prepare("SELECT * FROM apps WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $app = $stmt->fetch();
        } else {
            $error = "Database error. Failed to update.";
        }
    }
}

// Fetch categories
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editing: <?php echo htmlspecialchars($app['name']); ?> - ShreeBitu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
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
        body { font-family: 'Inter', sans-serif !important; background-color: #f7f9fb !important; color: #1e293b; }
        .sidebar { background: var(--slate-50) !important; border-right: 1px solid var(--slate-200); }
        .sidebar-mobile { position:fixed; top:0; left:0; bottom:0; width:260px; z-index:999 !important; transform:translateX(-100%); transition:transform 0.3s cubic-bezier(0.4,0,0.2,1); display:flex !important; flex-direction:column; box-shadow:20px 0 50px rgba(0,0,0,0.1); background: var(--slate-50) !important; }
        .sidebar-mobile.open { transform:translateX(0); }
        .sidebar-overlay { position:fixed; inset:0; background:rgba(15, 23, 42, 0.3); z-index:998 !important; opacity:0; pointer-events:none; transition:opacity 0.3s ease; }
        .sidebar-overlay.open { opacity:1; pointer-events:all; }
        .nav-item { transition: all 0.2s; font-weight: 500; font-size: 14px; color: var(--slate-600); }
        .nav-item:hover { background-color: var(--slate-100); color: var(--primary); }
        .nav-item.active { background-color: #ffffff; color: var(--primary); border-right: 3px solid var(--primary); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .header-blur { background:#ffffff !important; border-bottom:1px solid var(--slate-200); }
        .material-symbols-outlined { font-size: 20px; }
        .card-white { background: #ffffff; border-radius: 32px; border: 1px solid var(--slate-200); box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); transition: all 0.3s ease; }
        .btn-save { background: linear-gradient(135deg, #1f108e 0%, #312e81 100%); color: white; box-shadow: 0 10px 15px -3px rgba(31, 16, 142, 0.2); transition: all 0.3s ease; }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 20px 25px -5px rgba(31, 16, 142, 0.3); }
        
        /* Interactive Input Styling */
        .spec-input { text-align: right; font-weight: 800; color: var(--slate-900); border-radius: 8px; border: 1px solid transparent; background: transparent; outline: none; width: 100%; font-size: 14px; padding: 4px 8px; transition: all 0.2s ease; }
        .spec-input:hover { background: var(--slate-50); border-color: var(--slate-200); }
        .spec-input:focus { background: white; border-color: var(--primary); color: var(--primary); box-shadow: 0 0 0 4px rgba(31, 16, 142, 0.05); }
        
        .hero-input { background: transparent; border: none; border-bottom: 2px solid transparent; outline: none; transition: all 0.3s ease; }
        .hero-input:hover { border-bottom-color: var(--slate-200); }
        .hero-input:focus { border-bottom-color: var(--primary); color: var(--primary); }

        .main-scroll::-webkit-scrollbar { width: 5px; }
        .main-scroll::-webkit-scrollbar-track { background: transparent; }
        .main-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-[#1d1d1f]">

    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Area -->
    <main class="flex-1 flex flex-col h-full bg-[#f7f9fb] relative z-10 w-full overflow-hidden">
        <header class="h-16 header-blur flex items-center justify-between px-6 lg:px-8 shrink-0 z-30 sticky top-0 bg-white">
            <div class="flex items-center gap-4 flex-1">
                <button id="mobileMenuBtn" class="md:hidden text-slate-600 hover:text-indigo-700 focus:outline-none p-2 rounded-xl hover:bg-slate-100 transition-colors">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <div class="hidden md:flex items-center gap-2 text-sm text-slate-500">
                    <a href="myapps.php" class="hover:text-indigo-600 font-medium transition-colors">My Apps</a>
                    <span class="material-symbols-outlined text-slate-300">chevron_right</span>
                    <span class="font-bold text-slate-900 uppercase tracking-widest text-[11px]"><?php echo htmlspecialchars($app['name']); ?></span>
                    <span class="material-symbols-outlined text-slate-300">chevron_right</span>
                    <span class="font-bold text-indigo-600 uppercase tracking-widest text-[11px]">Edit Mode</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <?php if (isLoggedIn()): ?>
                    <div class="relative group">
                        <button class="flex items-center gap-3 p-1 pr-4 bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-indigo-200 transition-all group">
                            <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-[12px] font-bold shadow-indigo-100 shadow-lg overflow-hidden shrink-0">
                                <?php if (!empty($_SESSION['profile_pic'])): ?>
                                    <img src="../<?php echo htmlspecialchars($_SESSION['profile_pic']); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <div class="text-left hidden sm:block">
                                <p class="text-[11px] font-bold text-slate-700 group-hover:text-indigo-600 transition-colors leading-none">
                                    <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                                </p>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter mt-1">Member</p>
                            </div>
                            <span class="material-symbols-outlined text-slate-300 text-[18px]">expand_more</span>
                        </button>

                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-64 bg-white rounded-[24px] shadow-[0_20px_50px_rgba(0,0,0,0.15)] border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform scale-95 group-hover:scale-100 origin-top-right z-50 overflow-hidden">
                            <div class="p-5 border-b border-slate-50 bg-slate-50/50">
                                <p class="text-sm font-black text-slate-900 leading-none"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></p>
                                <p class="text-[10px] text-indigo-600 font-black uppercase tracking-widest mt-2"><?php echo isAdmin() ? 'Super Admin' : 'Premium Member'; ?></p>
                            </div>
                            <div class="p-2 space-y-1">
                                <?php if (isAdmin()): ?>
                                    <a href="../admin/dashboard.php" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-indigo-700 bg-indigo-50/50 hover:bg-indigo-50 rounded-xl transition-all">
                                        <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span> Admin Panel
                                    </a>
                                    <div class="h-px bg-slate-50 my-1"></div>
                                <?php endif; ?>
                                <a href="profile.php" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                                    <span class="material-symbols-outlined text-[20px]">person</span> Account Details
                                </a>
                                <a href="submit.php" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                                    <span class="material-symbols-outlined text-[20px]">publish</span> Submit New App
                                </a>
                                <a href="myapps.php" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                                    <span class="material-symbols-outlined text-[20px]">cloud_upload</span> My Uploads
                                </a>
                                <a href="favorites.php" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                                    <span class="material-symbols-outlined text-[20px]">favorite</span> My Favorites
                                </a>
                                <div class="h-px bg-slate-50 my-1"></div>
                                <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 rounded-xl transition-all">
                                    <span class="material-symbols-outlined text-[20px]">logout</span> Sign Out
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </header>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto px-4 md:px-8 lg:px-10 pb-24 md:pb-12 main-scroll">
        <div class="max-w-[1200px] mx-auto mt-6">
            
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-3xl mb-8 text-sm flex items-center gap-4 shadow-sm animate-in fade-in duration-300">
                    <span class="material-symbols-outlined text-red-500">error</span>
                    <span class="font-bold"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-3xl mb-8 text-sm flex items-center gap-4 shadow-sm animate-in fade-in duration-300">
                    <span class="material-symbols-outlined text-green-500">check_circle</span>
                    <span class="font-bold"><?php echo $success; ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <!-- App Hero Card (Mirrored from app.php) -->
                <div class="card-white p-8 md:p-12 mb-8 bg-white overflow-hidden border-indigo-100 shadow-xl shadow-indigo-100/20">
                    <div class="flex flex-col md:flex-row gap-10 items-center md:items-start text-center md:text-left">
                        <!-- Logo Upload -->
                        <div class="w-40 h-40 md:w-48 md:h-48 rounded-[48px] bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden shadow-sm flex-shrink-0 relative group hover:border-indigo-600 hover:bg-indigo-50 transition-all cursor-pointer">
                            <?php 
                            $logo_preview = $app['logo'];
                            if (strpos($logo_preview, 'http') !== 0) {
                                $logo_preview = ltrim(str_replace('../', '', $logo_preview), '/');
                                $logo_preview = $base_url . $logo_preview;
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($logo_preview); ?>" id="logo-preview-img" onerror="this.src='<?php echo $base_url; ?>assets/images/logo.png';" alt="Logo" class="w-full h-full object-cover opacity-60 group-hover:opacity-30 transition-opacity">
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-500 group-hover:text-indigo-600 transition-colors">
                                <span class="material-symbols-outlined text-4xl">add_photo_alternate</span>
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] mt-2">New Logo</span>
                            </div>
                            <input type="file" name="logo" accept="image/*" onchange="previewLogo(this)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        </div>

                        <div class="flex-1 w-full">
                            <div class="flex flex-wrap justify-center md:justify-start items-center gap-4 mb-6">
                                <span class="px-4 py-1.5 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-widest rounded-full border border-indigo-100 shadow-sm">EDITOR ACTIVE</span>
                                <span class="flex items-center gap-1.5 text-[10px] font-black text-green-600 uppercase tracking-widest">
                                    <span class="material-symbols-outlined text-[16px]">verified_user</span> Verified Safe
                                </span>
                                <span class="flex items-center gap-1.5 text-[10px] font-black text-amber-500 uppercase tracking-widest">
                                    <span class="material-symbols-outlined text-[16px]">star</span> New submission
                                </span>
                            </div>
                            
                            <div class="mb-8">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Application Title</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($app['name']); ?>" required class="hero-input text-4xl md:text-6xl font-black text-slate-900 w-full tracking-tight" placeholder="App Name">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Primary Category</label>
                                    <div class="relative">
                                        <select name="category_desktop" class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-slate-700 focus:ring-4 focus:ring-indigo-600/10 focus:border-indigo-600 outline-none transition-all appearance-none text-sm shadow-sm">
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php if($app['category'] === $cat['name']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">unfold_more</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Direct Download URL</label>
                                    <div class="relative">
                                        <input type="url" name="apk_link" value="<?php echo htmlspecialchars($app['apk_link']); ?>" required class="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl font-bold text-indigo-600 focus:ring-4 focus:ring-indigo-600/10 focus:border-indigo-600 outline-none transition-all text-sm shadow-sm" placeholder="https://...">
                                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">link</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column: Description -->
                    <div class="lg:col-span-2 space-y-8">
                        <div class="card-white p-8 md:p-12 shadow-xl shadow-slate-100/50">
                            <h3 class="text-2xl font-black text-slate-900 mb-10 flex items-center gap-4 tracking-tight">
                                <span class="w-2.5 h-10 bg-indigo-600 rounded-full shadow-lg shadow-indigo-200"></span>
                                Product Description
                            </h3>
                            <div class="relative group">
                                <textarea name="description_desktop" rows="18" class="w-full p-10 bg-slate-50 border border-slate-100 rounded-[48px] text-slate-700 leading-relaxed text-lg outline-none focus:ring-4 focus:ring-indigo-600/10 focus:border-indigo-600 transition-all resize-none shadow-inner" placeholder="Detailed app information..."><?php echo htmlspecialchars($app['description']); ?></textarea>
                                <div class="absolute top-6 right-6 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <span class="px-3 py-1.5 bg-white/80 backdrop-blur rounded-full text-[10px] font-black text-slate-400 uppercase tracking-widest border border-slate-100 shadow-sm">Rich Text Mode</span>
                                </div>
                            </div>
                            <div class="mt-8 p-6 bg-indigo-50/50 rounded-3xl border border-indigo-100 flex items-start gap-4">
                                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <span class="material-symbols-outlined text-indigo-600">lightbulb</span>
                                </div>
                                <div>
                                    <p class="text-[14px] text-indigo-950 font-bold mb-1 uppercase tracking-tight">Formatting Tip</p>
                                    <p class="text-[13px] text-indigo-700 leading-relaxed opacity-80">Use standard HTML tags like &lt;b&gt;, &lt;p&gt;, and &lt;ul&gt; to create structured, readable descriptions that convert more downloads.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Specs & Actions -->
                    <div class="lg:col-span-1 space-y-8">
                        <!-- Specifications Card -->
                        <div class="card-white p-10 bg-white shadow-2xl shadow-indigo-100/10 border-indigo-50">
                            <h3 class="text-xl font-black text-slate-900 mb-10 border-b border-slate-100 pb-5 tracking-tight flex items-center justify-between">
                                Specifications
                                <span class="material-symbols-outlined text-slate-300">settings_applications</span>
                            </h3>
                            <div class="space-y-6">
                                <div class="flex justify-between items-center gap-4 py-2 border-b border-slate-50 last:border-0 group">
                                    <span class="text-[11px] text-slate-400 font-black uppercase tracking-[0.2em] shrink-0 group-hover:text-indigo-600 transition-colors">Version</span>
                                    <div class="flex-1 max-w-[160px]">
                                        <input type="text" name="language_desktop" value="<?php echo htmlspecialchars($app['language'] ?? 'Latest'); ?>" class="spec-input" placeholder="e.g. 1.0.4">
                                    </div>
                                </div>
                                <div class="flex justify-between items-center gap-4 py-2 border-b border-slate-50 last:border-0 group">
                                    <span class="text-[11px] text-slate-400 font-black uppercase tracking-[0.2em] shrink-0 group-hover:text-indigo-600 transition-colors">File Size</span>
                                    <div class="flex-1 max-w-[160px]">
                                        <input type="text" name="file_size_desktop" value="<?php echo htmlspecialchars($app['file_size']); ?>" class="spec-input" placeholder="e.g. 45 MB">
                                    </div>
                                </div>
                                <div class="flex justify-between items-center gap-4 py-2 border-b border-slate-50 last:border-0 group">
                                    <span class="text-[11px] text-slate-400 font-black uppercase tracking-[0.2em] shrink-0 group-hover:text-indigo-600 transition-colors">OS Support</span>
                                    <div class="flex-1 max-w-[160px]">
                                        <input type="text" name="os_compatible_desktop" value="<?php echo htmlspecialchars($app['os_compatible']); ?>" class="spec-input" placeholder="e.g. Win / Android">
                                    </div>
                                </div>
                                <div class="flex justify-between items-center gap-4 py-2 border-b border-slate-50 last:border-0 group">
                                    <span class="text-[11px] text-slate-400 font-black uppercase tracking-[0.2em] shrink-0 group-hover:text-indigo-600 transition-colors">License</span>
                                    <div class="flex-1 max-w-[160px]">
                                        <input type="text" name="license_type_desktop" value="<?php echo htmlspecialchars($app['license_type']); ?>" class="spec-input !text-indigo-600 !font-black" placeholder="e.g. Free / Pro">
                                    </div>
                                </div>
                                <div class="flex justify-between items-center gap-4 py-2 last:border-0 group">
                                    <span class="text-[11px] text-slate-400 font-black uppercase tracking-[0.2em] shrink-0 group-hover:text-indigo-600 transition-colors">Developer</span>
                                    <div class="flex-1 max-w-[160px]">
                                        <input type="text" name="developer_desktop" value="<?php echo htmlspecialchars($app['developer']); ?>" class="spec-input" placeholder="Studio Name">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Card -->
                        <div class="bg-indigo-950 rounded-[48px] p-10 text-white shadow-2xl shadow-indigo-200 relative overflow-hidden group">
                            <div class="absolute -top-16 -right-16 w-40 h-40 bg-indigo-500/20 rounded-full blur-3xl group-hover:bg-indigo-500/40 transition-all duration-700"></div>
                            <div class="absolute -bottom-16 -left-16 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition-all duration-700"></div>
                            
                            <h4 class="font-black text-xl mb-3 flex items-center gap-3 relative z-10">
                                <span class="material-symbols-outlined text-indigo-400">publish</span>
                                Sync Updates
                            </h4>
                            <p class="text-sm text-indigo-300/80 leading-relaxed mb-10 relative z-10 font-medium">Your changes will be reviewed by our moderation team. Your app remains public during this process.</p>
                            
                            <button type="submit" class="w-full py-5 btn-save rounded-2xl font-black text-[16px] flex items-center justify-center gap-3 shadow-2xl relative z-10 active:scale-[0.98]">
                                <span class="material-symbols-outlined">auto_fix_high</span>
                                Save & Publish
                            </button>
                        </div>

                        <a href="myapps.php" class="flex w-full bg-white text-slate-500 font-black py-5 rounded-3xl border border-slate-200 hover:bg-slate-50 hover:text-slate-900 transition-all items-center justify-center text-[13px] shadow-sm uppercase tracking-widest">
                            Discard Changes
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    function previewLogo(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('logo-preview-img').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        const btn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (btn && sidebar && overlay) {
            btn.addEventListener('click', () => {
                sidebar.classList.add('open');
                overlay.classList.add('open');
            });
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
            });
        }
    });
</script>
</body>
</html>
