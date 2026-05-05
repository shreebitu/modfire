<?php
require_once '../includes/init.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);

    // Validation
    $error = null;
    if (strlen($username) < 3) {
        $error = "Username must be at least 3 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }

    if (!$error) {
        // Check if username/email already taken by someone else
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmtCheck->execute([$username, $email, $user_id]);
        if ($stmtCheck->fetch()) {
            $error = "This username or email is already associated with another account.";
        }
    }

    if (!$error) {
        $profile_pic = $_SESSION['profile_pic'];
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $filename = 'uploads/profiles/user_' . $user_id . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], '../' . $filename)) {
                    $profile_pic = $filename;
                    $_SESSION['profile_pic'] = $profile_pic;
                }
            } else {
                $error = "Invalid file type. Only JPG, PNG and WEBP are allowed.";
            }
        }
    }

    if (!$error) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, profile_pic = ? WHERE id = ?");
            if ($stmt->execute([$username, $email, $profile_pic, $user_id])) {
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $success = "Profile updated successfully!";
                logActivity('profile_update', 'Updated profile information');
            } else {
                $error = "Database update failed. Please try again.";
            }
        } catch (PDOException $e) {
            $error = "An error occurred: " . $e->getMessage();
        }
    }
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Stats
$appCount = $pdo->prepare("SELECT COUNT(*) FROM apps WHERE user_id = ?");
$appCount->execute([$user_id]);
$total_apps = $appCount->fetchColumn();

$favCount = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
$favCount->execute([$user_id]);
$total_favs = $favCount->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - ShreeBitu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"
        rel="stylesheet">
    <link rel="icon" type="image/png" href="<?php echo $assets_url; ?>images/logo.png">

</head>

