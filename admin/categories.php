<?php
require_once '../config.php';
require_once '../db.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';
$error = isset($_GET['error']) ? sanitizeInput($_GET['error']) : '';

// Handle Create/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    verifyCsrfToken($_POST['csrf_token'] ?? '');
    $name = sanitizeInput($_POST['name']);
    $icon = sanitizeInput($_POST['icon']);
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, icon = ? WHERE id = ?");
        $stmt->execute([$name, $slug, $icon, $id]);
        logActivity('category_update', "Updated category: $name (ID: $id)");
        redirect("categories.php?success=Category updated successfully.");
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)");
            $stmt->execute([$name, $slug, $icon]);
            logActivity('category_create', "Created new category: $name");
            redirect("categories.php?success=Category created successfully.");
        } catch (PDOException $e) {
            $error = "Category already exists.";
        }
    }
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    verifyCsrfToken($_POST['csrf_token'] ?? '');
    $id = (int)$_POST['id'];
    try {
        // First get the name for the log
        $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $cat_name = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            logActivity('category_delete', "Deleted category: $cat_name (ID: $id)");
            redirect("categories.php?success=Category deleted successfully.");
        } else {
            $error = "Category not found or already deleted.";
        }
    } catch (PDOException $e) {
        $error = "Cannot delete category. It might be in use.";
    }
}

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

$reportsCount = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management - Admin Panel</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">

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
        body { font-family: 'Inter', sans-serif !important; background-color: #f7f9fb !important; color: #1e293b; }
        .sidebar { background: var(--slate-50) !important; border-right: 1px solid var(--slate-200); }
        .nav-item { transition: all 0.2s; font-weight: 500; font-size: 14px; color: var(--slate-600); }
        .nav-item:hover { background-color: var(--slate-100); color: var(--primary); }
        .nav-item.active { background-color: #ffffff; color: var(--primary); border-right: 3px solid var(--primary); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .header-blur { background:#ffffff !important; border-bottom:1px solid var(--slate-200); }
        .card-white { background: #ffffff; border-radius: 24px; border: 1px solid var(--slate-200); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .main-scroll::-webkit-scrollbar { width: 5px; }
        .main-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-[#1d1d1f]">

<?php include 'components/sidebar.php'; ?>
<?php include 'components/mobile_sidebar.php'; ?>

<!-- Main Area -->
<main class="flex-1 flex flex-col h-full bg-[#f7f9fb] relative z-10 w-full overflow-hidden">
    <?php include 'components/header.php'; ?>

    <div class="flex-1 overflow-y-auto px-4 md:px-8 lg:px-10 pb-12 main-scroll">
        <div class="max-w-[1000px] mx-auto mt-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Category Form -->
            <div class="lg:col-span-1">
                <div class="card-white p-6 sticky top-0">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6" id="formTitle">Add New Category</h3>
                    
                    <?php if ($error): ?>
                        <div class="bg-red-50 text-red-700 p-3 rounded-xl mb-4 text-xs font-bold border border-red-100"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="bg-indigo-50 text-indigo-700 p-3 rounded-xl mb-4 text-xs font-bold border border-indigo-100"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-4" id="catForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="id" id="catId" value="0">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Category Name</label>
                            <input type="text" name="name" id="catName" required placeholder="e.g. Graphic Tools" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-indigo-600 transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Icon (Material Icon Name)</label>
                            <input type="text" name="icon" id="catIcon" required placeholder="e.g. brush" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-indigo-600 transition-all">
                            <p class="text-[10px] text-slate-400 mt-1 font-medium italic">Use Material Symbols names (e.g. apps, folder, android)</p>
                        </div>
                        <div class="pt-2 flex gap-2">
                            <button type="submit" name="save_category" class="flex-1 bg-indigo-900 text-white py-2.5 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-800 transition-all">Save Category</button>
                            <button type="button" onclick="resetForm()" class="px-4 py-2.5 bg-slate-100 text-slate-500 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Clear</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Category List -->
            <div class="lg:col-span-2">
                <div class="card-white overflow-hidden">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100">
                                <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider">Icon</th>
                                <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider">Category Name</th>
                                <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach ($categories as $cat): ?>
                                <tr class="hover:bg-slate-50/30 transition-all group">
                                    <td class="px-6 py-4">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                            <span class="material-symbols-outlined"><?php echo htmlspecialchars($cat['icon']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($cat['name']); ?></p>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick="editCategory(<?php echo $cat['id']; ?>, '<?php echo addslashes($cat['name']); ?>', '<?php echo addslashes($cat['icon']); ?>')" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all" title="Edit">
                                                <span class="material-symbols-outlined">edit</span>
                                            </button>
                                            <form method="POST" onsubmit="return confirm('Delete this category?')" style="display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                                <button type="submit" name="delete_category" class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition-all" title="Delete">
                                                    <span class="material-symbols-outlined">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
    function editCategory(id, name, icon) {
        document.getElementById('catId').value = id;
        document.getElementById('catName').value = name;
        document.getElementById('catIcon').value = icon;
        document.getElementById('formTitle').innerText = 'Edit Category: ' + name;
        document.getElementById('catName').focus();
        // Scroll to form on mobile
        if(window.innerWidth < 1024) {
            document.getElementById('catForm').scrollIntoView({ behavior: 'smooth' });
        }
    }
    function resetForm() {
        document.getElementById('catId').value = 0;
        document.getElementById('catName').value = '';
        document.getElementById('catIcon').value = 'apps';
        document.getElementById('formTitle').innerText = 'Add New Category';
    }
</script>

</body>
</html>
