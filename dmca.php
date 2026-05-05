<?php
require_once 'includes/init.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_url = sanitizeInput($_POST['app_url']);
    $email = sanitizeInput($_POST['email']);
    $details = sanitizeInput($_POST['details']);
    $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;

    if (empty($app_url) || empty($email) || empty($details)) {
        $error = "Please fill in all required fields.";
    } else {
        // Find app_id from URL if possible
        $app_id = 0;
        // Example URL: https://shreebitu.in/post/whatsapp-apk-download
        if (preg_match('/post\/([a-z0-9-]+)/', $app_url, $matches)) {
            $slug = $matches[1];
            $stmt = $pdo->prepare("SELECT id FROM apps WHERE slug = ?");
            $stmt->execute([$slug]);
            $app = $stmt->fetch();
            if ($app) {
                $app_id = $app['id'];
            }
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO reports (app_id, user_id, reason, details) VALUES (?, ?, ?, ?)");
            $reason = "DMCA Takedown Request - From: $email";
            $stmt->execute([$app_id ?: 0, $user_id, $reason, "Target URL: $app_url\n\n$details"]);
            $success = "Your DMCA notice has been submitted. Our legal team will review it within 48-72 hours.";
        } catch (PDOException $e) {
            $error = "Failed to submit report. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMCA Compliance - MODFIRE</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(241, 245, 249, 1);
        }
    </style>
</head>

<body class="bg-[#f8fafc] text-slate-900 flex h-screen overflow-hidden">

    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full relative z-10 w-full overflow-hidden">
        <?php
        $page_title = 'DMCA Policy';
        include 'includes/header.php';
        ?>

        <div class="flex-1 overflow-y-auto px-4 md:px-10 py-12 main-scroll">
            <div class="max-w-[900px] mx-auto">

                <header class="mb-12">
                    <div
                        class="w-16 h-16 bg-red-50 text-red-600 rounded-3xl flex items-center justify-center mb-6 shadow-sm border border-red-100">
                        <span class="material-symbols-outlined text-3xl">gavel</span>
                    </div>
                    <h2 class="text-4xl font-black text-slate-900 tracking-tight mb-4">DMCA Compliance Notice</h2>
                    <p class="text-slate-500 text-lg font-medium leading-relaxed">
                        MODFIRE respects the intellectual property rights of others and expects its users to do the
                        same.
                    </p>
                </header>

                <?php if ($success): ?>
                    <div
                        class="mb-8 p-6 bg-emerald-50 border border-emerald-100 rounded-3xl flex items-center gap-4 text-emerald-700">
                        <span class="material-symbols-outlined">check_circle</span>
                        <span class="text-sm font-bold uppercase tracking-widest"><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="mb-8 p-6 bg-red-50 border border-red-100 rounded-3xl flex items-center gap-4 text-red-700">
                        <span class="material-symbols-outlined">warning</span>
                        <span class="text-sm font-bold uppercase tracking-widest"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 gap-8">
                    <section class="glass-card p-10 rounded-[40px] shadow-sm">
                        <h3 class="text-xl font-bold mb-6 flex items-center gap-3">
                            <span class="material-symbols-outlined text-indigo-600">info</span>
                            General Information
                        </h3>
                        <div class="space-y-4 text-slate-600 leading-relaxed text-sm">
                            <p>It is our policy to respond to any infringement notices and take appropriate actions
                                under the Digital Millennium Copyright Act ("DMCA") and other applicable intellectual
                                property laws.</p>
                            <p>If your copyrighted material has been posted on MODFIRE or if links to your copyrighted
                                material are returned through our search engine and you want this material removed, you
                                must provide a written communication that details the information listed in the
                                following section.</p>
                        </div>
                    </section>

                    <section id="report"
                        class="bg-white border border-slate-200 p-10 rounded-[40px] shadow-xl shadow-slate-200/50">
                        <h3 class="text-xl font-bold mb-2 flex items-center gap-3">
                            <span class="material-symbols-outlined text-red-600">flag</span>
                            Submit Takedown Request
                        </h3>
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-10">Legal Submission
                            Form</p>

                        <form method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label
                                        class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Your
                                        Full Name</label>
                                    <input type="text" name="name" required
                                        class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-600/5 focus:border-indigo-600/30 outline-none transition-all">
                                </div>
                                <div>
                                    <label
                                        class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Authorized
                                        Email</label>
                                    <input type="email" name="email" required
                                        class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-600/5 focus:border-indigo-600/30 outline-none transition-all">
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Infringing
                                    Material URL</label>
                                <input type="url" name="app_url" placeholder="https://shreebitu.in/post/example-app"
                                    required
                                    class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-600/5 focus:border-indigo-600/30 outline-none transition-all font-mono text-[13px]">
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Infringement
                                    Details & Evidence</label>
                                <textarea name="details" rows="6" required
                                    class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-3xl text-sm focus:ring-4 focus:ring-indigo-600/5 focus:border-indigo-600/30 outline-none transition-all leading-relaxed"
                                    placeholder="Please provide evidence of copyright ownership..."></textarea>
                            </div>

                            <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <input type="checkbox" required class="mt-1 w-4 h-4 accent-indigo-600">
                                <p class="text-[11px] text-slate-500 font-medium leading-relaxed">
                                    I have a good faith belief that use of the copyrighted materials described above as
                                    allegedly infringing is not authorized by the copyright owner, its agent, or the
                                    law.
                                </p>
                            </div>

                            <button type="submit"
                                class="w-full py-4 bg-indigo-900 text-white rounded-2xl font-black uppercase tracking-[0.2em] text-xs shadow-xl shadow-indigo-100 hover:bg-indigo-950 transition-all active:scale-95 flex items-center justify-center gap-3">
                                <span class="material-symbols-outlined text-sm">send</span>
                                Submit Legal Notice
                            </button>
                        </form>
                    </section>
                </div>

                <?php include 'includes/footer.php'; ?>
            </div>
        </div>
    </main>
</body>

</html>