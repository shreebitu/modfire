<?php
require_once '../includes/init.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Auto-migration for features and older_versions columns
try {
    $pdo->query("SELECT features, older_versions FROM apps LIMIT 1");
} catch (PDOException $e) {
    // Check features separately
    try { $pdo->query("SELECT features FROM apps LIMIT 1"); } 
    catch (PDOException $ex) { $pdo->exec("ALTER TABLE apps ADD COLUMN features TEXT AFTER screenshots"); }
    
    // Add older_versions
    try { $pdo->query("SELECT older_versions FROM apps LIMIT 1"); } 
    catch (PDOException $ex) { $pdo->exec("ALTER TABLE apps ADD COLUMN older_versions TEXT AFTER features"); }
}

$stmt = $pdo->prepare("SELECT apps.*, users.username, categories.name as category_name FROM apps 
                        JOIN users ON apps.user_id = users.id 
                        LEFT JOIN categories ON apps.category = categories.name
                        WHERE apps.id = ?");
$stmt->execute([$id]);
$app = $stmt->fetch();

if (!$app) {
    die("App not found.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');
    $name = sanitizeInput($_POST['name']);
    $slug = sanitizeInput($_POST['slug']);
    $category = sanitizeInput($_POST['category']);
    $version = sanitizeInput($_POST['version']);
    $apk_link = sanitizeInput($_POST['apk_link']);
    $status = sanitizeInput($_POST['status']);
    $description = $_POST['description']; // Keeping HTML from TinyMCE
    
    $file_size = sanitizeInput($_POST['file_size']);
    $os_compatible = sanitizeInput($_POST['os_compatible']);
    $language = sanitizeInput($_POST['language']);
    $license_type = sanitizeInput($_POST['license_type']);
    $developer = sanitizeInput($_POST['developer']);
    $screenshots_raw = $_POST['screenshots'] ?? [];
    if (is_array($screenshots_raw)) {
        $screenshots_arr = array_filter(array_map('trim', $screenshots_raw));
        $screenshots = implode(',', array_map('sanitizeInput', $screenshots_arr));
    } else {
        $screenshots = sanitizeInput($screenshots_raw);
    }
    $related_apps = sanitizeInput($_POST['related_apps'] ?? '');
    
    $seo_title = sanitizeInput($_POST['seo_title']);
    $meta_description = sanitizeInput($_POST['meta_description']);
    $meta_keywords = sanitizeInput($_POST['meta_keywords']);
    
    $is_top = isset($_POST['is_top']) ? 1 : 0;
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    $is_recent = isset($_POST['is_recent']) ? 1 : 0;

    // Handle Features JSON
    $features_raw = $_POST['features_data'] ?? [];
    $features_arr = [];
    if (is_array($features_raw)) {
        foreach ($features_raw as $feat) {
            if (!empty($feat['title'])) {
                $features_arr[] = [
                    'icon' => sanitizeInput($feat['icon'] ?? 'star'),
                    'title' => sanitizeInput($feat['title'] ?? ''),
                    'desc' => sanitizeInput($feat['desc'] ?? ''),
                    'color' => sanitizeInput($feat['color'] ?? 'indigo')
                ];
            }
        }
    }
    $features_json = json_encode($features_arr);

    // Handle Older Versions JSON
    $versions_raw = $_POST['versions_data'] ?? [];
    $versions_arr = [];
    if (is_array($versions_raw)) {
        foreach ($versions_raw as $v) {
            if (!empty($v['version_name']) && !empty($v['download_url'])) {
                $versions_arr[] = [
                    'version_name' => sanitizeInput($v['version_name']),
                    'download_url' => sanitizeInput($v['download_url']),
                    'release_date' => sanitizeInput($v['release_date'] ?? '')
                ];
            }
        }
    }
    $versions_json = json_encode($versions_arr);

    // Handle slug auto-generation if empty
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug = $slug . '-download';
    }

    // Check for duplicate slug
    $checkSlug = $pdo->prepare("SELECT id FROM apps WHERE slug = ? AND id != ?");
    $checkSlug->execute([$slug, $id]);
    if ($checkSlug->fetch()) {
        $slug = $slug . '-' . time();
    }

    $logo_path = $app['logo'];
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $file_ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        if (!in_array($file_ext, $allowed_exts)) {
            $error = "Invalid logo file type. Allowed: " . implode(', ', $allowed_exts);
        } else {
            $file_name = 'logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $file_name)) {
                $logo_path = 'uploads/' . $file_name;
            } else {
                $error = "Failed to upload new logo.";
            }
        }
    }

    if (empty($error)) {
        $stmt = $pdo->prepare("UPDATE apps SET name = ?, slug = ?, category = ?, version = ?, apk_link = ?, description = ?, logo = ?, screenshots = ?, features = ?, older_versions = ?, related_apps = ?, file_size = ?, os_compatible = ?, language = ?, license_type = ?, developer = ?, status = ?, seo_title = ?, meta_description = ?, meta_keywords = ?, is_top = ?, is_popular = ?, is_recent = ? WHERE id = ?");
        if ($stmt->execute([$name, $slug, $category, $version, $apk_link, $description, $logo_path, $screenshots, $features_json, $versions_json, $related_apps, $file_size, $os_compatible, $language, $license_type, $developer, $status, $seo_title, $meta_description, $meta_keywords, $is_top, $is_popular, $is_recent, $id])) {
            logActivity('admin_action', "Modified application ID: $id ($name)");
            $success = "Metadata updated successfully!";
            // Refresh
            $stmt = $pdo->prepare("SELECT apps.*, users.username, categories.name as category_name FROM apps 
                                    JOIN users ON apps.user_id = users.id 
                                    LEFT JOIN categories ON apps.category = categories.name
                                    WHERE apps.id = ?");
            $stmt->execute([$id]);
            $app = $stmt->fetch();
        } else {
            $error = "Database update failed.";
        }
    }
}

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

