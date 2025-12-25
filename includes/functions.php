<?php
function is_fraud($ip, $user_agent) {
    // 1. Check for known VPN/Proxy IPs (Optional: Use an API like IPHub)
    
    // 2. Check for "Bot" strings in User Agent
    $bots = ['bot', 'crawler', 'spider', 'curl', 'wget', 'headless'];
    foreach($bots as $bot) {
        if(strpos(strtolower($user_agent), $bot) !== false) return "Bot Detected";
    }

    // 3. Rate Limiting: Max 5 views per IP across the whole site per hour
    // Logic: COUNT views in DB where ip = $ip AND timestamp > 1 hour ago
    
    return false; // Traffic is clean
}
?>
