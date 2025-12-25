<?php
// Set headers to allow requests from accrachic.com
header("Access-Control-Allow-Origin: https://accrachic.com");
header("Content-Type: application/json");

require '../config/db.php';

// Get the data sent from the blog
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $affiliate_id = $data['affiliate_id'];
    $post_id      = $data['post_id'];
    $ip_address   = $_SERVER['REMOTE_ADDR'];
    $user_agent   = $_SERVER['HTTP_USER_AGENT'];

    // 1. Basic Bot Check
    if (strpos(strtolower($user_agent), 'bot') !== false) {
        die(json_encode(['status' => 'ignored', 'reason' => 'bot']));
    }

    // 2. Fraud Check: Has this IP viewed this post in the last 24 hours?
    $stmt = $pdo->prepare("SELECT id FROM views_log WHERE ip_address = ? AND post_id = ? AND created_at > NOW() - INTERVAL 1 DAY");
    $stmt->execute([$ip_address, $post_id]);
    
    if ($stmt->rowCount() > 0) {
        // Log as duplicate but don't pay
        $status = 'rejected';
        $reason = 'duplicate_ip';
    } else {
        $status = 'approved';
        $reason = 'valid_view';
        
        // 3. Update Affiliate Balance (e.g., GHS 0.02)
        $update = $pdo->prepare("UPDATE users SET balance = balance + 0.02 WHERE id = ?");
        $update->execute([$affiliate_id]);
    }

    // 4. Save to Log
    $log = $pdo->prepare("INSERT INTO views_log (affiliate_id, post_id, ip_address, is_valid) VALUES (?, ?, ?, ?)");
    $log->execute([$affiliate_id, $post_id, $ip_address, ($status == 'approved' ? 1 : 0)]);

    echo json_encode(['status' => $status]);
}
?>
