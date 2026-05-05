<?php
/**
 * ============================================================
 * USER LOGIN PAGE
 * ============================================================
 * Purpose: Authenticates users via email + password
 * Input:   POST: email, password, csrf_token
 * Output:  On success → redirect to dashboard/homepage
 *          On failure → show error message
 * Connects to: includes/init.php, users table
 * ============================================================
 */
require_once '../includes/init.php';

// If user is already logged in, send them to the homepage
// (prevents seeing login page while authenticated)
if (isLoggedIn()) {
    redirect('../index.php');
}

// Initialize error message (empty = no error)
$error = '';

// ── Handle Login Form Submission ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Verify the CSRF token to prevent cross-site form attacks
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    // Step 2: Sanitize the email input (strip HTML/JS)
    // NOTE: Password is NOT sanitized — it needs to be compared raw against the hash
    $email    = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    // Step 3: Look up the user by email (using prepared statement to prevent SQL injection)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Step 4: Verify the password against the bcrypt hash stored in the database
    if ($user && password_verify($password, $user['password'])) {
        // Step 4a: Check if the user account is banned
        if ($user['is_banned']) {
            $error = "Your account has been suspended. Contact support@shreebitu.in for help.";
        } else {
            // Step 5: Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);

            // Step 6: Create a new session token and store it in BOTH session and database
            // This allows admins to invalidate sessions remotely by changing the DB token
            $token = bin2hex(random_bytes(32));
            $_SESSION['session_token'] = $token;
            $stmt = $pdo->prepare("UPDATE users SET session_token = ? WHERE id = ?");
            $stmt->execute([$token, $user['id']]);

            // Step 7: Store user data in the session for quick access across pages
            $_SESSION['user_id']     = $user['id'];
            $_SESSION['username']    = $user['username'];
            $_SESSION['role']        = $user['role'];
            $_SESSION['email']       = $user['email'];
            $_SESSION['profile_pic'] = $user['profile_pic'];

            // Step 8: Log the successful login for audit trail
            logActivity('login', 'User logged in successfully');

            // Step 9: Redirect based on role or requested page
            $redirect = $_GET['redirect'] ?? '';
            if ($user['role'] === 'admin') {
                // Admins go straight to the admin dashboard
                redirect('../admin/dashboard.php');
            } elseif ($redirect) {
                // If user was redirected here from a protected page, send them back
                redirect('../' . ltrim($redirect, '/'));
            } else {
                // Default: go to homepage
                redirect('../index.php');
            }
        }
    } else {
        // Step 4 failed: Log the failed attempt (helps detect brute force attacks)
        logActivity('login_failed', 'Failed login for: ' . $email);
        $error = "Invalid email or password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - MODFIRE</title>
    <meta name="description" content="Sign in to your MODFIRE account to manage your downloads and preferences.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="<?php echo $assets_url; ?>images/logo.png">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-auth {
            background-color: #f0f4ff;
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(99, 102, 241, 0.12) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 60% 80%, rgba(59, 130, 246, 0.08) 0%, transparent 50%);
            min-height: 100vh;
        }
        .input-field {
            width: 100%;
            padding: 12px 44px 12px 16px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 500;
            color: #0f172a;
            outline: none;
            transition: all 0.2s ease;
        }
        .input-field:focus {
            border-color: #6366f1;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.08);
        }
        .input-field::placeholder { color: #94a3b8; }
        .btn-primary {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 8px 24px -4px rgba(99, 102, 241, 0.4);
            letter-spacing: 0.02em;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            box-shadow: 0 12px 30px -4px rgba(99, 102, 241, 0.5);
            transform: translateY(-1px);
        }
        .btn-primary:active { transform: scale(0.98); }
        .card-glass {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.8);
        }
    </style>
