<?php 
require 'config/db.php'; 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// 1. Handle Profile & MoMo Updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $momo_type = $_POST['payment_method'];
    $momo_num  = $_POST['momo_number'];

    $upd = $pdo->prepare("UPDATE users SET full_name = ?, payment_method = ?, momo_number = ? WHERE id = ?");
    if ($upd->execute([$full_name, $momo_type, $momo_num, $user_id])) {
        $message = ["type" => "success", "text" => "Profile updated successfully!"];
    }
}

// 2. Handle Password Updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $new_pass = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
    $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    if ($upd->execute([$new_pass, $user_id])) {
        $message = ["type" => "success", "text" => "Password changed successfully!"];
    }
}

// 3. Fetch Current User Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Profile | AccraChic Earn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media (max-width: 768px) { main { padding-bottom: 90px; } }
    </style>
</head>
<body class="bg-slate-50 flex flex-col md:flex-row min-h-screen">

    <div class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-50">
        <div class="font-bold text-blue-400 italic">ACCRA<span class="text-white">CHIC</span></div>
        <button id="sidebar-toggle" class="p-2 text-white outline-none"><i data-lucide="menu"></i></button>
    </div>

    <aside id="main-sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full md:translate-x-0 md:relative md:flex flex-col w-64 bg-slate-900 text-white p-6 transition duration-200 ease-in-out z-50">
        <div class="text-xl font-bold mb-10 text-blue-400 border-b border-slate-800 pb-4 hidden md:block italic">AccraChic Earn</div>
        <nav class="space-y-4 flex-1">
            <a href="dashboard.php" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            <a href="withdrawals.php" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition">
                <i data-lucide="wallet" class="w-5 h-5"></i> Withdrawals
            </a>
            <a href="profile.php" class="flex items-center gap-3 p-3 bg-blue-600 rounded-lg text-white font-semibold">
                <i data-lucide="user" class="w-5 h-5"></i> Settings
            </a>
        </nav>
        <a href="logout.php" class="flex items-center gap-3 p-3 text-red-400 hover:text-red-300 transition border-t border-slate-800 pt-4">
            <i data-lucide="log-out" class="w-5 h-5"></i> Logout
        </a>
    </aside>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>

    <main class="flex-1 p-4 md:p-8">
        <header class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Account Settings</h1>
            <p class="text-slate-500 text-sm">Manage your personal info and payout methods.</p>
        </header>

        <?php if($message): ?>
            <div class="max-w-3xl mb-6 p-4 rounded-2xl text-sm font-bold <?php echo $message['type'] == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $message['text']; ?>
            </div>
        <?php endif; ?>

        <div class="max-w-3xl space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 md:p-8">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center">
                        <i data-lucide="user-cog"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-lg">General Profile</h3>
                        <p class="text-slate-500 text-xs uppercase font-bold tracking-widest">Details & Payouts</p>
                    </div>
                </div>

                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase">Full Name</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required 
                                   class="w-full mt-2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-500 font-medium">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase">Email Address</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['email']); ?>" disabled 
                                   class="w-full mt-2 bg-slate-100 border border-slate-200 rounded-xl px-4 py-3 text-slate-400 cursor-not-allowed">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase">Payment Method (MoMo)</label>
                            <select name="payment_method" class="w-full mt-2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-500 font-bold">
                                <option value="MTN" <?php if($user['payment_method'] == 'MTN') echo 'selected'; ?>>MTN Mobile Money</option>
                                <option value="Vodafone" <?php if($user['payment_method'] == 'Vodafone') echo 'selected'; ?>>Vodafone Cash</option>
                                <option value="AirtelTigo" <?php if($user['payment_method'] == 'AirtelTigo') echo 'selected'; ?>>AirtelTigo Money</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase">MoMo Number</label>
                            <input type="text" name="momo_number" value="<?php echo htmlspecialchars($user['momo_number']); ?>" required 
                                   class="w-full mt-2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-500 font-bold">
                        </div>
                    </div>

                    <button type="submit" name="update_profile" class="bg-blue-600 text-white font-bold px-8 py-4 rounded-xl hover:bg-blue-500 transition w-full md:w-auto">
                        Save Changes
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 md:p-8">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center">
                        <i data-lucide="shield-check"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-lg">Security</h3>
                        <p class="text-slate-500 text-xs uppercase font-bold tracking-widest">Update Password</p>
                    </div>
                </div>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase">New Password</label>
                        <input type="password" name="new_password" required placeholder="••••••••" 
                               class="w-full mt-2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-red-500">
                    </div>
                    <button type="submit" name="update_password" class="bg-slate-900 text-white font-bold px-8 py-4 rounded-xl hover:bg-slate-800 transition w-full md:w-auto">
                        Update Password
                    </button>
                </form>
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
        <a href="withdrawals.php" class="flex flex-col items-center text-slate-400">
            <i data-lucide="wallet" class="w-6 h-6"></i>
            <span class="text-[10px] font-bold mt-1 uppercase">Payout</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center text-blue-600">
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
