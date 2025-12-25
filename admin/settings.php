<?php
require '../config/db.php';
session_start();
// Only Super Admin and Finance Manager can see Payouts
if ($_SESSION['admin_role'] !== 'super_admin' && $_SESSION['admin_role'] !== 'finance_manager') {
    die("Access Denied: You do not have permission to view Finance records.");
}

// Gatekeeper: Only Super Admin can change system-wide settings
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'super_admin') {
    die("Access Denied: Only the Super Admin can modify system settings.");
}

$message = "";

// Handle Saving Settings
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_settings'])) {
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$value, $key]);
    }
    $message = "Settings updated successfully!";
}

// Fetch All Current Settings
$settings_raw = $pdo->query("SELECT * FROM system_settings")->fetchAll();
$settings = [];
foreach ($settings_raw as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Settings | AccraChic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 flex">

    <main class="flex-1 p-8 max-w-4xl">
        <header class="mb-8">
            <h1 class="text-2xl font-black text-slate-800">System Configuration (4.8)</h1>
            <p class="text-slate-500">Global rules for earnings, withdrawals, and fraud detection.</p>
        </header>

        <?php if($message): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-2xl mb-6 font-bold flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <input type="hidden" name="save_settings" value="1">

            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i data-lucide="coins" class="text-blue-500"></i> Earning Rules
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Global Rate per View (GHS)</label>
                        <input type="number" step="0.001" name="settings[global_rate]" value="<?php echo $settings['global_rate']; ?>" 
                               class="w-full mt-2 border p-3 rounded-xl outline-none focus:border-blue-500 font-bold">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Min. Time on Page (Seconds)</label>
                        <input type="number" name="settings[min_time_on_page]" value="<?php echo $settings['min_time_on_page']; ?>" 
                               class="w-full mt-2 border p-3 rounded-xl outline-none focus:border-blue-500 font-bold">
                        <p class="text-[10px] text-slate-400 mt-1 italic">Views shorter than this are marked as fraud.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i data-lucide="wallet" class="text-green-500"></i> Withdrawal Settings
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Minimum Payout (GHS)</label>
                        <input type="number" name="settings[min_withdrawal]" value="<?php echo $settings['min_withdrawal']; ?>" 
                               class="w-full mt-2 border p-3 rounded-xl outline-none focus:border-blue-500 font-bold">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">Currency Label</label>
                        <input type="text" name="settings[currency]" value="<?php echo $settings['currency']; ?>" 
                               class="w-full mt-2 border p-3 rounded-xl outline-none focus:border-blue-500 font-bold">
                    </div>
                </div>
            </div>

            <button type="submit" class="bg-slate-900 text-white font-black px-10 py-4 rounded-2xl hover:bg-black transition shadow-xl">
                Apply System Changes
            </button>
        </form>
    </main>

    <script>lucide.createIcons();</script>
</body>
</html>
