<?php
require_once '../includes/init.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

$success = '';
$error = '';

// Handle IP Blocking
if (isset($_POST['block_ip'])) {
    $ip = trim($_POST['ip_address']);
    $reason = sanitizeInput($_POST['reason']);
    
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO blocked_ips (ip_address, reason) VALUES (?, ?)");
            $stmt->execute([$ip, $reason]);
            logActivity('admin_action', "Blocked IP: $ip");
            $success = "IP address blocked successfully.";
        } catch (PDOException $e) {
            $error = "IP already blocked.";
        }
    } else {
        $error = "Invalid IP address format.";
    }
}

// Handle Unblock
if (isset($_GET['unblock'])) {
    $id = (int)$_GET['unblock'];
    $stmt = $pdo->prepare("DELETE FROM blocked_ips WHERE id = ?");
    $stmt->execute([$id]);
    logActivity('admin_action', "Unblocked IP ID: $id");
    $success = "IP address unblocked.";
}

// Handle Whitelist Update
if (isset($_POST['update_whitelist'])) {
    $whitelist = trim($_POST['whitelist']);
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'admin_ip_whitelist'");
    $stmt->execute([$whitelist]);
    logActivity('admin_action', "Updated Admin IP Whitelist");
    $success = "Admin IP whitelist updated.";
    $site_settings['admin_ip_whitelist'] = $whitelist;
}

$stmt = $pdo->query("SELECT * FROM blocked_ips ORDER BY blocked_at DESC");
$blocked_ips = $stmt->fetchAll();

$reportsCount = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Control - Admin Panel</title>
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
    </style>
</head>
<body class="flex h-screen overflow-hidden">

<?php include 'components/sidebar.php'; ?>
<?php include 'components/mobile_sidebar.php'; ?>

<main class="flex-1 flex flex-col h-full bg-[#f8fafc] relative z-10 w-full overflow-hidden">
    <?php include 'components/header.php'; ?>

    <div class="flex-1 overflow-y-auto p-6 lg:p-10 main-scroll">
        <div class="max-w-[1000px] mx-auto">
        <header class="mb-10">
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Security Center</h2>
            <p class="text-slate-500 mt-1 font-medium">Protect your platform from spam, malicious actors, and unauthorized access.</p>
        </header>

        <?php if ($success): ?>
            <div class="bg-indigo-900 text-white px-6 py-4 rounded-3xl mb-8 flex items-center gap-4 shadow-xl shadow-indigo-100">
                <span class="material-symbols-outlined text-sm">shield</span>
                <span class="text-xs font-black uppercase tracking-widest"><?php echo $success; ?></span>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-600 text-white px-6 py-4 rounded-3xl mb-8 flex items-center gap-4">
                <span class="material-symbols-outlined text-sm">warning</span>
                <span class="text-xs font-black uppercase tracking-widest"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Block IP Section -->
            <div class="space-y-8">
                <div class="card-white p-6">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6 flex items-center gap-3">
                        <span class="material-symbols-outlined text-red-600">block</span>
                        Block New IP
                    </h3>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">IP Address</label>
                            <input type="text" name="ip_address" required placeholder="e.g. 192.168.1.1" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-red-600 transition-all font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Reason for Block</label>
                            <input type="text" name="reason" placeholder="e.g. Spam uploads" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-red-600 transition-all">
                        </div>
                        <button type="submit" name="block_ip" class="w-full bg-red-600 text-white py-2.5 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-700 transition-all">Ban IP Address</button>
                    </form>
                </div>

                <div class="card-white p-6">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6 flex items-center gap-3">
                        <span class="material-symbols-outlined text-indigo-600">verified_user</span>
                        Admin Whitelist
                    </h3>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Whitelisted IPs (Admin Only)</label>
                            <textarea name="whitelist" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-indigo-600 transition-all font-mono" placeholder="127.0.0.1, 192.168.1.50"><?php echo htmlspecialchars($site_settings['admin_ip_whitelist'] ?? ''); ?></textarea>
                            <p class="text-[9px] text-slate-400 mt-1 font-bold">Leave empty to allow all IPs for Admin login.</p>
                        </div>
                        <button type="submit" name="update_whitelist" class="w-full bg-indigo-900 text-white py-2.5 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-950 transition-all">Update Whitelist</button>
                    </form>
                </div>
            </div>

            <!-- Blocked IP List -->
            <div class="card-white overflow-hidden h-fit">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Banned IP List</h3>
                </div>
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">IP Address</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Reason</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (count($blocked_ips) > 0): ?>
                            <?php foreach ($blocked_ips as $bip): ?>
                                <tr class="hover:bg-slate-50/50 transition-all">
                                    <td class="px-6 py-4 font-mono text-xs text-red-600 font-bold"><?php echo htmlspecialchars($bip['ip_address']); ?></td>
                                    <td class="px-6 py-4 text-xs font-medium text-slate-500"><?php echo htmlspecialchars($bip['reason'] ?: 'No reason provided'); ?></td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="security.php?unblock=<?php echo $bip['id']; ?>" class="text-xs font-black text-indigo-600 uppercase tracking-widest hover:underline">Unblock</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-slate-400 text-xs font-bold uppercase tracking-widest">No blocked IPs found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</main>

</body>
</html>
