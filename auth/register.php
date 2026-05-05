<?php
/**
 * ============================================================
 * USER REGISTRATION PAGE
 * ============================================================
 * Purpose: Creates new user accounts with validation
 * Input:   POST: username, email, password, confirm_password, csrf_token
 * Output:  On success → show success message with login link
 *          On failure → show error with form data preserved
 * Connects to: includes/init.php, users table, site_settings
 * ============================================================
 */
require_once '../includes/init.php';

// If user is already logged in, send them to the homepage
if (isLoggedIn()) {
    redirect('../index.php');
}

// Initialize messages and form data (to preserve input on error)
$error = '';
$success = '';
$form_data = ['username' => '', 'email' => ''];

// ── Handle Registration Form Submission ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if registration is enabled in admin settings
    if (($site_settings['registration_enabled'] ?? '1') == '0') {
        $error = "Registrations are currently disabled by the administrator.";
    } else {
        // Step 1: Verify CSRF token
        verifyCsrfToken($_POST['csrf_token'] ?? '');

        // Step 2: Sanitize and collect form inputs
        $username = sanitizeInput($_POST['username']);
        $email    = sanitizeInput($_POST['email']);
        $password = $_POST['password'];         // Raw — needs to be hashed, not sanitized
        $confirm  = $_POST['confirm_password'];  // For matching check only
        $form_data = ['username' => $username, 'email' => $email]; // Preserve for re-display

        // Step 3: Validate all inputs with specific error messages
        if (empty($username) || empty($email) || empty($password)) {
            $error = "All fields are required.";
        } elseif (strlen($username) < 3) {
            $error = "Username must be at least 3 characters long.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif ($password !== $confirm) {
            $error = "Passwords do not match. Please try again.";
        } else {
            // Step 4: Check for duplicate email or username in the database
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            if ($stmt->fetch()) {
                logActivity('register_failed', 'Duplicate email or username: ' . $email);
                $error = "Email or username is already registered.";
            } else {
                // Step 5: Hash the password using bcrypt (PASSWORD_DEFAULT)
                // This creates a secure one-way hash that can't be reversed
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Step 6: Insert the new user into the database
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                if ($stmt->execute([$username, $email, $hashed_password])) {
                    // Log successful registration
                    logActivity('register_success', 'New user: ' . $username);
                    $success = "Account created! You can now sign in.";
                    // Clear form data on success
                    $form_data = ['username' => '', 'email' => ''];
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - MODFIRE</title>
    <meta name="description" content="Create a free MODFIRE account to submit apps, save favorites, and more.">
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
        .strength-bar { height: 3px; border-radius: 99px; transition: all 0.3s ease; }
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
        <div class="w-9 h-9 bg-white/80 backdrop-blur rounded-full flex items-center justify-center border border-slate-200 shadow-sm group-hover:border-indigo-200 group-hover:shadow-indigo-100 transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        </div>
        <span class="hidden sm:block">Back to Home</span>
    </a>

    <div class="w-full max-w-[460px]">

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

            <h1 class="text-2xl font-black text-slate-900 mb-1 tracking-tight">Create Account</h1>
            <p class="text-slate-400 text-sm font-medium mb-8">Join thousands of users and start downloading today.</p>

            <!-- Alerts -->
            <?php if ($error): ?>
            <div class="flex items-center gap-3 bg-red-50 border border-red-100 text-red-600 px-4 py-3.5 rounded-2xl mb-6">
                <span class="material-symbols-outlined text-[20px] shrink-0">error</span>
                <p class="text-sm font-semibold"><?php echo htmlspecialchars($error); ?></p>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-3.5 rounded-2xl mb-6">
                <span class="material-symbols-outlined text-[20px] shrink-0">check_circle</span>
                <div>
                    <p class="text-sm font-bold">Account Created!</p>
                    <p class="text-xs font-medium mt-0.5">You can now <a href="login.php" class="underline font-bold">sign in to your account</a>.</p>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5" id="registerForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <!-- Username -->
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Username</label>
                    <div class="relative">
                        <input type="text" name="username" id="username"
                            value="<?php echo htmlspecialchars($form_data['username']); ?>"
                            autocomplete="username" placeholder="john_doe" required
                            class="input-field pr-11">
                        <span class="material-symbols-outlined absolute right-3.5 top-3 text-slate-300 text-[20px]">person</span>
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Email Address</label>
                    <div class="relative">
                        <input type="email" name="email" id="email"
                            value="<?php echo htmlspecialchars($form_data['email']); ?>"
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
                            autocomplete="new-password" placeholder="Min. 6 characters" required
                            oninput="checkStrength(this.value)"
                            class="input-field pr-11">
                        <button type="button" onclick="toggleVisibility('password', this)"
                            class="absolute right-3.5 top-3 text-slate-300 hover:text-slate-500 transition-colors">
                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                        </button>
                    </div>
                    <!-- Strength Indicator -->
                    <div class="mt-2 space-y-1.5" id="strengthArea" style="display:none">
                        <div class="flex gap-1.5">
                            <div class="strength-bar flex-1 bg-slate-100" id="s1"></div>
                            <div class="strength-bar flex-1 bg-slate-100" id="s2"></div>
                            <div class="strength-bar flex-1 bg-slate-100" id="s3"></div>
                            <div class="strength-bar flex-1 bg-slate-100" id="s4"></div>
                        </div>
                        <p class="text-[11px] font-bold text-slate-400" id="strengthLabel"></p>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Confirm Password</label>
                    <div class="relative">
                        <input type="password" name="confirm_password" id="confirm_password"
                            autocomplete="new-password" placeholder="Re-enter password" required
                            oninput="checkMatch()"
                            class="input-field pr-11">
                        <button type="button" onclick="toggleVisibility('confirm_password', this)"
                            class="absolute right-3.5 top-3 text-slate-300 hover:text-slate-500 transition-colors">
                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                        </button>
                    </div>
                    <p id="matchMsg" class="text-[11px] font-bold mt-1.5 hidden"></p>
                </div>

                <button type="submit" class="btn-primary mt-2">
                    Create My Account
                </button>
            </form>

            <p class="text-center text-sm text-slate-400 font-medium mt-6">
                Already have an account?
                <a href="login.php" class="text-indigo-600 font-bold hover:text-indigo-700 transition-colors ml-1">Sign in</a>
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
        function toggleVisibility(fieldId, btn) {
            const input = document.getElementById(fieldId);
            const icon = btn.querySelector('.material-symbols-outlined');
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        }

        function checkStrength(val) {
            const area = document.getElementById('strengthArea');
            const bars = ['s1','s2','s3','s4'].map(id => document.getElementById(id));
            const label = document.getElementById('strengthLabel');
            if (!val) { area.style.display = 'none'; return; }
            area.style.display = 'block';

            let score = 0;
            if (val.length >= 6) score++;
            if (val.length >= 10) score++;
            if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const colors = ['#ef4444','#f97316','#eab308','#22c55e'];
            const labels = ['Weak','Fair','Good','Strong'];
            bars.forEach((b, i) => b.style.background = i < score ? colors[score - 1] : '#e2e8f0');
            label.textContent = labels[score - 1] || '';
            label.style.color = score > 0 ? colors[score - 1] : '#94a3b8';
        }

        function checkMatch() {
            const p = document.getElementById('password').value;
            const c = document.getElementById('confirm_password').value;
            const msg = document.getElementById('matchMsg');
            if (!c) { msg.classList.add('hidden'); return; }
            msg.classList.remove('hidden');
            if (p === c) {
                msg.textContent = '✓ Passwords match';
                msg.style.color = '#22c55e';
            } else {
                msg.textContent = '✗ Passwords do not match';
                msg.style.color = '#ef4444';
            }
        }
    </script>
</body>
</html>
