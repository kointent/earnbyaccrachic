<?php
require '../config/db.php';
session_start();
// Only Super Admin and Finance Manager can see Payouts
if ($_SESSION['admin_role'] !== 'super_admin' && $_SESSION['admin_role'] !== 'finance_manager') {
    die("Access Denied: You do not have permission to view Finance records.");
}


// Logic for Approving Payout
if (isset($_POST['approve_id'])) {
    $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'paid' WHERE id = ?");
    $stmt->execute([$_POST['approve_id']]);
    // Optionally: Update user's withdrawn_total here
}

$payouts = $pdo->query("SELECT w.*, u.username, u.momo_number FROM withdrawals w JOIN users u ON w.user_id = u.id WHERE w.status = 'pending'")->fetchAll();
?>
