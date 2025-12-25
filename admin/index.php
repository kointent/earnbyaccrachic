<?php
require '../config/db.php';
session_start();

// 1. Updated Scan: Check for EITHER admin OR super_admin
try {
    $check = $pdo->query("SELECT * FROM users WHERE role = 'admin' OR role = 'super_admin' LIMIT 1");
    $adminUser = $check->fetch();
} catch (PDOException $e) {
    die("Database Error: Check if 'role' column exists. " . $e->getMessage());
}

$error = "";

// 2. Handle Login Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_admin'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Updated Login Query to allow both roles
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND (role = 'admin' OR role = 'super_admin')");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role'] = $user['role']; // Store the specific role
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid Credentials or Account is not set as Admin.";
    }
}

// 3. Handle First-Time Setup (Defaults to super_admin)
if (!$adminUser && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['setup_admin'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $ins = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'super_admin')");
    try {
        $ins->execute([$username, $email, $password]);
        header("Location: index.php?setup=success");
        exit();
    } catch (PDOException $e) {
        $error = "Setup failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | AccraChic</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md bg-white rounded-3xl p-8 shadow-2xl border-t-4 border-blue-600">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-black italic">ACCRA<span class="text-blue-600">CHIC</span> ADMIN</h1>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">Management Portal</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-xs font-bold border border-red-100 text-center">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if($adminUser): ?>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="login_admin" value="1">
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase ml-2">Administrator Email</label>
                    <input type="email" name="email" placeholder="email@accrachic.com" required 
                           class="w-full mt-1 border border-slate-200 p-4 rounded-xl outline-none focus:border-blue-600 transition">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase ml-2">Secure Password</label>
                    <input type="password" name="password" placeholder="••••••••" required 
                           class="w-full mt-1 border border-slate-200 p-4 rounded-xl outline-none focus:border-blue-600 transition">
                </div>
                <button type="submit" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl hover:bg-black transition shadow-lg">
                    Sign In to Dashboard
                </button>
            </form>
        <?php else: ?>
            <div class="bg-amber-50 text-amber-700 p-3 rounded-xl mb-6 text-xs font-bold text-center border border-amber-100">
                No Master Admin detected. Setup required.
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="setup_admin" value="1">
                <input type="text" name="username" placeholder="Master Full Name" required class="w-full border p-4 rounded-xl">
                <input type="email" name="email" placeholder="Master Email" required class="w-full border p-4 rounded-xl">
                <input type="password" name="password" placeholder="Master Password" required class="w-full border p-4 rounded-xl">
                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl">
                    Create Super Admin
                </button>
            </form>
        <?php endif; ?>

    </div>
</body>
</html>