$reportsCount = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();

// Fetch all approved apps for the "Related Apps" picker
$stmtAllApps = $pdo->query("SELECT id, name, logo, slug FROM apps WHERE status = 'approved' AND id != $id ORDER BY name ASC");
$allAppsForPicker = $stmtAllApps->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Content - Super Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root { --primary: #1f108e; --slate-50: #f8fafc; --slate-100: #f1f5f9; --slate-200: #e2e8f0; }
        body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; color: #1e293b; }
        .sidebar { background: var(--slate-50); border-right: 1px solid var(--slate-200); }
        .nav-item { transition: all 0.2s; font-weight: 500; font-size: 14px; color: #64748b; }
        .nav-item:hover { background-color: var(--slate-100); color: var(--primary); }
        .nav-item.active { background-color: #ffffff; color: var(--primary); border-right: 3px solid var(--primary); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .card-white { background: #ffffff; border-radius: 24px; border: 1px solid var(--slate-200); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .input-box { width: 100%; padding: 0.875rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; font-size: 0.875rem; outline: none; transition: all 0.2s; font-weight: 500; color: #1e293b; }
        .input-box:focus { border-color: #1f108e; box-shadow: 0 0 0 4px rgba(31, 16, 142, 0.05); background: white; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-[#1d1d1f]">

<?php include 'components/sidebar.php'; ?>
<?php include 'components/mobile_sidebar.php'; ?>

<main class="flex-1 flex flex-col h-full bg-[#f8fafc] relative z-10 w-full overflow-hidden">
    <?php include 'components/header.php'; ?>

    <div class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-10 main-scroll">
        <div class="max-w-[1200px] mx-auto">
        <header class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <a href="apps.php" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Modify Application</h2>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mt-0.5">Content ID: #<?php echo $id; ?></p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border <?php echo $app['status'] === 'approved' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-amber-50 text-amber-700 border-amber-100'; ?>">
                    Current Status: <?php echo $app['status']; ?>
                </span>
            </div>
        </header>

        <?php if ($success): ?>
            <div class="bg-indigo-900 text-white px-6 py-4 rounded-3xl mb-8 flex items-center gap-4 shadow-xl shadow-indigo-100">
                <span class="material-symbols-outlined text-sm">check_circle</span>
                <span class="text-xs font-black uppercase tracking-widest"><?php echo $success; ?></span>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-600 text-white px-6 py-4 rounded-3xl mb-8 flex items-center gap-4">
                <span class="material-symbols-outlined text-sm">warning</span>
                <span class="text-xs font-black uppercase tracking-widest"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Sidebar Info & Status -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="card-white p-6 flex flex-col items-center text-center">
                        <div class="w-24 h-24 rounded-3xl bg-slate-50 border border-slate-100 p-2 mb-4 shadow-inner relative group">
                            <?php 
                            $logo_preview = $app['logo'];
                            if (strpos($logo_preview, 'http') !== 0) {
                                $logo_preview = ltrim(str_replace('../', '', $logo_preview), '/');
                                $logo_preview = $base_url . $logo_preview;
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($logo_preview); ?>" id="logo-preview-img" class="w-full h-full object-contain rounded-2xl">
                            <label class="absolute inset-0 bg-black/40 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer">
                                <span class="material-symbols-outlined text-white">upload</span>
                                <input type="file" name="logo" accept="image/*" class="hidden" onchange="previewLogo(this)">
                            </label>
                        </div>
                        <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">Uploaded By</p>
                        <p class="text-sm font-bold text-slate-900 mb-6">@<?php echo htmlspecialchars($app['username']); ?></p>
                        
                        <div class="w-full space-y-4 text-left">
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Publishing Status</label>
                                <select name="status" class="input-box">
                                    <option value="pending" <?php echo $app['status'] === 'pending' ? 'selected' : ''; ?>>Pending Approval</option>
                                    <option value="approved" <?php echo $app['status'] === 'approved' ? 'selected' : ''; ?>>Approved / Live</option>
                                    <option value="rejected" <?php echo $app['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected / Hidden</option>
                                </select>
                            </div>

                            <div class="pt-4 space-y-3">
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Homepage Sections</label>
                                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                                    <input type="checkbox" name="is_top" id="is_top" <?php echo $app['is_top'] ? 'checked' : ''; ?> class="w-4 h-4 accent-indigo-600">
                                    <label for="is_top" class="text-xs font-bold text-slate-700">Top Download</label>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                                    <input type="checkbox" name="is_popular" id="is_popular" <?php echo $app['is_popular'] ? 'checked' : ''; ?> class="w-4 h-4 accent-indigo-600">
                                    <label for="is_popular" class="text-xs font-bold text-slate-700">Popular App</label>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                                    <input type="checkbox" name="is_recent" id="is_recent" <?php echo $app['is_recent'] ? 'checked' : ''; ?> class="w-4 h-4 accent-indigo-600">
                                    <label for="is_recent" class="text-xs font-bold text-slate-700">Recent Entry</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-white p-6">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Uploader Notes</h3>
                        <p class="text-[11px] text-slate-500 font-medium italic">"Submitted via user dashboard. Automatic virus check passed."</p>
                    </div>
                </div>

                <!-- Main Content Fields -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="card-white p-8">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-8 flex items-center gap-3">
                            <span class="material-symbols-outlined text-indigo-600">edit_square</span>
                            Core Metadata
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Application Title</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($app['name']); ?>" required class="input-box">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Custom Slug (URL)</label>
                                <div class="flex gap-2">
                                    <input type="text" name="slug" id="slug-input" value="<?php echo htmlspecialchars($app['slug']); ?>" placeholder="whatsapp-apk-download" class="input-box">
                                    <button type="button" onclick="generateSlug()" class="px-3 bg-slate-100 border border-slate-200 rounded-xl hover:bg-slate-200 transition-all">
                                        <span class="material-symbols-outlined text-sm">autorenew</span>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Primary Category</label>
                                <select name="category" class="input-box">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php if($app['category'] == $cat['name']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Version</label>
                                <input type="text" name="version" value="<?php echo htmlspecialchars($app['version']); ?>" placeholder="v1.2.3" class="input-box">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">File Size</label>
                                <input type="text" name="file_size" value="<?php echo htmlspecialchars($app['file_size']); ?>" class="input-box">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Download / Resource URL</label>
                                <input type="text" name="apk_link" value="<?php echo htmlspecialchars($app['apk_link']); ?>" required class="input-box font-mono text-xs">
                            </div>
                        </div>
                    </div>

                    <div class="card-white p-8">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-8 flex items-center gap-3">
                            <span class="material-symbols-outlined text-indigo-600">clinical_notes</span>
                            Technical Specs
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">OS Compatibility</label>
                                <input type="text" name="os_compatible" value="<?php echo htmlspecialchars($app['os_compatible']); ?>" class="input-box">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Language</label>
                                <input type="text" name="language" value="<?php echo htmlspecialchars($app['language']); ?>" class="input-box">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">License Type</label>
                                <input type="text" name="license_type" value="<?php echo htmlspecialchars($app['license_type']); ?>" class="input-box">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Developer Entity</label>
                                <input type="text" name="developer" value="<?php echo htmlspecialchars($app['developer']); ?>" class="input-box">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">App Screenshots (Max 6 URLs)</label>
                                <div id="screenshot-container" class="space-y-3">
                                    <?php 
                                    $s_list = array_filter(explode(',', $app['screenshots'] ?? ''));
                                    if (empty($s_list)) $s_list = [''];
                                    foreach ($s_list as $index => $s_url): 
                                    ?>
                                    <div class="flex gap-2 screenshot-row">
                                        <input type="url" name="screenshots[]" value="<?php echo htmlspecialchars(trim($s_url)); ?>" placeholder="https://example.com/screenshot.jpg" class="input-box text-xs">
                                        <?php if ($index > 0): ?>
                                        <button type="button" onclick="this.parentElement.remove()" class="px-3 bg-red-50 text-red-500 rounded-xl hover:bg-red-100 transition-all flex items-center justify-center">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" onclick="addScreenshot()" class="mt-3 flex items-center gap-2 text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:text-indigo-800 transition-all">
                                    <span class="material-symbols-outlined text-sm">add_circle</span>
                                    Add More URL
                                </button>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Related Applications</label>
                                
                                <!-- Visual Tag Container -->
                                <div id="related-tags" class="flex flex-wrap gap-2 mb-4">
                                    <!-- Tags will be injected here by JS -->
                                </div>

                                <!-- Hidden input to store the actual IDs -->
                                <input type="hidden" name="related_apps" id="related-apps-input" value="<?php echo htmlspecialchars($app['related_apps'] ?? ''); ?>">

                                <!-- Searchable Selector -->
                                <div class="relative group">
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-600 transition-colors">
                                        <span class="material-symbols-outlined text-[20px]">search</span>
                                    </div>
                                    <input type="text" id="app-search" placeholder="Search apps by name to link them..." class="input-box pl-12">
                                    
                                    <!-- Search Results Dropdown -->
                                    <div id="search-results" class="absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl border border-slate-100 shadow-2xl z-50 max-h-60 overflow-y-auto hidden main-scroll">
                                        <?php foreach ($allAppsForPicker as $pickerApp): 
                                            $p_logo = $pickerApp['logo'];
                                            if (strpos($p_logo, 'http') !== 0) {
                                                $p_logo = ltrim(str_replace('../', '', $p_logo), '/');
                                                $p_logo = $base_url . $p_logo;
                                            }
                                        ?>
                                        <div class="picker-item flex items-center gap-3 px-4 py-3 hover:bg-indigo-50 cursor-pointer transition-colors border-b border-slate-50 last:border-0" 
                                             data-id="<?php echo $pickerApp['id']; ?>" 
                                             data-name="<?php echo htmlspecialchars($pickerApp['name']); ?>"
                                             data-logo="<?php echo htmlspecialchars($p_logo); ?>">
                                            <div class="w-8 h-8 rounded-lg bg-slate-50 overflow-hidden shrink-0">
                                                <img src="<?php echo htmlspecialchars($p_logo); ?>" class="w-full h-full object-cover">
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-xs font-bold text-slate-900"><?php echo htmlspecialchars($pickerApp['name']); ?></p>
                                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">ID: #<?php echo $pickerApp['id']; ?></p>
                                            </div>
                                            <span class="material-symbols-outlined text-slate-300 group-hover:text-indigo-600 text-[18px]">add_circle</span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <p class="mt-3 text-[10px] text-slate-400 font-medium italic">Click an app from the search results to add it as a related application.</p>
                            </div>
                        </div>
                    </div>

                    <!-- App Features Section -->
                    <div class="card-white p-8">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-8 flex items-center gap-3">
                            <span class="material-symbols-outlined text-indigo-600">featured_play_list</span>
                            App Feature Cards
                        </h3>
                        <div id="features-container" class="space-y-6">
                            <?php 
                            $f_list = json_decode($app['features'] ?? '[]', true);
                            if (empty($f_list)) $f_list = []; // Start empty if none
                            foreach ($f_list as $f_idx => $f_data): 
                            ?>
                            <div class="feature-row bg-slate-50 p-6 rounded-3xl border border-slate-100 relative group">
                                <button type="button" onclick="this.parentElement.remove()" class="absolute -top-3 -right-3 w-8 h-8 bg-white text-red-500 border border-red-100 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span class="material-symbols-outlined text-sm">close</span>
                                </button>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="md:col-span-1">
                                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Select Icon</label>
                                        <select name="features_data[<?php echo $f_idx; ?>][icon]" class="input-box text-xs">
                                            <option value="verified" <?php echo ($f_data['icon'] ?? '') == 'verified' ? 'selected' : ''; ?>>Verified / Check</option>
                                            <option value="security" <?php echo ($f_data['icon'] ?? '') == 'security' ? 'selected' : ''; ?>>Privacy / Shield</option>
                                            <option value="bolt" <?php echo ($f_data['icon'] ?? '') == 'bolt' ? 'selected' : ''; ?>>Fast / Lightning</option>
                                            <option value="display_settings" <?php echo ($f_data['icon'] ?? '') == 'display_settings' ? 'selected' : ''; ?>>DPI / Display</option>
                                            <option value="groups" <?php echo ($f_data['icon'] ?? '') == 'groups' ? 'selected' : ''; ?>>Dual / Accounts</option>
                                            <option value="rocket_launch" <?php echo ($f_data['icon'] ?? '') == 'rocket_launch' ? 'selected' : ''; ?>>Performance / Rocket</option>
                                            <option value="touch_app" <?php echo ($f_data['icon'] ?? '') == 'touch_app' ? 'selected' : ''; ?>>One-Click / Click</option>
                                            <option value="settings_suggest" <?php echo ($f_data['icon'] ?? '') == 'settings_suggest' ? 'selected' : ''; ?>>Custom / Settings</option>
                                            <option value="lock" <?php echo ($f_data['icon'] ?? '') == 'lock' ? 'selected' : ''; ?>>Secure / Lock</option>
                                            <option value="speed" <?php echo ($f_data['icon'] ?? '') == 'speed' ? 'selected' : ''; ?>>Speed / Meter</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-1">
                                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Accent Color</label>
                                        <select name="features_data[<?php echo $f_idx; ?>][color]" class="input-box text-xs">
                                            <option value="indigo" <?php echo $f_data['color'] == 'indigo' ? 'selected' : ''; ?>>Blue / Indigo</option>
                                            <option value="emerald" <?php echo $f_data['color'] == 'emerald' ? 'selected' : ''; ?>>Green / Emerald</option>
                                            <option value="purple" <?php echo $f_data['color'] == 'purple' ? 'selected' : ''; ?>>Purple / Violet</option>
                                            <option value="amber" <?php echo $f_data['color'] == 'amber' ? 'selected' : ''; ?>>Orange / Amber</option>
                                            <option value="rose" <?php echo $f_data['color'] == 'rose' ? 'selected' : ''; ?>>Red / Rose</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-1">
                                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Feature Title</label>
                                        <input type="text" name="features_data[<?php echo $f_idx; ?>][title]" value="<?php echo htmlspecialchars($f_data['title']); ?>" placeholder="High Performance" class="input-box text-xs">
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Description</label>
                                        <textarea name="features_data[<?php echo $f_idx; ?>][desc]" rows="2" class="input-box text-xs" placeholder="Describe the feature..."><?php echo htmlspecialchars($f_data['desc']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" onclick="addFeature()" class="mt-6 w-full py-4 border-2 border-dashed border-slate-200 rounded-2xl text-slate-400 font-bold text-xs uppercase tracking-widest hover:border-indigo-300 hover:text-indigo-600 hover:bg-indigo-50/30 transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-sm">add_circle</span>
                            Add New Feature Card
                        </button>
                    </div>

                    <!-- Older Versions Section -->
                    <div class="card-white p-8">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-8 flex items-center gap-3">
                            <span class="material-symbols-outlined text-indigo-600">history</span>
                            Older Versions
                        </h3>
                        <div id="versions-container" class="space-y-4">
                            <?php 
                            $v_list = json_decode($app['older_versions'] ?? '[]', true);
                            if (empty($v_list)) $v_list = [];
                            foreach ($v_list as $v_idx => $v_data): 
                            ?>
                            <div class="version-row flex flex-col md:flex-row gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100 relative group">
                                <button type="button" onclick="this.parentElement.remove()" class="absolute -top-2 -right-2 w-6 h-6 bg-white text-red-500 border border-red-100 rounded-full shadow-md opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[14px]">close</span>
                                </button>
                                <div class="flex-1">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Version Name</label>
                                    <input type="text" name="versions_data[<?php echo $v_idx; ?>][version_name]" value="<?php echo htmlspecialchars($v_data['version_name']); ?>" placeholder="v1.0.2" class="input-box text-xs py-2">
                                </div>
                                <div class="flex-[2]">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Download URL</label>
                                    <input type="url" name="versions_data[<?php echo $v_idx; ?>][download_url]" value="<?php echo htmlspecialchars($v_data['download_url']); ?>" placeholder="https://..." class="input-box text-xs py-2">
                                </div>
                                <div class="flex-1">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Release Date</label>
                                    <input type="text" name="versions_data[<?php echo $v_idx; ?>][release_date]" value="<?php echo htmlspecialchars($v_data['release_date'] ?? ''); ?>" placeholder="Oct 2023" class="input-box text-xs py-2">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" onclick="addVersion()" class="mt-4 flex items-center gap-2 text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:text-indigo-800 transition-all">
                            <span class="material-symbols-outlined text-sm">add_circle</span>
                            Add Version Link
                        </button>
                    </div>

                    <div class="card-white p-8">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-8 flex items-center gap-3">
                            <span class="material-symbols-outlined text-indigo-600">search</span>
                            SEO Optimization
                        </h3>
                        <div class="space-y-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">SEO Title (Google Title)</label>
                                <input type="text" name="seo_title" value="<?php echo htmlspecialchars($app['seo_title']); ?>" placeholder="Download WhatsApp APK - Latest Version 2024" class="input-box">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Meta Description</label>
                                <textarea name="meta_description" rows="3" class="input-box" placeholder="Brief summary for search engines..."><?php echo htmlspecialchars($app['meta_description']); ?></textarea>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Meta Keywords</label>
                                <input type="text" name="meta_keywords" value="<?php echo htmlspecialchars($app['meta_keywords']); ?>" placeholder="whatsapp, apk, download, android" class="input-box">
                            </div>
                        </div>
                    </div>

                    <div class="card-white p-8">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6">Long Description</h3>
                        <textarea name="description" rows="12" class="input-box leading-relaxed" placeholder="Detailed app information..."><?php echo htmlspecialchars($app['description']); ?></textarea>
                    </div>

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

                    function generateSlug() {
                        const name = document.querySelector('input[name="name"]').value;
                        const slug = name.toLowerCase()
                            .replace(/[^a-z0-9]+/g, '-')
                            .replace(/(^-|-$)/g, '') + '-download';
                        document.getElementById('slug-input').value = slug;
                    }

                    function addScreenshot() {
                        const container = document.getElementById('screenshot-container');
                        const rows = container.querySelectorAll('.screenshot-row');
                        if (rows.length >= 6) {
                            alert('Maximum 6 screenshots allowed.');
                            return;
                        }
                        
                        const newRow = document.createElement('div');
                        newRow.className = 'flex gap-2 screenshot-row animate-in fade-in slide-in-from-top-1 duration-200';
                        newRow.innerHTML = `
                            <input type="url" name="screenshots[]" placeholder="https://example.com/screenshot.jpg" class="input-box text-xs">
                            <button type="button" onclick="this.parentElement.remove()" class="px-3 bg-red-50 text-red-500 rounded-xl hover:bg-red-100 transition-all flex items-center justify-center">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        `;
                        container.appendChild(newRow);
                    }

                    let featureIdx = <?php echo count($f_list); ?>;
                    function addFeature() {
                        const container = document.getElementById('features-container');
                        const newRow = document.createElement('div');
                        newRow.className = 'feature-row bg-slate-50 p-6 rounded-3xl border border-slate-100 relative group animate-in fade-in slide-in-from-top-1 duration-200';
                        newRow.innerHTML = `
                            <button type="button" onclick="this.parentElement.remove()" class="absolute -top-3 -right-3 w-8 h-8 bg-white text-red-500 border border-red-100 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-1">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Select Icon</label>
                                    <select name="features_data[${featureIdx}][icon]" class="input-box text-xs">
                                        <option value="verified">Verified / Check</option>
                                        <option value="security">Privacy / Shield</option>
                                        <option value="bolt">Fast / Lightning</option>
                                        <option value="display_settings">DPI / Display</option>
                                        <option value="groups">Dual / Accounts</option>
                                        <option value="rocket_launch">Performance / Rocket</option>
                                        <option value="touch_app">One-Click / Click</option>
                                        <option value="settings_suggest">Custom / Settings</option>
                                        <option value="lock">Secure / Lock</option>
                                        <option value="speed">Speed / Meter</option>
                                    </select>
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Accent Color</label>
                                    <select name="features_data[${featureIdx}][color]" class="input-box text-xs">
                                        <option value="indigo">Blue / Indigo</option>
                                        <option value="emerald">Green / Emerald</option>
                                        <option value="purple">Purple / Violet</option>
                                        <option value="amber">Orange / Amber</option>
                                        <option value="rose">Red / Rose</option>
                                    </select>
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Feature Title</label>
                                    <input type="text" name="features_data[${featureIdx}][title]" placeholder="Feature Name" class="input-box text-xs">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Description</label>
                                    <textarea name="features_data[${featureIdx}][desc]" rows="2" class="input-box text-xs" placeholder="Describe the feature..."></textarea>
                                </div>
                            </div>
                        `;
                        container.appendChild(newRow);
                        featureIdx++;
                    }

                    // Related Apps Picker Logic
                    const relatedInput = document.getElementById('related-apps-input');
                    const tagsContainer = document.getElementById('related-tags');
                    const appSearch = document.getElementById('app-search');
                    const searchResults = document.getElementById('search-results');
                    const pickerItems = document.querySelectorAll('.picker-item');

                    let selectedIds = relatedInput.value ? relatedInput.value.split(',').map(id => id.trim()) : [];

                    function updateRelatedInput() {
                        relatedInput.value = selectedIds.join(',');
                        renderTags();
                    }

                    function renderTags() {
                        tagsContainer.innerHTML = '';
                        selectedIds.forEach(id => {
                            if (!id) return;
                            const item = Array.from(pickerItems).find(i => i.dataset.id == id);
                            const name = item ? item.dataset.name : `App #${id}`;
                            const logo = item ? item.dataset.logo : '';

                            const tag = document.createElement('div');
                            tag.className = 'flex items-center gap-2 px-3 py-1.5 bg-indigo-50 border border-indigo-100 rounded-xl animate-in zoom-in-95 duration-200';
                            tag.innerHTML = `
                                ${logo ? `<img src="${logo}" class="w-4 h-4 rounded-md object-cover">` : ''}
                                <span class="text-[11px] font-bold text-indigo-700">${name}</span>
                                <button type="button" onclick="removeRelated('${id}')" class="text-indigo-300 hover:text-indigo-600 transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                </button>
                            `;
                            tagsContainer.appendChild(tag);
                        });
                    }

                    function removeRelated(id) {
                        selectedIds = selectedIds.filter(sid => sid != id);
                        updateRelatedInput();
                    }

                    appSearch.addEventListener('focus', () => searchResults.classList.remove('hidden'));
                    document.addEventListener('click', (e) => {
                        if (!appSearch.contains(e.target) && !searchResults.contains(e.target)) {
                            searchResults.classList.add('hidden');
                        }
                    });

                    appSearch.addEventListener('input', (e) => {
                        const term = e.target.value.toLowerCase();
                        pickerItems.forEach(item => {
                            const name = item.dataset.name.toLowerCase();
                            if (name.includes(term)) {
                                item.classList.remove('hidden');
                            } else {
                                item.classList.add('hidden');
                            }
                        });
                        searchResults.classList.remove('hidden');
                    });

                    pickerItems.forEach(item => {
                        item.addEventListener('click', () => {
                            const id = item.dataset.id;
                            if (!selectedIds.includes(id)) {
                                selectedIds.push(id);
                                updateRelatedInput();
                            }
                            appSearch.value = '';
                            searchResults.classList.add('hidden');
                        });
                    });

                    // Initial render
                    renderTags();

                    let versionIdx = <?php echo count($v_list); ?>;
                    function addVersion() {
                        const container = document.getElementById('versions-container');
                        const newRow = document.createElement('div');
                        newRow.className = 'version-row flex flex-col md:flex-row gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100 relative group animate-in fade-in slide-in-from-top-1 duration-200';
                        newRow.innerHTML = `
                            <button type="button" onclick="this.parentElement.remove()" class="absolute -top-2 -right-2 w-6 h-6 bg-white text-red-500 border border-red-100 rounded-full shadow-md opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="material-symbols-outlined text-[14px]">close</span>
                            </button>
                            <div class="flex-1">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Version Name</label>
                                <input type="text" name="versions_data[${versionIdx}][version_name]" placeholder="v1.0.2" class="input-box text-xs py-2">
                            </div>
                            <div class="flex-[2]">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Download URL</label>
                                <input type="url" name="versions_data[${versionIdx}][download_url]" placeholder="https://..." class="input-box text-xs py-2">
                            </div>
                            <div class="flex-1">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Release Date</label>
                                <input type="text" name="versions_data[${versionIdx}][release_date]" placeholder="Oct 2023" class="input-box text-xs py-2">
                            </div>
                        `;
                        container.appendChild(newRow);
                        versionIdx++;
                    }
                    </script>

                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 bg-indigo-900 text-white font-black py-4 rounded-2xl uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-950 transition-all active:scale-95 flex items-center justify-center gap-3">
                            <span class="material-symbols-outlined">save</span>
                            Commit Metadata Changes
                        </button>
                        <a href="apps.php" class="px-8 py-4 bg-white border border-slate-200 text-slate-600 font-black rounded-2xl uppercase tracking-widest text-[11px] flex items-center justify-center">Discard</a>
                    </div>
                </div>
            </div>
        </form>
        </div>
    </div>
</main>

</body>
</html>
