<?php
require '../config/db.php';
session_start();

// Gatekeeper: Only Super Admin & Content Managers
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
// Only Super Admin and Finance Manager can see Payouts
if ($_SESSION['admin_role'] !== 'super_admin' && $_SESSION['admin_role'] !== 'finance_manager') {
    die("Access Denied: You do not have permission to view Finance records.");
}

// Fetch all invalid views to analyze fraud patterns
$fraudQuery = "SELECT v.*, u.username, p.title as post_title 
               FROM views_log v 
               LEFT JOIN users u ON v.affiliate_id = u.id 
               LEFT JOIN blog_posts p ON v.post_id = p.id 
               WHERE v.is_valid = 0 
               ORDER BY v.created_at DESC LIMIT 50";
$fraudLogs = $pdo->query($fraudQuery)->fetchAll();

// Count fraud by reason for the chart/summary
$statsQuery = "SELECT fraud_reason, COUNT(*) as total FROM views_log WHERE is_valid = 0 GROUP BY fraud_reason";
$stats = $pdo->query($statsQuery)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fraud Analysis | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 flex">

    <main class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-black text-slate-800">Fraud & Anti-Bot Report (4.4)</h1>
            <div class="flex gap-2">
                <?php foreach($stats as $s): ?>
                    <div class="bg-white border px-4 py-2 rounded-xl shadow-sm">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block"><?php echo $s['fraud_reason'] ?? 'Unknown'; ?></span>
                        <span class="text-lg font-black text-red-600"><?php echo $s['total']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-[10px] uppercase text-slate-400 font-black">
                    <tr>
                        <th class="p-4">Affiliate</th>
                        <th class="p-4">Post</th>
                        <th class="p-4">IP Address</th>
                        <th class="p-4">Reason</th>
                        <th class="p-4">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php if(empty($fraudLogs)): ?>
                        <tr><td colspan="5" class="p-10 text-center text-slate-400 italic">No fraud detected yet. All views are currently valid.</td></tr>
                    <?php endif; ?>
                    
                    <?php foreach($fraudLogs as $log): ?>
                    <tr class="hover:bg-red-50/30 transition">
                        <td class="p-4 font-bold text-slate-700"><?php echo htmlspecialchars($log['username'] ?? 'Deleted User'); ?></td>
                        <td class="p-4 text-slate-500"><?php echo htmlspecialchars($log['post_title'] ?? 'Unknown Post'); ?></td>
                        <td class="p-4 font-mono text-xs"><?php echo $log['ip_address']; ?></td>
                        <td class="p-4">
                            <span class="bg-red-100 text-red-600 px-2 py-1 rounded-md text-[10px] font-black uppercase">
                                <?php echo $log['fraud_reason']; ?>
                            </span>
                        </td>
                        <td class="p-4 text-slate-400"><?php echo date('M d, H:i', strtotime($log['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-6 p-4 bg-amber-50 border border-amber-100 rounded-2xl">
            <p class="text-xs text-amber-700 font-medium">
                <strong>Admin Note:</strong> These views were logged but <strong>no commission</strong> was added to the affiliate's balance. Frequent "Duplicate IP" flags from the same affiliate may indicate refresh-abuse.
            </p>
        </div>
    </main>

    <script>lucide.createIcons();</script>
</body>
</html>
