<?php
require_once '../config.php';
require_once '../db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($site_settings['registration_enabled'] ?? '1') == '0') {
        die("Registrations are currently disabled by the administrator.");
    }
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        logActivity('register_failed', 'Duplicate email or username: ' . $email);
        $error = "Email or Username already exists.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashed_password])) {
            logActivity('register_success', 'New user registered: ' . $username);
            $success = "Registration successful! You can now <a href='login.php' class='underline'>log in</a>.";
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - ShreeBitu Catalog</title>
    <meta name="description" content="Create a ShreeBitu account to manage your software downloads and preferences.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="<?php echo $assets_url; ?>images/logo.png">

    <style>
        body {
            font-family: 'Inter', sans-serif !important;
            background-color: #f7f9fc !important;
        }

        .input-focus:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
    </style>
</head>

<body class="min-h-screen flex flex-col justify-center items-center p-4">

    <!-- Back to Home -->
    <a href="../index.php" class="fixed top-6 left-6 flex items-center gap-2 text-gray-500 hover:text-gray-900 transition-colors group font-medium text-[14px]">
        <div class="w-9 h-9 bg-white rounded-full flex items-center justify-center border border-gray-200 shadow-sm group-hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </div>
        <span class="hidden sm:block">SHREEBITU</span>
    </a>

    <div class="w-full max-w-[480px] bg-white border border-gray-100 rounded-[24px] shadow-sm p-8 sm:p-10 my-8">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 flex items-center justify-center rotate-3 mx-auto mb-4 overflow-hidden">
                <img src="<?php echo $assets_url; ?>images/logo.png" class="w-full h-full object-contain -rotate-3 mix-blend-multiply" alt="Logo">
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Create Account</h1>
            <p class="text-gray-500 mt-2">Join ShreeBitu for the best experience</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Signup Form -->
        <form action="" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Username</label>
                <input type="text" name="username" placeholder="john_doe" required
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none input-focus transition-all text-[15px]">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Email Address</label>
                <div class="relative">
                    <input type="email" name="email" placeholder="name@example.com" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none input-focus transition-all text-[15px]">
                    <svg class="w-5 h-5 text-gray-400 absolute right-4 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                    </svg>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Password</label>
                <div class="relative">
                    <input type="password" name="password" placeholder="••••••••" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none input-focus transition-all text-[15px]">
                    <svg class="w-5 h-5 text-gray-400 absolute right-4 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 00-2 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>

            <button type="submit" class="w-full bg-[#2563eb] hover:bg-[#1d4ed8] text-white font-bold py-3.5 rounded-xl text-[16px] mt-4 shadow-sm transition-colors">
                Create Account
            </button>
        </form>

        <p class="text-center mt-8 text-sm text-gray-500 font-medium">
            Already have an account? 
            <a href="login.php" class="text-blue-600 font-bold hover:underline">Sign in instead</a>
        </p>

        <!-- Footer Links -->
        <div class="flex justify-center gap-6 mt-12 pb-4">
            <a href="../about.php" class="text-[11px] font-bold text-slate-400 hover:text-indigo-600 transition-colors uppercase tracking-widest">About</a>
            <a href="../contact.php" class="text-[11px] font-bold text-slate-400 hover:text-indigo-600 transition-colors uppercase tracking-widest">Contact</a>
            <a href="../privacy.php" class="text-[11px] font-bold text-slate-400 hover:text-indigo-600 transition-colors uppercase tracking-widest">Privacy</a>
        </div>
    </div>

</body>
</html>
