<?php
require '../config/db.php';
// Add your admin session check here
// Only Super Admin and Finance Manager can see Payouts
if ($_SESSION['admin_role'] !== 'super_admin' && $_SESSION['admin_role'] !== 'finance_manager') {
    die("Access Denied: You do not have permission to view Finance records.");
}

// Fetch the 50 most recent rejected views
$stmt = $pdo->prepare("
    SELECT v.*, u.username, p.title 
    FROM views_log v 
    LEFT JOIN users u ON v.affiliate_id = u.id 
    LEFT JOIN blog_posts p ON v.post_id = p.id 
    WHERE v.is_valid = 0 
    ORDER BY v.created_at DESC 
    LIMIT 50
");
$stmt->execute();
$logs = $stmt->fetchAll();
?>

<div class="p-8 bg-slate-50 min-h-screen">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900">Security & Fraud Logs</h1>
        <p class="text-slate-500">Monitor rejected traffic and suspicious affiliate behavior.</p>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-900 text-white">
                <tr>
                    <th class="p-5 text-sm font-semibold uppercase">Affiliate</th>
                    <th class="p-5 text-sm font-semibold uppercase">Blog Post</th>
                    <th class="p-5 text-sm font-semibold uppercase">IP Address</th>
                    <th class="p-5 text-sm font-semibold uppercase">Reason</th>
                    <th class="p-5 text-sm font-semibold uppercase">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach($logs as $log): ?>
                <tr class="hover:bg-red-50/30 transition">
                    <td class="p-5 font-bold text-blue-600">@<?php echo htmlspecialchars($log['username']); ?></td>
                    <td class="p-5 text-slate-600 truncate max-w-xs"><?php echo htmlspecialchars($log['title']); ?></td>
                    <td class="p-5 font-mono text-sm text-slate-500"><?php echo $log['ip_address']; ?></td>
                    <td class="p-5">
                        <?php 
                        $badgeClass = ($log['reason'] == 'Duplicate IP') ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700';
                        ?>
                        <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $badgeClass; ?>">
                            <?php echo strtoupper($log['reason']); ?>
                        </span>
                    </td>
                    <td class="p-5 text-slate-400 text-sm italic">
                        <?php echo date('M d, H:i', strtotime($log['created_at'])); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
