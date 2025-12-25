<?php 
require 'config/db.php'; 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// 1. Get Current Balance & Payment Info
$stmt = $pdo->prepare("SELECT available_balance, payment_method, momo_number FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$res = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'min_time_on_page'");
$min_time = $res->fetchColumn();
// Use $min_time in your validation logic

// 2. Handle Withdrawal Request
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_amount'])) {
    $amount = floatval($_POST['request_amount']);
    
    if ($amount >= 50 && $amount <= $user['available_balance']) {
        // Insert into withdrawals table
        $ins = $pdo->prepare("INSERT INTO withdrawals (user_id, amount, method, account_details, status) VALUES (?, ?, ?, ?, 'pending')");
        $details = $user['payment_method'] . " - " . $user['momo_number'];
        
        if ($ins->execute([$user_id, $amount, $user['payment_method'], $details])) {
            // Deduct from available balance
            $upd = $pdo->prepare("UPDATE users SET available_balance = available_balance - ? WHERE id = ?");
            $upd->execute([$amount, $user_id]);
            $message = ["type" => "success", "text" => "Request submitted! We will process your MoMo payout within 24 hours."];
            // Refresh balance
            $user['available_balance'] -= $amount;
        }
    } else {
        $message = ["type" => "error", "text" => "Invalid amount. Minimum withdrawal is GHS 50.00"];
    }
}

// 3. Fetch Withdrawal History
$history_stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC");
$history_stmt->execute([$user_id]);
$withdrawals = $history_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Withdrawals | AccraChic Earn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media (max-width: 768px) { main { padding-bottom: 90px; } }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-paid { background: #dcfce7; color: #166534; }
    </style>
</head>
<body class="bg-slate-50 flex flex-col md:flex-row min-h-screen">

    <div class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-50">
        <div class="font-bold text-blue-400 italic text-lg">ACCRA<span class="text-white">CHIC</span></div>
        <button id="sidebar-toggle" class="p-2 text-white outline-none">
            <i data-lucide="menu"></i>
        </button>
    </div>

    <aside id="main-sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full md:translate-x-0 md:relative md:flex flex-col w-64 bg-slate-900 text-white p-6 transition duration-200 ease-in-out z-50">
        <div class="text-xl font-bold mb-10 text-blue-400 border-b border-slate-800 pb-4 hidden md:block italic">AccraChic Earn</div>
        <nav class="space-y-4 flex-1">
            <a href="dashboard.php" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            <a href="withdrawals.php" class="flex items-center gap-3 p-3 bg-blue-600 rounded-lg text-white font-semibold">
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
        <header class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Withdraw Funds</h1>
            <p class="text-slate-500 text-sm">Convert your earnings into Mobile Money.</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-blue-600 rounded-3xl p-6 text-white shadow-xl shadow-blue-200">
                    <p class="text-blue-100 text-xs font-bold uppercase tracking-wider mb-1">Available to Withdraw</p>
                    <h2 class="text-3xl font-black">GHS <?php echo number_format($user['available_balance'], 2); ?></h2>
                </div>

                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-800 mb-4">Request Payout</h3>
                    
                    <?php if($message): ?>
                        <div class="mb-4 p-3 rounded-lg text-xs font-bold <?php echo $message['type'] == 'success' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'; ?>">
                            <?php echo $message['text']; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase">Amount (GHS)</label>
                            <input type="number" name="request_amount" step="0.01" min="50" placeholder="0.00" required 
                                   class="w-full mt-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-500 font-bold">
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                            <p class="text-[10px] text-slate-500 uppercase font-bold">Receiving Account</p>
                            <p class="text-sm font-bold text-slate-700"><?php echo $user['payment_method']; ?>: <?php echo $user['momo_number']; ?></p>
                        </div>
                        <button type="submit" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl hover:bg-slate-800 transition transform active:scale-95">
                            Submit Request
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100">
                        <h3 class="font-bold text-slate-800">Transaction History</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left min-w-[500px]">
                            <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                                <tr>
                                    <th class="p-4">Date</th>
                                    <th class="p-4">Amount</th>
                                    <th class="p-4">Method</th>
                                    <th class="p-4 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach($withdrawals as $row): ?>
                                <tr>
                                    <td class="p-4 text-sm text-slate-600"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td class="p-4 font-bold text-slate-800 text-sm">GHS <?php echo number_format($row['amount'], 2); ?></td>
                                    <td class="p-4 text-xs text-slate-500 font-medium"><?php echo $row['method']; ?></td>
                                    <td class="p-4 text-right">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase <?php echo 'status-'.$row['status']; ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($withdrawals)): ?>
                                    <tr><td colspan="4" class="p-10 text-center text-slate-400 text-sm italic">No withdrawals yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 px-6 py-3 flex justify-between items-center z-50">
        <a href="dashboard.php" class="flex flex-col items-center text-slate-400">
            <i data-lucide="layout-dashboard" class="w-6 h-6"></i>
            <span class="text-[10px] font-bold mt-1 uppercase">Home</span>
        </a>
        <a href="#" class="flex flex-col items-center text-slate-400">
            <i data-lucide="link" class="w-6 h-6"></i>
            <span class="text-[10px] font-bold mt-1 uppercase">Links</span>
        </a>
        <a href="withdrawals.php" class="flex flex-col items-center text-blue-600">
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
    </script>
</body>
</html>
