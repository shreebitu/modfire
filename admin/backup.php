<?php
require_once '../config.php';
require_once '../db.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

$success = '';
$error = '';
$backup_dir = '../backups/';

// Handle Backup Generation
if (isset($_POST['create_backup'])) {
    $tables = array();
    $result = $pdo->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    $sql = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\nSET time_zone = \"+00:00\";\n\n";

    foreach ($tables as $table) {
        $res = $pdo->query("SHOW CREATE TABLE $table");
        $row = $res->fetch(PDO::FETCH_NUM);
        $sql .= "\n\n" . $row[1] . ";\n\n";

        $res = $pdo->query("SELECT * FROM $table");
        while ($row = $res->fetch(PDO::FETCH_NUM)) {
            $sql .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < count($row); $j++) {
                $row[$j] = addslashes($row[$j]);
                $row[$j] = str_replace("\n", "\\n", $row[$j]);
                if (isset($row[$j])) {
                    $sql .= '"' . $row[$j] . '"';
                } else {
                    $sql .= '""';
                }
                if ($j < (count($row) - 1)) {
                    $sql .= ',';
                }
            }
            $sql .= ");\n";
        }
    }

    $backup_name = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    if (!is_dir($backup_dir))
        mkdir($backup_dir, 0755, true);

    file_put_contents($backup_dir . $backup_name, $sql);
    logActivity('admin_action', "Generated database backup: $backup_name");
    $success = "Backup generated: $backup_name";
}

// Handle Backup Deletion
if (isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    $filepath = $backup_dir . $file;
    if (file_exists($filepath) && is_file($filepath) && pathinfo($filepath, PATHINFO_EXTENSION) === 'sql') {
        if (unlink($filepath)) {
            logActivity('admin_action', "Deleted database backup: $file");
            $success = "Backup deleted successfully.";
        } else {
            $error = "Failed to delete backup.";
        }
    } else {
        $error = "Invalid backup file.";
    }
}

// Fetch existing backups
$backups = is_dir($backup_dir) ? array_diff(scandir($backup_dir), array('..', '.')) : [];
rsort($backups);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup & Recovery - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary: #1f108e;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fb;
            color: #1e293b;
        }

        .sidebar {
            background: var(--slate-50);
            border-right: 1px solid var(--slate-200);
        }

        .nav-item {
            transition: all 0.2s;
            font-weight: 500;
            font-size: 14px;
            color: #64748b;
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

        .card-white {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid var(--slate-200);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body class="flex h-screen overflow-hidden">

    <?php include 'components/sidebar.php'; ?>
    <?php include 'components/mobile_sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-full bg-[#f8fafc] relative z-10 w-full overflow-hidden">
        <?php include 'components/header.php'; ?>

        <div class="flex-1 overflow-y-auto p-6 lg:p-10 main-scroll">
            <div class="max-w-[800px] mx-auto">
                <header class="mb-10">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Disaster Recovery</h2>
                    <p class="text-slate-500 mt-1 font-medium text-sm">Secure your platform data with manual and
                        automated database snapshots.</p>
                </header>

                <?php if ($success): ?>
                    <div
                        class="bg-indigo-900 text-white px-6 py-4 rounded-3xl mb-8 flex items-center gap-4 shadow-xl shadow-indigo-100">
                        <span class="material-symbols-outlined text-sm">cloud_done</span>
                        <span class="text-xs font-black uppercase tracking-widest"><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div
                        class="bg-red-600 text-white px-6 py-4 rounded-3xl mb-8 flex items-center gap-4 shadow-xl shadow-red-100">
                        <span class="material-symbols-outlined text-sm">error</span>
                        <span class="text-xs font-black uppercase tracking-widest"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 gap-8">
                    <div class="card-white p-8 bg-slate-900 border-slate-800 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8 opacity-10">
                            <span
                                class="material-symbols-outlined text-[80px] text-white">settings_backup_restore</span>
                        </div>
                        <div class="relative z-10">
                            <h3 class="text-sm font-black text-white uppercase tracking-widest mb-2">Create New Snapshot
                            </h3>
                            <p class="text-slate-400 text-xs font-medium mb-8">This will generate a full .sql backup of
                                all your tables and data.</p>
                            <form method="POST">
                                <button type="submit" name="create_backup"
                                    class="bg-indigo-500 hover:bg-indigo-400 text-white px-8 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-indigo-500/20 active:scale-95">Generate
                                    Backup Now</button>
                            </form>
                        </div>
                    </div>

                    <div class="card-white overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Available
                                Backups</h3>
                        </div>
                        <div class="divide-y divide-slate-50">
                            <?php if (count($backups) > 0): ?>
                                <?php foreach ($backups as $file): ?>
                                    <div class="flex items-center justify-between p-6 hover:bg-slate-50/50 transition-all">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400">
                                                <span class="material-symbols-outlined">description</span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-900">
                                                    <?php echo htmlspecialchars($file); ?></p>
                                                <p
                                                    class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">
                                                    <?php echo round(filesize($backup_dir . $file) / 1024, 2); ?> KB</p>
                                            </div>
                                        </div>
                                        <div class="flex gap-2">
                                            <a href="../backups/<?php echo $file; ?>" download
                                                class="px-4 py-2 bg-slate-100 hover:bg-indigo-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Download</a>
                                            <a href="backup.php?delete=<?php echo $file; ?>"
                                                onclick="return confirm('Delete this backup permanently?')"
                                                class="px-4 py-2 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Delete</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div
                                    class="p-12 text-center text-slate-400 font-bold uppercase text-[11px] tracking-widest">
                                    No backups found.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>

</html>