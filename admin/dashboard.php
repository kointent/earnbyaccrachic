<?php
require '../config/db.php';
session_start();

if (!isset($_SESSION['admin_id'])) { header("Location: index.php"); exit(); }

// Simple stats for the boxes
$userCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$pendingPayouts = $pdo->query("SELECT COUNT(*) FROM withdrawals WHERE status = 'pending'")->fetchColumn();
$totalViews = $pdo->query("SELECT COUNT(*) FROM views_log WHERE is_valid = 1")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | AccraChic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-100 flex min-h-screen">

    <aside class="w-64 bg-slate-900 text-white flex flex-col fixed h-full">
        <div class="p-6 text-xl font-black italic border-b border-slate-800 text-blue-400">ACCRA<span class="text-white">CHIC</span></div>
        <nav class="flex-1 p-4 space-y-2 mt-4">
            <a href="dashboard.php" class="flex items-center gap-3 p-3 bg-blue-600 rounded-xl text-white font-bold">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            <a href="manage-posts.php" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition">
                <i data-lucide="file-text" class="w-5 h-5"></i> Manage Posts
            </a>
            <a href="users.php" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition">
                <i data-lucide="users" class="w-5 h-5"></i> Affiliates
            </a>
            <a href="payouts.php" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition">
                <i data-lucide="banknote" class="w-5 h-5"></i> Payouts
            </a>
            <a href="fraud-report.php" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition">
                <i data-lucide="shield-alert" class="w-5 h-5"></i> Fraud Logs
            </a>
            <a href="finance.php" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition">
                <i data-lucide="pie-chart" class="w-5 h-5"></i> Finance
            </a>
            <a href="settings.php" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition">
                <i data-lucide="settings" class="w-5 h-5"></i> Settings
            </a>
        </nav>
        <div class="p-4 border-t border-slate-800">
            <a href="logout.php" class="flex items-center gap-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition">
                <i data-lucide="log-out" class="w-5 h-5"></i> Logout
            </a>
        </div>
    </aside>

    <main class="flex-1 ml-64 p-10">
        <header class="mb-10">
            <h1 class="text-3xl font-black text-slate-800">Welcome, Admin</h1>
            <p class="text-slate-500">Here is what's happening with AccraChic Earn today.</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-3xl border shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase mb-1">Total Affiliates</p>
                <h2 class="text-3xl font-black text-slate-800"><?php echo $userCount; ?></h2>
            </div>
            <div class="bg-white p-6 rounded-3xl border shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase mb-1">Pending Payouts</p>
                <h2 class="text-3xl font-black text-amber-600"><?php echo $pendingPayouts; ?></h2>
            </div>
            <div class="bg-white p-6 rounded-3xl border shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase mb-1">Total Valid Views</p>
                <h2 class="text-3xl font-black text-blue-600"><?php echo $totalViews; ?></h2>
            </div>
        </div>
        
        </main>

    <script>lucide.createIcons();</script>
</body>
</html>
