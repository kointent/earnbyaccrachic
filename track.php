<?php
require 'config/db.php';
session_start();

// 1. Capture Data
$post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
$affiliate_id = isset($_GET['ref']) ? intval($_GET['ref']) : 0;
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];

$res = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'min_time_on_page'");
$min_time = $res->fetchColumn();
// Use $min_time in your validation logic


// 2. ANTI-FRAUD CHECK (Feature 4.4)
$is_valid = 1;
$fraud_reason = null;

// A. Bot Detection
$bots = ['bot', 'crawler', 'spider', 'curl', 'python', 'wget'];
foreach ($bots as $bot) {
    if (stripos($user_agent, $bot) !== false) {
        $is_valid = 0;
        $fraud_reason = "Bot Detected";
    }
}

// B. Duplicate IP Check (24 Hour Window)
$check_ip = $pdo->prepare("SELECT id FROM views_log WHERE ip_address = ? AND affiliate_id = ? AND created_at > NOW() - INTERVAL 1 DAY");
$check_ip->execute([$ip_address, $affiliate_id]);
if ($check_ip->fetch()) {
    $is_valid = 0;
    $fraud_reason = "Duplicate IP";
}

// 3. LOG THE VIEW
$log = $pdo->prepare("INSERT INTO views_log (affiliate_id, post_id, ip_address, user_agent, is_valid) VALUES (?, ?, ?, ?, ?)");
$log->execute([$affiliate_id, $post_id, $ip_address, $user_agent, $is_valid]);

// 4. UPDATE EARNINGS (Only if valid)
if ($is_valid) {
    // Get the rate for this specific post (Feature 4.2/4.5)
    $stmt = $pdo->prepare("SELECT rate_per_view, slug FROM blog_posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if ($post) {
        $rate = $post['rate_per_view'];
        
        // Add money to user balance
        $upd = $pdo->prepare("UPDATE users SET available_balance = available_balance + ? WHERE id = ?");
        $upd->execute([$rate, $affiliate_id]);
        
        // Redirect to the actual blog post on your main site
        header("Location: https://accrachic.com/" . $post['slug']);
        exit();
    }
} else {
    // If fraud, still redirect so the user doesn't suspect anything, but don't pay
    $stmt = $pdo->prepare("SELECT slug FROM blog_posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();
    header("Location: https://accrachic.com/" . ($post['slug'] ?? ''));
    exit();
}
