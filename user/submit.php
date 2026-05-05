<?php
/**
 * ============================================================
 * USER: APP SUBMISSION PAGE
 * ============================================================
 * Purpose: Allows logged-in users to submit new apps to the platform.
 *          Handles logo upload, optional file upload (APK/PDF/PPT),
 *          external link, and all metadata fields.
 *
 * Input:   POST: name, description, category, logo (file), app_file (file),
 *          apk_link, file_size, os_compatible, language, license_type,
 *          developer, csrf_token
 * Output:  Success/error message, form with mobile + desktop layouts
 * Security: Login required, CSRF protected, rate-limited (5/hour),
 *           file extension whitelist, duplicate URL detection
 * Connects to: apps table, categories table, site_settings
 * ============================================================
 */
require_once '../includes/init.php';

// ── Authentication Gate ──
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$error = '';
$success = '';

// ── Handle Form Submission ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if uploads are enabled in admin settings
    if (($site_settings['upload_enabled'] ?? '1') == '0') {
        die("New submissions are currently disabled by the administrator.");
    }
    // Verify CSRF token
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $user_id = $_SESSION['user_id'];

    // ── Rate Limiting: Prevent spam submissions (max 5 per hour per user) ──
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM apps WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->execute([$user_id]);
    $recent_submissions = $stmt->fetchColumn();

    if ($recent_submissions >= 5) {
        logActivity('upload_rate_limited', 'User exceeded submission rate limit');
        $error = "You have reached the limit of 5 submissions per hour. Please try again later.";
    }

    // ── Collect the external download link ──
    $apk_link = sanitizeInput($_POST['apk_link'] ?? '');

    // Check if external links are allowed by admin settings
    if (!empty($apk_link) && ($site_settings['external_links_allowed'] ?? '1') == '0' && empty($_FILES['app_file']['name'])) {
        $error = "Submitting external links is currently disabled. Please upload a file instead.";
    }

    // ── Duplicate URL Detection: Prevent the same link from being submitted twice ──
    if (empty($error) && !empty($apk_link)) {
        $stmt = $pdo->prepare("SELECT id FROM apps WHERE apk_link = ?");
        $stmt->execute([$apk_link]);
        if ($stmt->fetch()) {
            $error = "This URL has already been submitted.";
        }
    }

    // ── Collect all metadata fields ──
    // Desktop and mobile forms have separate field names; pick whichever is filled
    $name = sanitizeInput($_POST['name']);
    $allowed_tags = '<p><br><b><strong><i><em><u><ul><ol><li><a href title target><h1><h2><h3><h4><h5><h6><img><blockquote><span><div><hr><table><tbody><tr><td><th>';
    $description_desktop = isset($_POST['description_desktop']) ? strip_tags(trim($_POST['description_desktop']), $allowed_tags) : '';
    $description_mobile = isset($_POST['description_mobile']) ? strip_tags(trim($_POST['description_mobile']), $allowed_tags) : '';
    $description = !empty($description_desktop) ? $description_desktop : $description_mobile;
    $category = !empty($_POST['category_desktop']) ? sanitizeInput($_POST['category_desktop']) : (isset($_POST['category_mobile']) ? sanitizeInput($_POST['category_mobile']) : '');
    $file_size = !empty($_POST['file_size_desktop']) ? sanitizeInput($_POST['file_size_desktop']) : (isset($_POST['file_size_mobile']) ? sanitizeInput($_POST['file_size_mobile']) : '');

    // OS compatibility can come from checkboxes (mobile) or text input (desktop)
    $os_mobile_arr = [];
    if (!empty($_POST['os_compatible_m1']))
        $os_mobile_arr[] = sanitizeInput($_POST['os_compatible_m1']);
    if (!empty($_POST['os_compatible_m2']))
        $os_mobile_arr[] = sanitizeInput($_POST['os_compatible_m2']);
    $os_mobile = implode(", ", $os_mobile_arr);
    $os_compatible = !empty($_POST['os_compatible_desktop']) ? sanitizeInput($_POST['os_compatible_desktop']) : $os_mobile;

    $language = !empty($_POST['language_desktop']) ? sanitizeInput($_POST['language_desktop']) : (isset($_POST['language_mobile']) ? sanitizeInput($_POST['language_mobile']) : '');
    $license_type = !empty($_POST['license_type_desktop']) ? sanitizeInput($_POST['license_type_desktop']) : (isset($_POST['license_type_mobile']) ? sanitizeInput($_POST['license_type_mobile']) : '');
    $developer = !empty($_POST['developer_desktop']) ? sanitizeInput($_POST['developer_desktop']) : (isset($_POST['developer_mobile']) ? sanitizeInput($_POST['developer_mobile']) : '');

    // ── Handle Logo Upload (required) ──
    $logo_path = '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $file_tmp = $_FILES['logo']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $allowed_img = ['jpg', 'jpeg', 'png', 'svg', 'webp'];

        if (in_array($file_ext, $allowed_img)) {
            // Generate a unique filename to prevent collisions
            $file_name = 'logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
            if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
                $logo_path = 'uploads/' . $file_name;
            } else {
                $error = "Failed to upload logo.";
            }
        } else {
            $error = "Invalid logo format.";
        }
    } else {
        $error = "Please upload a logo.";
    }

    // ── Handle Main File Upload (optional — alternative to external link) ──
    $final_apk_link = $apk_link;
    if (empty($error) && isset($_FILES['app_file']) && $_FILES['app_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/files/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0755, true);

        $file_tmp = $_FILES['app_file']['tmp_name'];
        $file_orig_name = $_FILES['app_file']['name'];
        $file_ext = strtolower(pathinfo($file_orig_name, PATHINFO_EXTENSION));

        // Validate against admin-configured allowed extensions and max file size
        $allowed_exts = explode(',', str_replace(' ', '', $site_settings['allowed_extensions'] ?? 'apk,pdf,ppt'));
        $max_size_mb = (int) ($site_settings['max_file_size'] ?? 100);
        $file_size_bytes = $_FILES['app_file']['size'];

        if (!in_array($file_ext, $allowed_exts)) {
            $error = "File extension not allowed. Allowed: " . implode(', ', $allowed_exts);
        } elseif ($file_size_bytes > ($max_size_mb * 1024 * 1024)) {
            $error = "File is too large. Max allowed: " . $max_size_mb . " MB";
        } else {
            $new_file_name = 'file_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
            if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                // Use local file path as the download link
                $final_apk_link = 'uploads/files/' . $new_file_name;
                // Auto-detect file size if not manually entered
                if (empty($file_size)) {
                    $file_size = round($file_size_bytes / (1024 * 1024), 2) . ' MB';
                }
            } else {
                $error = "Failed to store uploaded file.";
            }
        }
    }

    // Ensure at least one download source is provided
    if (empty($error) && empty($final_apk_link)) {
        $error = "Please provide an external link or upload a file.";
    }

    // ── Insert the app into the database ──
    if (empty($error)) {
        // If approval system is enabled, set status to 'pending'; otherwise auto-approve
        $initial_status = ($site_settings['approval_system'] ?? '1') == '1' ? 'pending' : 'approved';
        $stmt = $pdo->prepare("INSERT INTO apps (name, description, logo, apk_link, category, user_id, file_size, os_compatible, language, license_type, developer, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $description, $logo_path, $final_apk_link, $category, $user_id, $file_size, $os_compatible, $language, $license_type, $developer, $initial_status])) {
            logActivity('upload_success', 'New app submitted: ' . $name);
            $success = $initial_status === 'pending' ? "Submission successful! Waiting for admin approval." : "Submission successful! Your app is now live.";
        } else {
            $error = "Database save failed.";
        }
    }
}

