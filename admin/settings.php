<?php
require_once '../config.php';
require_once '../db.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

$success = '';
$error = '';

// Handle Save Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    verifyCsrfToken($_POST['csrf_token'] ?? '');
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }
    logActivity('update_settings', 'System settings were updated by admin');
    $success = "Global settings updated successfully.";
    
    // Refresh settings in current session context
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $site_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

// Fetch all settings
$stmt = $pdo->query("SELECT * FROM settings");
$settings_raw = $stmt->fetchAll();
$settings = [];
foreach ($settings_raw as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}

$reportsCount = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Control - ShreeBitu</title>
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
        .section-title { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 1.5rem; display: flex; items-center: center; gap: 0.5rem; }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

<?php include 'components/sidebar.php'; ?>
<?php include 'components/mobile_sidebar.php'; ?>

<!-- Main Area -->
<main class="flex-1 flex flex-col h-full bg-[#f8fafc] relative z-10 w-full overflow-hidden">
    <?php include 'components/header.php'; ?>

    <div class="flex-1 overflow-y-auto main-scroll p-6 lg:p-10">
        <div class="max-w-[1000px] mx-auto">
        
        <header class="mb-10">
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Global Website Control</h2>
            <p class="text-slate-500 mt-1 font-medium">Manage every behavior and restriction of your platform in real-time.</p>
        </header>

        <?php if ($success): ?>
            <div class="bg-indigo-900 text-white px-6 py-4 rounded-3xl mb-8 flex items-center gap-4 shadow-xl shadow-indigo-100 animate-in slide-in-from-top duration-300">
                <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-sm">done_all</span>
                </div>
                <span class="text-xs font-black uppercase tracking-widest"><?php echo $success; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <!-- Core Toggles -->
            <div class="card-white p-8">
                <div class="section-title">
                    <span class="material-symbols-outlined text-[18px]">toggle_on</span> System Toggles
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div>
                            <p class="text-sm font-bold text-slate-900">Maintenance Mode</p>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-tighter">Site बंद करें</p>
                        </div>
                        <select name="settings[maintenance_mode]" class="bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-bold uppercase tracking-tight outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="0" <?php echo ($settings['maintenance_mode'] ?? '0') == '0' ? 'selected' : ''; ?>>OFF (LIVE)</option>
                            <option value="1" <?php echo ($settings['maintenance_mode'] ?? '0') == '1' ? 'selected' : ''; ?>>ON (LOCKED)</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div>
                            <p class="text-sm font-bold text-slate-900">Public Registration</p>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-tighter">New User Signup</p>
                        </div>
                        <select name="settings[registration_enabled]" class="bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-bold uppercase tracking-tight outline-none">
                            <option value="1" <?php echo ($settings['registration_enabled'] ?? '1') == '1' ? 'selected' : ''; ?>>ENABLED</option>
                            <option value="0" <?php echo ($settings['registration_enabled'] ?? '1') == '0' ? 'selected' : ''; ?>>DISABLED</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div>
                            <p class="text-sm font-bold text-slate-900">File Uploading</p>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-tighter">Allow users to post</p>
                        </div>
                        <select name="settings[upload_enabled]" class="bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-bold uppercase tracking-tight outline-none">
                            <option value="1" <?php echo ($settings['upload_enabled'] ?? '1') == '1' ? 'selected' : ''; ?>>ENABLED</option>
                            <option value="0" <?php echo ($settings['upload_enabled'] ?? '1') == '0' ? 'selected' : ''; ?>>DISABLED</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div>
                            <p class="text-sm font-bold text-slate-900">Download System</p>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-tighter">Global Download Switch</p>
                        </div>
                        <select name="settings[download_enabled]" class="bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-bold uppercase tracking-tight outline-none">
                            <option value="1" <?php echo ($settings['download_enabled'] ?? '1') == '1' ? 'selected' : ''; ?>>ENABLED</option>
                            <option value="0" <?php echo ($settings['download_enabled'] ?? '1') == '0' ? 'selected' : ''; ?>>DISABLED</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Content Restrictions -->
            <div class="card-white p-8">
                <div class="section-title">
                    <span class="material-symbols-outlined text-[18px]">gavel</span> Content Restrictions
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Max Upload Size (MB)</label>
                        <input type="number" name="settings[max_file_size]" value="<?php echo htmlspecialchars($settings['max_file_size'] ?? '100'); ?>" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-indigo-600 transition-all font-bold">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Allowed Extensions</label>
                        <input type="text" name="settings[allowed_extensions]" value="<?php echo htmlspecialchars($settings['allowed_extensions'] ?? 'apk,pdf,ppt'); ?>" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-indigo-600 transition-all font-bold">
                        <p class="text-[9px] text-slate-400 mt-2 font-bold uppercase tracking-tighter">Comma separated: apk, pdf, ppt, zip</p>
                    </div>
                    <div class="md:col-span-2 flex items-center justify-between p-5 bg-indigo-50/50 rounded-2xl border border-indigo-100">
                        <div>
                            <p class="text-sm font-bold text-slate-900">Moderation Mode</p>
                            <p class="text-[11px] text-indigo-600 font-bold">New uploads require manual admin approval</p>
                        </div>
                        <select name="settings[approval_system]" class="bg-white border border-indigo-200 rounded-xl px-4 py-2 text-xs font-black uppercase tracking-tight outline-none shadow-sm">
                            <option value="1" <?php echo ($settings['approval_system'] ?? '1') == '1' ? 'selected' : ''; ?>>STRICT (PENDING FIRST)</option>
                            <option value="0" <?php echo ($settings['approval_system'] ?? '1') == '0' ? 'selected' : ''; ?>>FAST (AUTO-APPROVE)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Advanced Security -->
            <div class="card-white p-8">
                <div class="section-title">
                    <span class="material-symbols-outlined text-[18px]">security</span> Security & API
                </div>
                <div class="space-y-6">
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Admin IP Whitelist</label>
                        <textarea name="settings[admin_ip_whitelist]" rows="2" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-red-500 transition-all font-mono text-xs" placeholder="Leave blank to allow all IPs. Use commas for multiple IPs."><?php echo htmlspecialchars($settings['admin_ip_whitelist'] ?? ''); ?></textarea>
                        <p class="text-[10px] text-red-500 mt-2 font-bold uppercase tracking-tighter flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">warning</span> Caution: If you set this, you can only log in from these IPs.
                        </p>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div>
                            <p class="text-sm font-bold text-slate-900">External Link Support</p>
                            <p class="text-[10px] text-slate-500 font-bold">Allow users to provide URLs instead of file uploads</p>
                        </div>
                        <select name="settings[external_links_allowed]" class="bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-bold uppercase tracking-tight outline-none">
                            <option value="1" <?php echo ($settings['external_links_allowed'] ?? '1') == '1' ? 'selected' : ''; ?>>ALLOW</option>
                            <option value="0" <?php echo ($settings['external_links_allowed'] ?? '1') == '0' ? 'selected' : ''; ?>>BLOCK</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" name="save_settings" class="bg-indigo-900 text-white px-12 py-4 rounded-3xl text-sm font-black uppercase tracking-[0.2em] hover:bg-indigo-950 transition-all shadow-2xl shadow-indigo-200 hover:-translate-y-1 active:translate-y-0 active:shadow-none">
                    Apply Global Changes
                </button>
            </div>
        </form>
    </div>
</main>

</body>
</html>
