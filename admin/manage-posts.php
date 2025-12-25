<?php
require '../config/db.php';
session_start();

// 1. GATEKEEPER: Ensure only Admin/Content Manager can access
if (!isset($_SESSION['admin_id']) || !in_array($_SESSION['admin_role'], ['super_admin', 'content_manager'])) {
    header("Location: index.php");
    exit();
}

$message = "";
$msg_type = "";

// 2. HANDLE API SYNC (Triggered via AJAX)
if (isset($_GET['action']) && $_GET['action'] == 'sync') {
    header('Content-Type: application/json');
    try {
        $api_url = "https://accrachic.com/wp-json/wp/v2/posts?per_page=10";
        $response = @file_get_contents($api_url);
        
        if ($response === FALSE) {
            echo json_encode(['success' => false, 'message' => 'Could not connect to AccraChic.com API.']);
            exit();
        }

        $wp_posts = json_decode($response, true);
        $new_count = 0;

        foreach ($wp_posts as $wp_post) {
            $title = $wp_post['title']['rendered'];
            $slug  = $wp_post['slug'];
            
            // Check for duplicates
            $check = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = ?");
            $check->execute([$slug]);
            
            if (!$check->fetch()) {
                $ins = $pdo->prepare("INSERT INTO blog_posts (title, slug, rate_per_view, status) VALUES (?, ?, ?, 'active')");
                $ins->execute([$title, $slug, 0.02]);
                $new_count++;
            }
        }
        echo json_encode(['success' => true, 'new_posts' => $new_count]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

// 3. HANDLE MANUAL ADDITION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['manual_add'])) {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $rate = $_POST['rate'] ?? 0.02;

    $ins = $pdo->prepare("INSERT INTO blog_posts (title, slug, rate_per_view, status) VALUES (?, ?, ?, 'active')");
    if($ins->execute([$title, $slug, $rate])) {
        $message = "Post added successfully!";
        $msg_type = "green";
    }
}

// 4. HANDLE STATUS TOGGLE
if (isset($_GET['toggle_id'])) {
    $current = $_GET['current'];
    $new_status = ($current == 'active') ? 'inactive' : 'active';
    $stmt = $pdo->prepare("UPDATE blog_posts SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $_GET['toggle_id']]);
    header("Location: manage-posts.php?success=status");
    exit();
}

// 5. HANDLE DELETE
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: manage-posts.php?success=deleted");
    exit();
}

// Check for URL success messages
if (isset($_GET['success'])) {
    $message = ($_GET['success'] == 'status') ? "Status updated!" : "Post deleted successfully!";
    $msg_type = "blue";
}

// 6. FETCH POSTS
$posts = $pdo->query("SELECT * FROM blog_posts ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Content | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 flex min-h-screen">

    <aside class="w-64 bg-slate-900 text-white flex flex-col fixed h-full shadow-2xl">
        <div class="p-8 text-xl font-extrabold italic border-b border-slate-800 tracking-tighter">
            ACCRA<span class="text-blue-500">CHIC</span>
        </div>
        <nav class="flex-1 p-4 space-y-2 mt-4">
            <a href="dashboard.php" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            <a href="manage-posts.php" class="flex items-center gap-3 p-3 bg-blue-600 rounded-2xl text-white font-bold shadow-lg shadow-blue-900/50">
                <i data-lucide="file-text" class="w-5 h-5"></i> Manage Posts
            </a>
            <a href="payouts.php" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition">
                <i data-lucide="banknote" class="w-5 h-5"></i> Payouts
            </a>
            <a href="users.php" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition">
                <i data-lucide="users" class="w-5 h-5"></i> Affiliates
            </a>
        </nav>
        <div class="p-4 border-t border-slate-800">
            <a href="logout.php" class="flex items-center gap-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition">
                <i data-lucide="log-out" class="w-5 h-5"></i> Logout
            </a>
        </div>
    </aside>

    <main class="flex-1 ml-64 p-10">
        
        <div class="flex justify-between items-center mb-10 bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Blog Content</h1>
                <p class="text-slate-500 font-medium">Auto-sync or manually add campaign links.</p>
            </div>
            
            <button id="sync-btn" class="flex items-center gap-2 bg-slate-900 hover:bg-black text-white px-8 py-4 rounded-2xl font-bold transition-all transform active:scale-95 shadow-xl">
                <i data-lucide="refresh-cw" id="sync-icon" class="w-5 h-5"></i>
                <span id="btn-text">Sync AccraChic.com</span>
            </button>
        </div>

        <?php if($message): ?>
            <div class="mb-6 p-4 rounded-2xl bg-<?php echo $msg_type; ?>-50 text-<?php echo $msg_type; ?>-700 font-bold border border-<?php echo $msg_type; ?>-100 flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div id="sync-msg" class="hidden mb-6 p-4 rounded-2xl text-sm font-bold border"></div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm h-fit">
                <h3 class="font-bold text-slate-800 mb-6 text-lg">Add Custom Link</h3>
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="manual_add" value="1">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Post Title</label>
                        <input type="text" name="title" required class="w-full mt-1 bg-slate-50 border border-slate-200 p-4 rounded-2xl outline-none focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">URL Slug</label>
                        <input type="text" name="slug" placeholder="e.g. fashion-trends-2024" required class="w-full mt-1 bg-slate-50 border border-slate-200 p-4 rounded-2xl outline-none focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Rate (GHS)</label>
                        <input type="number" step="0.001" name="rate" value="0.02" class="w-full mt-1 bg-slate-50 border border-slate-200 p-4 rounded-2xl outline-none focus:border-blue-500 transition font-bold text-blue-600">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-extrabold py-4 rounded-2xl shadow-lg shadow-blue-100 transition-all uppercase tracking-widest text-xs">
                        Create Post
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 text-[10px] uppercase text-slate-400 font-black tracking-widest">
                            <tr>
                                <th class="p-6">Content Detail</th>
                                <th class="p-6">PPC Rate</th>
                                <th class="p-6">Status</th>
                                <th class="p-6">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if(empty($posts)): ?>
                                <tr><td colspan="4" class="p-20 text-center text-slate-400 font-medium">No posts found. Sync with AccraChic.com to begin.</td></tr>
                            <?php endif; ?>

                            <?php foreach($posts as $p): ?>
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="p-6">
                                    <div class="font-bold text-slate-800 leading-tight"><?php echo htmlspecialchars($p['title']); ?></div>
                                    <div class="text-[10px] text-slate-400 mt-1 font-mono">/<?php echo $p['slug']; ?></div>
                                </td>
                                <td class="p-6">
                                    <span class="text-sm font-black text-blue-600">GHS <?php echo number_format($p['rate_per_view'], 2); ?></span>
                                </td>
                                <td class="p-6">
                                    <a href="manage-posts.php?toggle_id=<?php echo $p['id']; ?>&current=<?php echo $p['status']; ?>" 
                                       class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase transition-all
                                       <?php echo $p['status'] == 'active' ? 'bg-green-100 text-green-600 hover:bg-green-200' : 'bg-slate-200 text-slate-500 hover:bg-slate-300'; ?>">
                                        <?php echo $p['status']; ?>
                                    </a>
                                </td>
                                <td class="p-6">
                                    <a href="manage-posts.php?delete_id=<?php echo $p['id']; ?>" 
                                       onclick="return confirm('WARNING: Deleting this will disable all affiliate links for this post. Continue?')"
                                       class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
    lucide.createIcons();

    document.getElementById('sync-btn').addEventListener('click', function() {
        const btn = this;
        const icon = document.getElementById('sync-icon');
        const text = document.getElementById('btn-text');
        const msg = document.getElementById('sync-msg');

        btn.disabled = true;
        icon.classList.add('animate-spin');
        text.innerText = 'Syncing...';

        fetch('manage-posts.php?action=sync')
            .then(response => response.json())
            .then(data => {
                msg.classList.remove('hidden', 'bg-red-50', 'text-red-600', 'border-red-100', 'bg-green-50', 'text-green-600', 'border-green-100');
                if(data.success) {
                    msg.classList.add('bg-green-50', 'text-green-600', 'border-green-100');
                    msg.innerHTML = `<div class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4"></i> Found ${data.new_posts} new posts! Reloading...</div>`;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    msg.classList.add('bg-red-50', 'text-red-600', 'border-red-100');
                    msg.innerText = "Error: " + data.message;
                    btn.disabled = false;
                    icon.classList.remove('animate-spin');
                    text.innerText = 'Sync AccraChic.com';
                }
                lucide.createIcons();
            })
            .catch(err => {
                msg.classList.remove('hidden');
                msg.classList.add('bg-red-50', 'text-red-600', 'border-red-100');
                msg.innerText = "Connection failed. Check your API settings.";
                btn.disabled = false;
                icon.classList.remove('animate-spin');
                text.innerText = 'Sync AccraChic.com';
            });
    });
    </script>
</body>
</html>