// ── Fetch categories for the dropdown/pill selectors ──
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit App - ShreeBitu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"
        rel="stylesheet">
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

        body {
            font-family: 'Inter', sans-serif !important;
            background-color: #f7f9fb !important;
            color: #1e293b;
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
        <header
            class="h-16 header-blur flex items-center justify-between px-6 lg:px-8 shrink-0 z-30 sticky top-0 bg-white">
            <div class="flex items-center gap-1 md:gap-4 flex-1">
                <button id="mobileMenuBtn"
                    class="md:hidden text-slate-600 hover:text-indigo-700 focus:outline-none p-1 rounded-xl transition-all flex items-center justify-center">
                    <span class="material-symbols-outlined text-[34px]">menu</span>
                </button>
                <div class="flex md:hidden items-center">
                    <h1 class="text-2xl font-black text-indigo-900 tracking-tighter leading-none">MODFIRE</h1>
                </div>

                <!-- Breadcrumbs / Title -->
                <div class="hidden md:flex items-center gap-2 text-sm text-slate-500">
                    <a href="../index.php" class="hover:text-indigo-600 font-medium transition-colors">Home</a>
                    <span class="material-symbols-outlined text-slate-300">chevron_right</span>
                    <span class="font-bold text-slate-900 uppercase tracking-widest text-[11px]">Submit
                        Application</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <?php if (isLoggedIn()): ?>
                    <div class="relative group">
                        <button
                            class="flex items-center gap-3 p-1 pr-4 bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-indigo-200 transition-all group">
                            <div
                                class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-[12px] font-bold shadow-indigo-100 shadow-lg overflow-hidden shrink-0">
                                <?php if (!empty($_SESSION['profile_pic'])): ?>
                                    <img src="../<?php echo htmlspecialchars($_SESSION['profile_pic']); ?>"
                                        class="w-full h-full object-cover">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <div class="text-left hidden sm:block">
                                <p
                                    class="text-[11px] font-bold text-slate-700 group-hover:text-indigo-600 transition-colors leading-none">
                                    <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                                </p>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter mt-1">Profile Hub
                                </p>
                            </div>
                            <span class="material-symbols-outlined text-slate-300 text-[18px]">expand_more</span>
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            class="absolute right-0 mt-2 w-64 bg-white rounded-[24px] shadow-[0_20px_50px_rgba(0,0,0,0.15)] border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform scale-95 group-hover:scale-100 origin-top-right z-50 overflow-hidden">
                            <div class="p-5 border-b border-slate-50 bg-slate-50/50">
                                <p class="text-sm font-black text-slate-900 leading-none">
                                    <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                                </p>
                                <p class="text-[10px] text-indigo-600 font-black uppercase tracking-widest mt-2">
                                    <?php echo isAdmin() ? 'Super Admin' : 'Premium Member'; ?>
                                </p>
                            </div>
                            <div class="p-2 space-y-1">
                                <a href="profile.php"
                                    class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                                    <span class="material-symbols-outlined text-[20px]">person</span> Account Details
                                </a>
                                <?php if (isAdmin()): ?>
                                    <a href="../admin/dashboard.php"
                                        class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-indigo-700 bg-indigo-50/50 hover:bg-indigo-50 rounded-xl transition-all">
                                        <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span> Admin Panel
                                    </a>
                                    <div class="h-px bg-slate-50 my-1"></div>
                                <?php endif; ?>

                                <a href="submit.php"
                                    class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-indigo-700 bg-indigo-50/50 rounded-xl transition-all">
                                    <span class="material-symbols-outlined text-[20px]">publish</span> Submit New App
                                </a>
                                <a href="myapps.php"
                                    class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                                    <span class="material-symbols-outlined text-[20px]">cloud_upload</span> My Uploads
                                </a>
                                <a href="favorites.php"
                                    class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-all">
                                    <span class="material-symbols-outlined text-[20px]">favorite</span> My Favorites
                                </a>
                                <div class="h-px bg-slate-50 my-1"></div>
                                <a href="../auth/logout.php"
                                    class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 rounded-xl transition-all">
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
            <div class="max-w-[1500px] mx-auto mt-6 md:mt-8">
                <div class="mb-6 md:mb-8 text-center md:text-left">
                    <h1 class="text-[24px] md:text-[28px] font-bold text-slate-900 leading-tight tracking-tight">Submit
                        a New App</h1>
                    <p class="text-[13px] md:text-[14px] text-slate-500 mt-1">Publish your software to the global
                        catalog.</p>
                </div>

                <?php if ($error): ?>
                    <div
                        class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-red-500">error</span>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div
                        class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 text-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-green-500">check_circle</span>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data"
                    class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <!-- Left Column (Main Form) -->
                    <div class="lg:col-span-2 space-y-8 md:space-y-6">

                        <!-- Basic Information Card -->
                        <div
                            class="bg-transparent md:bg-white md:rounded-xl md:shadow-sm md:border md:border-slate-100 md:p-8">
                            <h2 class="hidden md:flex text-[16px] font-bold text-slate-900 mb-6 items-center gap-2">
                                <span class="material-symbols-outlined text-indigo-600">info</span>
                                Basic Information
                            </h2>

                            <div class="space-y-6">
                                <!-- Logo Upload -->
                                <div class="flex items-start md:items-center gap-4 md:gap-6 flex-col md:flex-row">
                                    <label
                                        class="md:hidden block text-[11px] font-bold text-slate-900 uppercase tracking-wider w-full">App
                                        Logo</label>
                                    <div
                                        class="w-20 h-20 md:w-24 md:h-24 rounded-xl border-2 border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-400 bg-white md:bg-slate-50 flex-shrink-0 relative overflow-hidden group hover:border-indigo-600 hover:bg-indigo-50 transition-colors">
                                        <span class="material-symbols-outlined text-2xl">add_photo_alternate</span>
                                        <span
                                            class="text-[9px] font-bold uppercase tracking-wider mt-1 md:mt-0">Upload</span>
                                        <input type="file" name="logo" accept="image/*" required
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    </div>
                                    <div class="hidden md:block pt-2">
                                        <label
                                            class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wide">App
                                            Logo</label>
                                        <p class="text-[13px] text-slate-500 mb-2">Recommend 512x512px PNG or SVG. Max
                                            size 2MB.</p>
                                        <div class="text-[13px] font-semibold text-indigo-900">Select image file</div>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-[11px] font-bold text-slate-700 mb-1.5 uppercase tracking-wider">App
                                        Name</label>
                                    <input type="text" name="name" placeholder="e.g., Quantum Task Manager" required
                                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-600 outline-none transition-all text-[14px]">
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="hidden md:block">
                                        <label
                                            class="block text-[11px] font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Category</label>
                                        <select name="category_desktop"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-600 outline-none transition-all text-[14px] appearance-none">
                                            <option value="" disabled selected>Select Category</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo htmlspecialchars($cat['name']); ?>">
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Mobile Category Pills -->
                                    <div class="col-span-2 md:hidden">
                                        <label
                                            class="block text-[11px] font-bold text-slate-900 mb-2 uppercase tracking-wider">Category</label>
                                        <div class="flex gap-2.5 overflow-x-auto pb-1 scrollbar-hide">
                                            <?php foreach ($categories as $index => $cat): ?>
                                                <label class="flex-shrink-0 cursor-pointer">
                                                    <input type="radio" name="category_mobile"
                                                        value="<?php echo htmlspecialchars($cat['name']); ?>"
                                                        class="peer sr-only" <?php echo $index === 0 ? 'checked' : ''; ?>>
                                                    <span
                                                        class="px-4 py-2 rounded-full border border-slate-200 text-[13px] text-slate-700 peer-checked:bg-indigo-900 peer-checked:text-white peer-checked:border-indigo-900 transition-all whitespace-nowrap block">
                                                        <?php echo htmlspecialchars($cat['name']); ?>
                                                    </span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <div class="md:hidden">
                                        <label
                                            class="block text-[11px] font-bold text-slate-900 mb-1.5 uppercase tracking-wider">Developer
                                            / Studio</label>
                                        <input type="text" name="developer_mobile"
                                            placeholder="Individual or Company Name"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-600 outline-none transition-all text-[14px]">
                                    </div>

                                    <div class="hidden md:block">
                                        <label
                                            class="block text-[11px] font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Primary
                                            Language</label>
                                        <input type="text" name="language_desktop" placeholder="e.g., English"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-600 outline-none transition-all text-[14px]">
                                    </div>
                                </div>

                                <div class="hidden md:block">
                                    <label
                                        class="block text-[11px] font-bold text-slate-700 mb-1.5 uppercase tracking-wider">App
                                        Description</label>
                                    <textarea id="description_desktop" name="description_desktop" rows="6"
                                        placeholder="Describe the core features and benefits of your application..."
                                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-600 outline-none transition-all text-[14px] leading-relaxed resize-y"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Technical & Developer Details Card -->
                        <div
                            class="bg-transparent md:bg-white md:rounded-xl md:shadow-sm md:border md:border-slate-100 md:p-8">
                            <h2 class="hidden md:flex text-[16px] font-bold text-slate-900 mb-6 items-center gap-2">
                                <span class="material-symbols-outlined text-indigo-600">code</span>
                                Technical & Developer Details
                            </h2>

                            <div class="space-y-6">
                                <div class="grid grid-cols-2 gap-4 md:gap-5">
                                    <div class="hidden md:block col-span-2 md:col-span-1">
                                        <label
                                            class="block text-[11px] font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Developer
                                            / Studio</label>
                                        <input type="text" name="developer_desktop"
                                            placeholder="Company or Individual Name"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-600 outline-none transition-all text-[14px]">
                                    </div>
                                    <div class="col-span-1 md:hidden">
                                        <label
                                            class="block text-[11px] font-bold text-slate-900 mb-1.5 uppercase tracking-wider">File
                                            Size</label>
                                        <input type="text" name="file_size_mobile" placeholder="e.g. 45MB"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-600 outline-none transition-all text-[14px]">
                                    </div>
                                    <div class="col-span-1 md:col-span-1 md:hidden">
                                        <label
                                            class="block text-[11px] font-bold text-slate-900 mb-1.5 uppercase tracking-wider">Primary
                                            Language</label>
                                        <select name="language_mobile"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-600 outline-none transition-all text-[14px] appearance-none">
                                            <option value="English">English</option>
                                            <option value="Spanish">Spanish</option>
                                            <option value="French">French</option>
                                        </select>
                                    </div>

                                    <div class="col-span-2 md:col-span-1 md:hidden">
                                        <label
                                            class="block text-[11px] font-bold text-slate-900 mb-1.5 uppercase tracking-wider">OS
                                            Compatibility</label>
                                        <div class="grid grid-cols-2 gap-4">
                                            <label
                                                class="flex items-center gap-3 bg-white border border-slate-200 rounded-lg px-4 py-3 cursor-pointer">
                                                <input type="checkbox" name="os_compatible_m1" value="Android 12+"
                                                    checked
                                                    class="w-4 h-4 text-indigo-900 border-slate-300 rounded focus:ring-indigo-900">
                                                <span class="text-[13px] text-slate-800">Android 12+</span>
                                            </label>
                                            <label
                                                class="flex items-center gap-3 bg-white border border-slate-200 rounded-lg px-4 py-3 cursor-pointer">
                                                <input type="checkbox" name="os_compatible_m2" value="Linux x86"
                                                    class="w-4 h-4 text-indigo-900 border-slate-300 rounded focus:ring-indigo-900">
                                                <span class="text-[13px] text-slate-800">Linux x86</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="hidden md:block col-span-1">
                                        <label
                                            class="block text-[11px] font-bold text-slate-700 mb-1.5 uppercase tracking-wider">License
                                            Type</label>
                                        <input type="text" name="license_type_desktop"
                                            placeholder="Open Source (MIT), Freeware..."
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-600 outline-none transition-all text-[14px]">
                                    </div>
                                </div>

                                <div class="hidden md:grid grid-cols-2 gap-5">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-slate-700 mb-1.5 uppercase tracking-wider">OS
                                            Compatibility</label>
                                        <input type="text" name="os_compatible_desktop"
                                            placeholder="e.g., Android 12+, Linux x86"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-600 outline-none transition-all text-[14px]">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-slate-700 mb-1.5 uppercase tracking-wider">File
                                            Size (MB)</label>
                                        <input type="text" name="file_size_desktop" placeholder="0.0"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-600 outline-none transition-all text-[14px]">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Direct
                                            File Upload (Optional)</label>
                                        <div
                                            class="w-full h-[52px] border border-slate-200 border-dashed rounded-lg flex items-center px-4 bg-slate-50 hover:bg-white transition-colors relative cursor-pointer group">
                                            <span
                                                class="material-symbols-outlined text-slate-400 group-hover:text-indigo-600 mr-3">upload_file</span>
                                            <span class="text-[13px] text-slate-500 group-hover:text-slate-900">Choose
                                                APK, PDF, or PPT...</span>
                                            <input type="file" name="app_file"
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                        </div>
                                        <p class="text-[10px] text-slate-400 mt-1">Max size: 100MB</p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-slate-700 mb-1.5 uppercase tracking-wider">OR
                                            External Link</label>
                                        <div class="relative">
                                            <span
                                                class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-lg">link</span>
                                            <input type="url" name="apk_link" placeholder="https://example.com/file.apk"
                                                class="w-full pl-9 pr-4 py-3 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-600 outline-none transition-all text-[14px]">
                                        </div>
                                    </div>
                                </div>

                                <div class="md:hidden">
                                    <label
                                        class="block text-[11px] font-bold text-slate-900 mb-1.5 uppercase tracking-wider">License
                                        Type</label>
                                    <select name="license_type_mobile"
                                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-600 outline-none transition-all text-[14px] appearance-none">
                                        <option value="MIT License">MIT License</option>
                                        <option value="GPLv3">GPLv3</option>
                                        <option value="Freeware">Freeware</option>
                                    </select>
                                </div>

                                <div class="md:hidden">
                                    <label
                                        class="block text-[11px] font-bold text-slate-900 mb-1.5 uppercase tracking-wider">App
                                        Description</label>
                                    <textarea name="description_mobile" rows="4"
                                        placeholder="Describe the key features, target audience, and release notes..."
                                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-600 outline-none transition-all text-[14px] leading-relaxed resize-y"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column (Sidebar panels) -->
                    <div class="lg:col-span-1 space-y-6">

                        <!-- Review Protocol (Mobile Info box) -->
                        <div
                            class="bg-slate-100 md:bg-indigo-50 rounded-xl p-4 md:p-6 border border-slate-200 md:border-indigo-100 flex gap-3 items-start md:items-center">
                            <span class="material-symbols-outlined text-indigo-900 md:text-indigo-600">visibility</span>
                            <div class="hidden md:block">
                                <h3 class="font-bold text-indigo-900 text-[15px] mb-1">Review Protocol</h3>
                                <p class="text-[13px] text-indigo-700 leading-relaxed">
                                    Your submission will be reviewed by our moderation team. Most apps are verified
                                    within 24-48 business hours.
                                </p>
                            </div>
                            <p class="md:hidden text-[13px] text-slate-600 leading-relaxed">
                                Your submission will be reviewed by our moderation team. Most apps are verified within
                                24-48 business hours.
                            </p>
                        </div>

                        <!-- Submission Checklist -->
                        <div class="hidden md:block bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                            <h3 class="text-[11px] font-bold text-slate-700 mb-4 uppercase tracking-wider">Submission
                                Checklist</h3>
                            <ul class="space-y-3.5">
                                <li class="flex items-center gap-3 text-[13px] text-slate-800 font-medium">
                                    <span class="material-symbols-outlined text-indigo-600 text-lg">check_circle</span>
                                    APK link is accessible
                                </li>
                                <li class="flex items-center gap-3 text-[13px] text-slate-800 font-medium">
                                    <span class="material-symbols-outlined text-indigo-600 text-lg">check_circle</span>
                                    High-res icon uploaded
                                </li>
                                <li class="flex items-center gap-3 text-[13px] text-slate-400 font-medium">
                                    <span class="material-symbols-outlined text-lg">radio_button_unchecked</span>
                                    All metadata complete
                                </li>
                            </ul>
                        </div>

                        <!-- Image Placeholder -->
                        <div
                            class="hidden md:flex bg-slate-900 rounded-xl shadow-sm border border-slate-800 overflow-hidden relative h-[150px] items-end p-5">
                            <div class="absolute inset-0 opacity-40 mix-blend-screen"
                                style="background-image: linear-gradient(to right, #1e293b 1px, transparent 1px), linear-gradient(to bottom, #1e293b 1px, transparent 1px); background-size: 14px 14px;">
                            </div>
                            <div class="relative z-10 w-full">
                                <div class="text-[10px] font-bold text-white uppercase tracking-widest mb-1.5">Platform
                                    Integrity Dashboard</div>
                                <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden mt-2">
                                    <div class="w-2/3 h-full bg-indigo-500"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-3 pt-4 md:pt-2">
                            <button type="submit"
                                class="w-full bg-indigo-900 text-white font-bold py-3.5 rounded-xl hover:bg-indigo-800 shadow-md transition-all text-[16px] flex justify-center items-center gap-2">
                                <span class="material-symbols-outlined">publish</span>
                                Submit App for Review
                            </button>
                        </div>
                    </div>
            </div>
            </form>
        </div>
        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const btn = document.getElementById('mobileMenuBtn');
            const closeBtn = document.getElementById('closeSidebarBtn');
            const sidebar = document.getElementById('mobileSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (btn && sidebar && overlay) {
                const openMenu = () => {
                    sidebar.classList.add('open');
                    overlay.classList.add('open');
                    document.body.style.overflow = 'hidden';
                };
                const closeMenu = () => {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('open');
                    document.body.style.overflow = '';
                };
                btn.addEventListener('click', openMenu);
                if (closeBtn) closeBtn.addEventListener('click', closeMenu);
                overlay.addEventListener('click', closeMenu);
                document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMenu(); });
            }
        });
    </script>
</body>

</html>