<body class="flex h-screen overflow-hidden text-[#1d1d1f]">

    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Area -->
    <main class="flex-1 flex flex-col h-full bg-[#f7f9fb] relative z-10 w-full overflow-hidden">
        <!-- Top Nav -->
        <?php 
        $page_title = 'My Profile';
        $breadcrumb_html = '<span class="font-bold text-slate-900 uppercase tracking-widest text-[11px]">User Account Dashboard</span>';
        include '../includes/header.php'; 
        ?>

        <div class="flex-1 overflow-y-auto px-4 md:px-8 lg:px-10 pb-12 main-scroll">
            <div class="max-w-5xl mx-auto py-10">

                <div class="flex flex-col md:flex-row gap-10 items-start">
                    <!-- Left: Profile Info -->
                    <div class="w-full md:w-80 space-y-6">
                        <div class="bg-white rounded-[32px] p-8 border border-slate-200 shadow-sm text-center">
                            <div class="relative inline-block mb-6">
                                <div
                                    class="w-24 h-24 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-3xl font-bold border-4 border-white shadow-xl overflow-hidden">
                                    <?php if (!empty($user['profile_pic'])): ?>
                                        <img src="../<?php echo htmlspecialchars($user['profile_pic']); ?>"
                                            class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                                <div
                                    class="absolute -bottom-1 -right-1 w-8 h-8 bg-indigo-600 border-4 border-white rounded-full flex items-center justify-center text-white">
                                    <span class="material-symbols-outlined text-[14px]">verified</span>
                                </div>
                            </div>
                            <h2 class="text-xl font-bold text-slate-900">
                                <?php echo htmlspecialchars($user['username']); ?>
                            </h2>
                            <p class="text-sm text-slate-500 font-medium">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </p>

                            <div class="mt-8 pt-8 border-t border-slate-100 grid grid-cols-2 gap-4">
                                <?php if (isAdmin()):
                                    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                                    $total_system_apps = $pdo->query("SELECT COUNT(*) FROM apps")->fetchColumn();
                                    ?>
                                    <div>
                                        <p class="text-2xl font-black text-indigo-600"><?php echo $total_users; ?></p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Total
                                            Users</p>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-black text-indigo-600"><?php echo $total_system_apps; ?></p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                                            Global Apps</p>
                                    </div>
                                <?php else: ?>
                                    <div>
                                        <p class="text-2xl font-black text-indigo-600"><?php echo $total_apps; ?></p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                                            Uploads</p>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-black text-indigo-600"><?php echo $total_favs; ?></p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                                            Favorites</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div
                            class="<?php echo isAdmin() ? 'bg-indigo-950' : 'bg-slate-900'; ?> rounded-[32px] p-8 text-white shadow-xl shadow-indigo-100">
                            <h3 class="font-bold mb-2">
                                <?php echo isAdmin() ? 'Administrative Level' : 'Member Since'; ?>
                            </h3>
                            <p class="text-sm opacity-70 mb-6">
                                <?php echo isAdmin() ? 'Super Admin Access' : date('F d, Y', strtotime($user['created_at'])); ?>
                            </p>
                            <?php if (isAdmin()): ?>
                                <a href="../admin/dashboard.php"
                                    class="w-full py-4 bg-indigo-600 hover:bg-indigo-500 rounded-2xl text-center text-xs font-black uppercase tracking-widest transition-all inline-block mb-3 shadow-lg shadow-indigo-900/50">Command
                                    Center</a>
                            <?php endif; ?>
                            <a href="../auth/logout.php"
                                class="w-full py-3 bg-white/10 hover:bg-white/20 rounded-xl text-center text-xs font-bold uppercase tracking-widest transition-all inline-block">Logout
                                Account</a>
                        </div>
                    </div>

                    <!-- Right: Form -->
                    <div class="flex-1">
                        <div class="bg-white rounded-[40px] p-8 md:p-12 border border-slate-200 shadow-sm">
                            <h3 class="text-2xl font-bold text-slate-900 mb-2">Edit Profile</h3>
                            <p class="text-slate-500 mb-10">Manage your personal information and profile picture.</p>

                            <?php if (isset($success)): ?>
                                <div
                                    class="bg-green-50 border border-green-100 text-green-700 px-6 py-4 rounded-2xl mb-8 font-medium flex items-center gap-3">
                                    <span class="material-symbols-outlined">check_circle</span>
                                    <?php echo $success; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($error)): ?>
                                <div
                                    class="bg-red-50 border border-red-100 text-red-700 px-6 py-4 rounded-2xl mb-8 font-medium flex items-center gap-3">
                                    <span class="material-symbols-outlined">error</span>
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <form action="" method="POST" enctype="multipart/form-data" class="space-y-8">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-2">
                                        <label
                                            class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Username</label>
                                        <input type="text" name="username"
                                            value="<?php echo htmlspecialchars($user['username']); ?>"
                                            class="w-full px-6 py-4 rounded-2xl border border-slate-200 focus:border-indigo-600 focus:ring-4 focus:ring-indigo-50 outline-none transition-all font-medium"
                                            required>
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Email
                                            Address</label>
                                        <input type="email" name="email"
                                            value="<?php echo htmlspecialchars($user['email']); ?>"
                                            class="w-full px-6 py-4 rounded-2xl border border-slate-200 focus:border-indigo-600 focus:ring-4 focus:ring-indigo-50 outline-none transition-all font-medium"
                                            required>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label
                                        class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Profile
                                        Picture</label>
                                    <div
                                        class="flex items-center gap-6 p-6 border-2 border-dashed border-slate-200 rounded-[32px] hover:border-indigo-400 transition-all group">
                                        <div
                                            class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-all">
                                            <span class="material-symbols-outlined text-3xl">image</span>
                                        </div>
                                        <div class="flex-1">
                                            <input type="file" name="profile_pic"
                                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
                                            <p class="text-[11px] text-slate-400 mt-2">Recommended: Square image, max
                                                2MB (JPG, PNG, WEBP)</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-6 border-t border-slate-100 flex justify-end">
                                    <button type="submit" name="update_profile"
                                        class="bg-indigo-900 text-white px-10 py-4 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-indigo-800 transition-all shadow-xl shadow-indigo-100 active:scale-95">Save
                                        Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

</body>

</html>