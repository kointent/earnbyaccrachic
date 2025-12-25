<?php
require 'config/db.php';
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // 1. Check if this is the very first login
        $_SESSION['is_first_login'] = ($user['last_login'] === null);

        // 2. Update the last_login timestamp in the database
        $update = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $update->execute([$user['id']]);

        // 3. Set Session Variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login | AccraChic Earn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .hero-gradient { background: radial-gradient(circle at top right, #4c1d95, #1e1b4b, #0f172a); }
        .bg-glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="hero-gradient min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="index.php" class="text-2xl font-black tracking-widest text-white italic">
                ACCRA<span class="text-blue-500">CHIC</span>
            </a>
            <p class="text-slate-400 mt-2">Welcome back! Log in to your portal.</p>
        </div>

        <div class="bg-glass border border-white/10 p-8 rounded-3xl shadow-2xl">
            <?php if($error): ?>
                <div class="bg-red-500/20 border border-red-500/50 text-red-200 p-3 rounded-xl mb-6 text-sm text-center">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Email Address</label>
                    <input type="email" name="email" required 
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white outline-none focus:border-blue-500 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Password</label>
                    <input type="password" name="password" required 
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white outline-none focus:border-blue-500 transition-all">
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-900/40 transition-all transform active:scale-95">
                    Sign In
                </button>
            </form>

            <div class="mt-8 text-center border-t border-white/5 pt-6">
                <p class="text-slate-400 text-sm">
                    Don't have an account? 
                    <a href="register.php" class="text-blue-400 font-bold hover:underline">Join now</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
