<?php
require_once '../includes/init.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

$success = '';
$error = '';

// Handle role update
if (isset($_POST['update_role'])) {
    $user_id = (int)$_POST['user_id'];
    $role = $_POST['role'];
    
    // Safety: Prevent self-demotion and protecting root admin (ID: 1)
    if ($user_id === (int)$_SESSION['user_id']) {
        $error = "You cannot change your own role. This prevents accidental lockout.";
    } elseif ($user_id === 1) {
        $error = "The root Super Admin role cannot be modified for security reasons.";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $user_id]);
        logActivity('admin_action', "Updated role for user ID $user_id to $role");
        $success = "User role updated successfully.";
    }
}

// Handle ban/unban
if (isset($_POST['toggle_ban'])) {
    $user_id = (int)$_POST['user_id'];
    $status = (int)$_POST['ban_status'];
    
    if ($user_id === (int)$_SESSION['user_id']) {
        $error = "You cannot ban yourself.";
    } elseif ($user_id === 1 && $status === 1) {
        $error = "The root Super Admin cannot be banned.";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET is_banned = ? WHERE id = ?");
        $stmt->execute([$status, $user_id]);
        logActivity('admin_action', ($status ? "Banned" : "Unbanned") . " user ID $user_id");
        $success = $status ? "User banned successfully." : "User unbanned successfully.";
    }
}

// Handle shadow ban
if (isset($_POST['toggle_shadow_ban'])) {
    $user_id = (int)$_POST['user_id'];
    $status = (int)$_POST['shadow_status'];
    $stmt = $pdo->prepare("UPDATE users SET shadow_banned = ? WHERE id = ?");
    $stmt->execute([$status, $user_id]);
    logActivity('admin_action', ($status ? "Shadow Banned" : "Removed Shadow Ban from") . " user ID $user_id");
    $success = $status ? "User shadow banned." : "Shadow ban removed.";
}

// Handle force logout
if (isset($_POST['force_logout'])) {
    $user_id = (int)$_POST['user_id'];
    // Setting session_token to NULL or a random string forces the user to re-login based on config.php logic
    $new_token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare("UPDATE users SET session_token = ? WHERE id = ?");
    $stmt->execute([$new_token, $user_id]);
    logActivity('admin_action', "Forced logout for user ID $user_id");
    $success = "User has been forced to logout.";
}

// Handle password reset
if (isset($_POST['reset_password'])) {
    $user_id = (int)$_POST['user_id'];
    $temp_pass = bin2hex(random_bytes(4)); // 8 chars
    $hashed = password_hash($temp_pass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed, $user_id]);
    logActivity('admin_action', "Reset password for user ID $user_id");
    $success = "Password reset to: " . $temp_pass;
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

