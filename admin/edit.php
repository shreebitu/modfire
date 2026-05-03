<?php
require_once '../config.php';
require_once '../db.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT apps.*, users.username, categories.name as category_name FROM apps 
                        JOIN users ON apps.user_id = users.id 
                        LEFT JOIN categories ON apps.category_id = categories.id
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
    $category_id = (int)$_POST['category_id'];
    $version = sanitizeInput($_POST['version']);
    $apk_link = sanitizeInput($_POST['apk_link']);
    $status = sanitizeInput($_POST['status']);
    $description = $_POST['description']; // Keeping HTML from TinyMCE
    
    $file_size = sanitizeInput($_POST['file_size']);
    $os_compatible = sanitizeInput($_POST['os_compatible']);
    $language = sanitizeInput($_POST['language']);
    $license_type = sanitizeInput($_POST['license_type']);
    $developer = sanitizeInput($_POST['developer']);
    $screenshots = sanitizeInput($_POST['screenshots']);
    
    $seo_title = sanitizeInput($_POST['seo_title']);
    $meta_description = sanitizeInput($_POST['meta_description']);
    $meta_keywords = sanitizeInput($_POST['meta_keywords']);

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
        $stmt = $pdo->prepare("UPDATE apps SET name = ?, slug = ?, category_id = ?, version = ?, apk_link = ?, description = ?, logo = ?, screenshots = ?, file_size = ?, os_compatible = ?, language = ?, license_type = ?, developer = ?, status = ?, seo_title = ?, meta_description = ?, meta_keywords = ? WHERE id = ?");
        if ($stmt->execute([$name, $slug, $category_id, $version, $apk_link, $description, $logo_path, $screenshots, $file_size, $os_compatible, $language, $license_type, $developer, $status, $seo_title, $meta_description, $meta_keywords, $id])) {
            logActivity('admin_action', "Modified application ID: $id ($name)");
            $success = "Metadata updated successfully!";
            // Refresh
            $stmt = $pdo->prepare("SELECT apps.*, users.username, categories.name as category_name FROM apps 
                                    JOIN users ON apps.user_id = users.id 
                                    LEFT JOIN categories ON apps.category_id = categories.id
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
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      tinymce.init({
        selector: 'textarea[name="description"]',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
      });
    </script>
    <style>
        :root { --primary: #1f108e; --slate-50: #f8fafc; --slate-100: #f1f5f9; --slate-200: #e2e8f0; }
        body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; color: #1e293b; }
        .sidebar { background: var(--slate-50); border-right: 1px solid var(--slate-200); }
        .nav-item { transition: all 0.2s; font-weight: 500; font-size: 14px; color: #64748b; }
        .nav-item:hover { background-color: var(--slate-100); color: var(--primary); }
        .nav-item.active { background-color: #ffffff; color: var(--primary); border-right: 3px solid var(--primary); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .card-white { background: #ffffff; border-radius: 24px; border: 1px solid var(--slate-200); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .input-box { width: 100%; px: 1rem; py: 0.625rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.875rem; outline: none; transition: all 0.2s; }
        .input-box:focus { border-color: #1f108e; box-shadow: 0 0 0 4px rgba(31, 16, 142, 0.05); background: white; }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

<!-- Sidebar -->
<aside class="sidebar w-[260px] flex-shrink-0 flex flex-col h-full hidden md:flex z-20">
    <div class="px-6 py-8 mb-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-indigo-900 rounded-xl flex items-center justify-center shadow-lg">
                <span class="material-symbols-outlined text-white text-lg">shield_person</span>
            </div>
            <div>
                <h1 class="text-lg font-black text-indigo-950 leading-tight">SUPER ADMIN</h1>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Platform Root</p>
            </div>
        </div>
    </div>
    <div class="flex-1 px-3 space-y-1">
        <nav class="space-y-1">
            <a href="dashboard.php" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg"><span class="material-symbols-outlined">dashboard</span> Dashboard</a>
            <a href="apps.php" class="nav-item active flex items-center gap-3 px-3 py-2.5 rounded-lg"><span class="material-symbols-outlined">inventory_2</span> Manage Content</a>
            <a href="users.php" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg"><span class="material-symbols-outlined">group</span> User Control</a>
            <a href="reports.php" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg"><span class="material-symbols-outlined">flag</span> Reports</a>
        </nav>
        <div class="h-px bg-slate-200 my-4 mx-3"></div>
        <nav class="space-y-1">
            <a href="categories.php" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg"><span class="material-symbols-outlined">category</span> Categories</a>
            <a href="settings.php" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg"><span class="material-symbols-outlined">settings</span> Global Settings</a>
            <a href="security.php" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg"><span class="material-symbols-outlined">security</span> IP & Security</a>
            <a href="logs.php" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg"><span class="material-symbols-outlined">history</span> Action Logs</a>
        </nav>
    </div>
</aside>

<main class="flex-1 overflow-y-auto bg-[#f8fafc] p-6 lg:p-10">
    <div class="max-w-[1000px] mx-auto">
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
                            $logo_src = (strpos($logo_preview, 'http') === 0) ? $logo_preview : '../' . $logo_preview;
                            ?>
                            <img src="<?php echo htmlspecialchars($logo_src); ?>" class="w-full h-full object-contain rounded-2xl">
                            <label class="absolute inset-0 bg-black/40 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer">
                                <span class="material-symbols-outlined text-white">upload</span>
                                <input type="file" name="logo" class="hidden">
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
                                <select name="category_id" class="input-box">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php if($app['category_id'] == $cat['id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
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
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Screenshots (Comma separated URLs)</label>
                                <textarea name="screenshots" rows="3" class="input-box font-mono text-xs" placeholder="https://example.com/s1.jpg, https://example.com/s2.jpg"><?php echo htmlspecialchars($app['screenshots']); ?></textarea>
                            </div>
                        </div>
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
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6">Long Description (Rich Text)</h3>
                        <textarea name="description" rows="12" class="input-box leading-relaxed" placeholder="Detailed app information..."><?php echo htmlspecialchars($app['description']); ?></textarea>
                    </div>

                    <script>
                    function generateSlug() {
                        const name = document.querySelector('input[name="name"]').value;
                        const slug = name.toLowerCase()
                            .replace(/[^a-z0-9]+/g, '-')
                            .replace(/(^-|-$)/g, '') + '-download';
                        document.getElementById('slug-input').value = slug;
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
</main>

</body>
</html>
