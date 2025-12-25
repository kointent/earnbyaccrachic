<?// logic example for admin/affiliates.php
$stmt = $pdo->query("
    SELECT u.*, 
    (SELECT COUNT(*) FROM views_log WHERE affiliate_id = u.id AND is_valid = 0) as fraud_count 
    FROM users u
");
// If fraud_count > 50, show a red "Warning" icon next to the affiliate's name.
// Only Super Admin and Finance Manager can see Payouts
if ($_SESSION['admin_role'] !== 'super_admin' && $_SESSION['admin_role'] !== 'finance_manager') {
    die("Access Denied: You do not have permission to view Finance records.");
}
?>