$reportsCount = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Control - ShreeBitu Admin</title>
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
        <div class="max-w-[1200px] mx-auto">
            <header class="mb-10 flex justify-between items-end">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">User Moderation</h2>
                <p class="text-slate-500 mt-1 font-medium">Full granular control over registered accounts and their permissions.</p>
            </div>
            <div class="bg-indigo-900 text-white px-6 py-2.5 rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-lg">
                <?php echo count($users); ?> Total Users
            </div>
        </header>

        <?php if ($error): ?>
            <div class="bg-red-900 text-white px-6 py-4 rounded-3xl mb-8 flex items-center gap-4 shadow-xl shadow-red-100">
                <span class="material-symbols-outlined text-sm">warning</span>
                <span class="text-xs font-black uppercase tracking-widest"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-indigo-900 text-white px-6 py-4 rounded-3xl mb-8 flex items-center gap-4 shadow-xl shadow-indigo-100">
                <span class="material-symbols-outlined text-sm">info</span>
                <span class="text-xs font-black uppercase tracking-widest"><?php echo $success; ?></span>
            </div>
        <?php endif; ?>

        <div class="card-white overflow-hidden">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left whitespace-nowrap min-w-[800px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">User Profile</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Account Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Last Active</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Moderation Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-slate-50/50 transition-all group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center font-black text-indigo-900 border border-slate-200">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($user['username']); ?></p>
                                        <p class="text-[11px] text-slate-500 font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest border <?php echo $user['role'] === 'admin' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-slate-100 text-slate-600 border-slate-200'; ?>">
                                        <?php echo $user['role']; ?>
                                    </span>
                                    <?php if ($user['is_banned']): ?>
                                        <span class="px-2 py-0.5 rounded-lg text-[9px] font-black bg-red-100 text-red-600 border border-red-200 uppercase">BANNED</span>
                                    <?php endif; ?>
                                    <?php if ($user['shadow_banned']): ?>
                                        <span class="px-2 py-0.5 rounded-lg text-[9px] font-black bg-amber-100 text-amber-600 border border-amber-200 uppercase">SHADOW</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <p class="text-[11px] font-bold text-slate-400"><?php echo date('d M, H:i', strtotime($user['last_active'])); ?></p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <form method="POST" class="flex gap-1">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        
                                        <!-- Shadow Ban Toggle -->
                                        <input type="hidden" name="shadow_status" value="<?php echo $user['shadow_banned'] ? 0 : 1; ?>">
                                        <button type="submit" name="toggle_shadow_ban" class="p-1.5 rounded-lg hover:bg-amber-50 text-amber-500 transition-all" title="Shadow Ban (Hide user uploads from others)">
                                            <span class="material-symbols-outlined text-[18px]">visibility_off</span>
                                        </button>
                                        
                                        <!-- Ban Toggle -->
                                        <input type="hidden" name="ban_status" value="<?php echo $user['is_banned'] ? 0 : 1; ?>">
                                        <button type="submit" name="toggle_ban" class="p-1.5 rounded-lg hover:bg-red-50 text-red-600 transition-all" title="Full Ban">
                                            <span class="material-symbols-outlined text-[18px]"><?php echo $user['is_banned'] ? 'person_check' : 'person_off'; ?></span>
                                        </button>
                                    </form>

                                    <!-- More Actions Dropdown (Using simple click-reveal for UI) -->
                                    <div class="relative group/actions">
                                        <button class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-400">
                                            <span class="material-symbols-outlined text-[18px]">more_vert</span>
                                        </button>
                                        <div class="absolute right-0 top-full mt-1 w-48 bg-white rounded-2xl shadow-2xl border border-slate-100 opacity-0 invisible group-hover/actions:opacity-100 group-hover/actions:visible transition-all z-50 p-2 text-left">
                                            <form method="POST">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="force_logout" class="w-full flex items-center gap-3 px-3 py-2 text-[11px] font-bold text-slate-600 hover:bg-slate-50 rounded-xl">
                                                    <span class="material-symbols-outlined text-sm">logout</span> Force Logout
                                                </button>
                                                <button type="submit" name="reset_password" onclick="return confirm('Reset this user\'s password?')" class="w-full flex items-center gap-3 px-3 py-2 text-[11px] font-bold text-indigo-600 hover:bg-indigo-50 rounded-xl">
                                                    <span class="material-symbols-outlined text-sm">lock_reset</span> Reset Password
                                                </button>
                                            </form>
                                            <div class="h-px bg-slate-50 my-1"></div>
                                            <?php if ($user['id'] != $_SESSION['user_id'] && $user['id'] != 1): ?>
                                                <form method="POST">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="role" value="<?php echo $user['role'] === 'admin' ? 'user' : 'admin'; ?>">
                                                    <button type="submit" name="update_role" class="w-full flex items-center gap-3 px-3 py-2 text-[11px] font-bold text-slate-600 hover:bg-slate-50 rounded-xl">
                                                        <span class="material-symbols-outlined text-sm">admin_panel_settings</span> Toggle Admin
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <div class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center italic">
                                                    Root Protection Active
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
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