</head>
<body class="bg-auth flex flex-col items-center justify-center min-h-screen p-4 py-10">

    <!-- Back Button -->
    <a href="../index.php" class="fixed top-5 left-5 flex items-center gap-2 text-slate-500 hover:text-indigo-600 transition-all group text-sm font-semibold z-50">
        <div class="w-9 h-9 bg-white/80 backdrop-blur rounded-full flex items-center justify-center border border-slate-200 shadow-sm group-hover:border-indigo-200 transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        </div>
        <span class="hidden sm:block">Back to Home</span>
    </a>

    <div class="w-full max-w-[420px]">

        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="../index.php" class="inline-flex items-center gap-2 group">
                <div class="w-12 h-12 bg-white rounded-2xl shadow-md flex items-center justify-center overflow-hidden border border-slate-100 group-hover:scale-105 transition-transform">
                    <img src="<?php echo $assets_url; ?>images/logo.png" class="w-full h-full object-contain mix-blend-multiply" alt="Logo">
                </div>
                <div class="text-2xl font-black leading-none">
                    <span class="text-indigo-950">MOD</span><span class="text-orange-500">FIRE</span>
                </div>
            </a>
            <p class="text-slate-400 text-sm font-medium mt-3">Premium App Marketplace</p>
        </div>

        <!-- Card -->
        <div class="card-glass rounded-[28px] shadow-2xl shadow-indigo-100/50 p-8">

            <h1 class="text-2xl font-black text-slate-900 mb-1 tracking-tight">Welcome Back</h1>
            <p class="text-slate-400 text-sm font-medium mb-8">Sign in to continue to your account.</p>

            <!-- Error Alert -->
            <?php if ($error): ?>
            <div class="flex items-center gap-3 bg-red-50 border border-red-100 text-red-600 px-4 py-3.5 rounded-2xl mb-6">
                <span class="material-symbols-outlined text-[20px] shrink-0">error</span>
                <p class="text-sm font-semibold"><?php echo htmlspecialchars($error); ?></p>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'session_expired'): ?>
            <div class="flex items-center gap-3 bg-amber-50 border border-amber-100 text-amber-700 px-4 py-3.5 rounded-2xl mb-6">
                <span class="material-symbols-outlined text-[20px] shrink-0">info</span>
                <p class="text-sm font-semibold">Your session expired. Please sign in again.</p>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <!-- Email -->
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Email Address</label>
                    <div class="relative">
                        <input type="email" name="email" id="email"
                            autocomplete="email" placeholder="name@example.com" required
                            class="input-field pr-11">
                        <span class="material-symbols-outlined absolute right-3.5 top-3 text-slate-300 text-[20px]">mail</span>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password"
                            autocomplete="current-password" placeholder="••••••••" required
                            class="input-field pr-11">
                        <button type="button" onclick="toggleVisibility()"
                            class="absolute right-3.5 top-3 text-slate-300 hover:text-slate-500 transition-colors">
                            <span class="material-symbols-outlined text-[20px]" id="eyeIcon">visibility</span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary mt-2">
                    Sign In to MODFIRE
                </button>
            </form>

            <p class="text-center text-sm text-slate-400 font-medium mt-6">
                Don't have an account?
                <a href="register.php" class="text-indigo-600 font-bold hover:text-indigo-700 transition-colors ml-1">Create one free</a>
            </p>
        </div>

        <!-- Footer Links -->
        <div class="flex justify-center gap-6 mt-8 pb-4">
            <a href="../about.php" class="text-[10px] font-bold text-slate-400 hover:text-indigo-600 transition-colors uppercase tracking-widest">About</a>
            <a href="../privacy.php" class="text-[10px] font-bold text-slate-400 hover:text-indigo-600 transition-colors uppercase tracking-widest">Privacy</a>
            <a href="../dmca.php" class="text-[10px] font-bold text-slate-400 hover:text-indigo-600 transition-colors uppercase tracking-widest">DMCA</a>
        </div>
    </div>

    <script>
        function toggleVisibility() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        }
    </script>
</body>
</html>
