<?php 
require 'config/db.php'; 
session_start();

// Guard: Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// --- DATABASE LOGIC ---
// 1. Get Balance
$stmt = $pdo->prepare("SELECT available_balance, pending_balance, withdrawn_total FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$available_balance = $user['available_balance'] ?? 0.00;
$pending_balance = $user['pending_balance'] ?? 0.00;

// 2. Today's Earnings
$stmt = $pdo->prepare("SELECT COUNT(*) as today_count FROM views_log WHERE affiliate_id = ? AND is_valid = 1 AND DATE(created_at) = CURDATE()");
$stmt->execute([$user_id]);
$today_data = $stmt->fetch();
$today_earnings = $today_data['today_count'] * 0.02;

// 3. Stats
$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total_clicks, 
    SUM(CASE WHEN is_valid = 1 THEN 1 ELSE 0 END) as valid_views,
    SUM(CASE WHEN is_valid = 0 THEN 1 ELSE 0 END) as rejected_views
    FROM views_log WHERE affiliate_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

// 4. Blog Posts
$posts_stmt = $pdo->query("SELECT * FROM blog_posts WHERE status = 'active' LIMIT 10");
$posts = $posts_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard | AccraChic Earn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media (max-width: 768px) { main { padding-bottom: 80px; } }
    </style>
</head>
<body class="bg-slate-50 flex flex-col md:flex-row min-h-screen">

    <div class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-50">
        <div class="font-bold text-blue-400 italic">ACCRA<span class="text-white">CHIC</span></div>
        <button id="sidebar-toggle" class="p-2 text-white outline-none">
            <i data-lucide="menu"></i>
        </button>
    </div>

    <aside id="main-sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full md:translate-x-0 md:relative md:flex flex-col w-64 bg-slate-900 text-white p-6 transition duration-200 ease-in-out z-50">
        <div class="text-xl font-bold mb-10 text-blue-400 border-b border-slate-800 pb-4 hidden md:block italic">AccraChic Earn</div>
        
        <nav class="space-y-4 flex-1">
            <a href="dashboard.php" class="flex items-center gap-3 p-3 bg-blue-600 rounded-lg text-white font-semibold">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            <a href="#" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition">
                <i data-lucide="link" class="w-5 h-5"></i> My Links
            </a>
            <a href="withdrawals.php" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition">
                <i data-lucide="wallet" class="w-5 h-5"></i> Withdrawals
            </a>
            <a href="profile.php" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition">
                <i data-lucide="settings" class="w-5 h-5"></i> Settings
            </a>
        </nav>

        <a href="logout.php" class="flex items-center gap-3 p-3 text-red-400 hover:text-red-300 transition border-t border-slate-800 pt-4">
            <i data-lucide="log-out" class="w-5 h-5"></i> Logout
        </a>
    </aside>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>

    <main class="flex-1 p-4 md:p-8">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <?php if (isset($_SESSION['is_first_login']) && $_SESSION['is_first_login']): ?>
                    <h1 class="text-2xl font-bold text-slate-800">Welcome, <?php echo htmlspecialchars($username); ?>! 👋</h1>
                    <p class="text-blue-600 text-sm font-semibold italic">We're excited to have you join our network.</p>
                    <?php $_SESSION['is_first_login'] = false; ?>
                <?php else: ?>
                    <h1 class="text-2xl font-bold text-slate-800">Welcome back, <?php echo htmlspecialchars($username); ?>!</h1>
                    <p class="text-slate-500 text-sm">Here is your link performance today.</p>
                <?php endif; ?>
            </div>
            <div class="bg-white px-6 py-3 rounded-2xl shadow-sm border border-slate-200 w-full md:w-auto text-center md:text-left">
                <p class="text-xs text-slate-400 uppercase font-bold">Withdrawable Balance</p>
                <p class="text-xl font-black text-slate-800">GHS <?php echo number_format($available_balance, 2); ?></p>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Today's Earnings</p>
                <h2 class="text-2xl font-black text-blue-600">GHS <?php echo number_format($today_earnings, 2); ?></h2>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pending Approval</p>
                <h2 class="text-2xl font-black text-violet-600">GHS <?php echo number_format($pending_balance, 2); ?></h2>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Withdrawn</p>
                <h2 class="text-2xl font-black text-slate-800">GHS <?php echo number_format($user['withdrawn_total'] ?? 0, 2); ?></h2>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-gradient-to-br from-blue-600 to-blue-700 p-6 rounded-2xl text-white shadow-xl shadow-blue-200">
                <p class="text-blue-100 text-sm mb-1 uppercase tracking-wider">Total Clicks</p>
                <h2 class="text-3xl font-bold"><?php echo number_format($stats['total_clicks'] ?? 0); ?></h2>
            </div>
            <div class="bg-gradient-to-br from-violet-600 to-violet-700 p-6 rounded-2xl text-white shadow-xl shadow-violet-200">
                <p class="text-violet-100 text-sm mb-1 uppercase tracking-wider">Valid Views</p>
                <h2 class="text-3xl font-bold"><?php echo number_format($stats['valid_views'] ?? 0); ?></h2>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-red-500 text-sm mb-1 uppercase tracking-wider font-bold">Rejected Views</p>
                <h2 class="text-3xl font-bold text-slate-800"><?php echo number_format($stats['rejected_views'] ?? 0); ?></h2>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Available Blog Posts</h3>
                <span class="text-xs text-blue-600 font-bold">Earn GHS 0.02 Per View</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left min-w-[500px]">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr>
                            <th class="p-4">Blog Post</th>
                            <th class="p-4">Rate</th>
                            <th class="p-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($posts as $post): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4">
                                <span class="font-medium text-slate-700 block"><?php echo htmlspecialchars($post['title']); ?></span>
                            </td>
                            <td class="p-4 text-green-600 font-bold">GHS <?php echo $post['rate_per_view']; ?></td>
                            <td class="p-4 text-right">
                                <button onclick="copyLink('<?php echo $post['slug']; ?>', '<?php echo $user_id; ?>')" class="bg-blue-50 text-blue-600 px-4 py-2 rounded-lg text-sm font-bold hover:bg-blue-600 hover:text-white transition flex items-center gap-2 ml-auto">
                                    <i data-lucide="copy" class="w-4 h-4"></i> Copy Link
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 px-6 py-3 flex justify-between items-center z-50">
        <a href="dashboard.php" class="flex flex-col items-center text-blue-600">
            <i data-lucide="layout-dashboard" class="w-6 h-6"></i>
            <span class="text-[10px] font-bold mt-1 uppercase">Home</span>
        </a>
        <a href="#" class="flex flex-col items-center text-slate-400">
            <i data-lucide="link" class="w-6 h-6"></i>
            <span class="text-[10px] font-bold mt-1 uppercase">Links</span>
        </a>
        <a href="withdrawals.php" class="flex flex-col items-center text-slate-400">
            <i data-lucide="wallet" class="w-6 h-6"></i>
            <span class="text-[10px] font-bold mt-1 uppercase">Payout</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center text-slate-400">
            <i data-lucide="user" class="w-6 h-6"></i>
            <span class="text-[10px] font-bold mt-1 uppercase">Me</span>
        </a>
    </div>

    <script>
        lucide.createIcons();

        const toggleBtn = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('main-sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        toggleBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        function copyLink(slug, userId) {
            const url = `https://accrachic.com/${slug}?ref=${userId}`;
            navigator.clipboard.writeText(url).then(() => {
                alert("Affiliate link copied to clipboard!");
            });
        }
    </script>
</body>
</html>
