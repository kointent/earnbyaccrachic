<?php
require '../config/db.php';
session_start();

if (!isset($_SESSION['admin_id'])) { header("Location: index.php"); exit(); }

// Only Super Admin and Finance Manager can see Payouts
if ($_SESSION['admin_role'] !== 'super_admin' && $_SESSION['admin_role'] !== 'finance_manager') {
    die("Access Denied: You do not have permission to view Finance records.");
}


// Handle Status Changes (Ban/Activate)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $status = ($_GET['action'] == 'ban') ? 'banned' : 'active';
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$status, $_GET['id']]);
    header("Location: users.php");
}

$users = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Affiliate Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 flex">
    <main class="flex-1 p-8">
        <h1 class="text-2xl font-black mb-8">Affiliate Management (4.3)</h1>
        <div class="bg-white rounded-3xl border shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-[10px] uppercase font-black text-slate-400">
                    <tr>
                        <th class="p-4">Affiliate</th>
                        <th class="p-4">Balance (GHS)</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach($users as $u): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="p-4">
                            <div class="font-bold"><?php echo htmlspecialchars($u['username']); ?></div>
                            <div class="text-xs text-slate-400"><?php echo $u['email']; ?></div>
                        </td>
                        <td class="p-4 font-mono font-bold"><?php echo number_format($u['available_balance'], 2); ?></td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase <?php echo $u['status'] == 'active' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'; ?>">
                                <?php echo $u['status']; ?>
                            </span>
                        </td>
                        <td class="p-4 space-x-2">
                            <a href="users.php?action=ban&id=<?php echo $u['id']; ?>" class="text-xs font-bold text-red-500 hover:underline">Ban</a>
                            <a href="users.php?action=activate&id=<?php echo $u['id']; ?>" class="text-xs font-bold text-green-500 hover:underline">Activate</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script>lucide.createIcons();</script>
</body>
</html>
