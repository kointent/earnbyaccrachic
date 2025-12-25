<?php
require '../config/db.php';
session_start();

// Gatekeeper: Only Super Admin and Finance Managers
if (!isset($_SESSION['admin_id']) || !in_array($_SESSION['admin_role'], ['super_admin', 'finance_manager'])) {
    die("Access Denied: You do not have permission to view Finance records.");
}

try {
    // 1. Total Views Generated (Valid vs Invalid)
    $viewStats = $pdo->query("SELECT 
        SUM(CASE WHEN is_valid = 1 THEN 1 ELSE 0 END) as total_valid,
        SUM(CASE WHEN is_valid = 0 THEN 1 ELSE 0 END) as total_fraud
        FROM views_log")->fetch();

    // 2. Total Payout Obligations (Money currently in user balances)
    $totalOwed = $pdo->query("SELECT SUM(available_balance) FROM users WHERE role = 'user'")->fetchColumn();

    // 3. Total Already Paid Out
    $totalPaid = $pdo->query("SELECT SUM(amount) FROM withdrawals WHERE status = 'paid'")->fetchColumn();

    // 4. Top Earning Affiliates (Feature 4.6)
    $topAffiliates = $pdo->query("SELECT username, available_balance 
                                 FROM users WHERE role = 'user' 
                                 ORDER BY available_balance DESC LIMIT 5")->fetchAll();

    // 5. Monthly Growth (Simplistic view)
    $monthlyTraffic = $pdo->query("SELECT DATE(created_at) as day, COUNT(*) as clicks 
                                   FROM views_log 
                                   WHERE created_at > NOW() - INTERVAL 7 DAY 
                                   GROUP BY day")->fetchAll();

} catch (PDOException $e) {
    die("Finance Query Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finance Dashboard | AccraChic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 flex">

    <main class="flex-1 p-8">
        <div class="mb-10">
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Finance & Performance (4.6)</h1>
            <p class="text-slate-500 font-medium">Tracking system profitability and payout health.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Valid Views</p>
                <h2 class="text-3xl font-black text-blue-600"><?php echo number_format($viewStats['total_valid']); ?></h2>
                <p class="text-[10px] text-slate-400 mt-2">Invalid: <?php echo number_format($viewStats['total_fraud']); ?></p>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm border-l-4 border-l-amber-500">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Owed (Unpaid)</p>
                <h2 class="text-3xl font-black text-slate-800">GHS <?php echo number_format($totalOwed, 2); ?></h2>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm border-l-4 border-l-green-500">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Paid Out</p>
                <h2 class="text-3xl font-black text-slate-800">GHS <?php echo number_format($totalPaid, 2); ?></h2>
            </div>

            <div class="bg-slate-900 p-6 rounded-3xl shadow-xl shadow-slate-200">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Platform Cost</p>
                <h2 class="text-3xl font-black text-white">GHS <?php echo number_format($totalOwed + $totalPaid, 2); ?></h2>
                <p class="text-[10px] text-blue-400 mt-2 uppercase font-bold">Accumulated Liabilities</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Top Earning Affiliates</h3>
                    <i data-lucide="award" class="text-amber-500"></i>
                </div>
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-[10px] uppercase text-slate-400 font-bold">
                        <tr>
                            <th class="p-4">Username</th>
                            <th class="p-4 text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($topAffiliates as $aff): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4 font-bold text-slate-700"><?php echo htmlspecialchars($aff['username']); ?></td>
                            <td class="p-4 text-right font-black text-blue-600">GHS <?php echo number_format($aff['available_balance'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h3 class="font-bold text-slate-800 mb-6">Recent Daily Traffic</h3>
                <div class="space-y-4">
                    <?php foreach($monthlyTraffic as $day): ?>
                        <div class="flex items-center gap-4">
                            <span class="text-xs font-bold text-slate-400 w-24"><?php echo date('M d', strtotime($day['day'])); ?></span>
                            <div class="flex-1 bg-slate-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-blue-500 h-full" style="width: <?php echo min(100, ($day['clicks']/500)*100); ?>%"></div>
                            </div>
                            <span class="text-xs font-black text-slate-700"><?php echo $day['clicks']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <script>lucide.createIcons();</script>
</body>
</html>
