<?php
require 'config/db.php';
session_start();

$message = "";
// Catch email from the landing page "Get Started" form
$pre_email = isset($_GET['pre_email']) ? htmlspecialchars($_GET['pre_email']) : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $username  = $_POST['username'];
    $email     = $_POST['email'];
    $phone     = $_POST['phone'];
    $password  = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $momo_type = $_POST['payment_method'];
    $momo_num  = $_POST['momo_number'];

    // Check if email or username exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $check->execute([$email, $username]);
    
    if ($check->rowCount() > 0) {
        $message = ["type" => "error", "text" => "Email or Username already taken."];
    } else {
        $ins = $pdo->prepare("INSERT INTO users (full_name, username, email, phone_number, password, payment_method, momo_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($ins->execute([$full_name, $username, $email, $phone, $password, $momo_type, $momo_num])) {
            $message = ["type" => "success", "text" => "Account created! You can now login."];
        } else {
            $message = ["type" => "error", "text" => "Registration failed. Try again."];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Join Network | AccraChic Earn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .hero-gradient { background: radial-gradient(circle at top right, #4c1d95, #1e1b4b, #0f172a); }
        .bg-glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="hero-gradient min-h-screen py-10 px-4">

    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-8">
            <a href="index.php" class="text-2xl font-black tracking-widest text-white italic uppercase">
                ACCRA<span class="text-blue-500">CHIC</span>
            </a>
            <h1 class="text-white text-xl font-bold mt-4">Create Your Affiliate Account</h1>
            <p class="text-slate-400 text-sm">Start earning GHS for every view you generate.</p>
        </div>

        <div class="bg-glass border border-white/10 p-6 md:p-10 rounded-3xl shadow-2xl">
            <?php if($message): ?>
                <div class="mb-6 p-4 rounded-xl text-sm font-bold <?php echo $message['type'] == 'success' ? 'bg-green-500/20 text-green-300 border border-green-500/50' : 'bg-red-500/20 text-red-300 border border-red-500/50'; ?>">
                    <?php echo $message['text']; ?>
                    <?php if($message['type'] == 'success') echo '<a href="login.php" class="underline ml-2">Login here</a>'; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h3 class="text-blue-400 text-xs font-black uppercase tracking-widest">Personal Details</h3>
                        <input type="text" name="full_name" placeholder="Full Name" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white outline-none focus:border-blue-500">
                        <input type="text" name="username" placeholder="Username" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white outline-none focus:border-blue-500">
                        <input type="email" name="email" value="<?php echo $pre_email; ?>" placeholder="Email Address" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white outline-none focus:border-blue-500">
                        <input type="password" name="password" placeholder="Create Password" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white outline-none focus:border-blue-500">
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-blue-400 text-xs font-black uppercase tracking-widest">Payout Details (MoMo)</h3>
                        <input type="text" name="phone" placeholder="WhatsApp Number" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white outline-none focus:border-blue-500">
                        
                        <select name="payment_method" class="w-full bg-[#1e1b4b] border border-white/10 rounded-xl px-4 py-4 text-white outline-none focus:border-blue-500">
                            <option value="MTN">MTN Mobile Money</option>
                            <option value="Vodafone">Telecel (Vodafone) Cash</option>
                            <option value="AirtelTigo">AirtelTigo Money</option>
                        </select>
                        
                        <input type="text" name="momo_number" placeholder="MoMo Number (05XXXXXXXX)" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white outline-none focus:border-blue-500">
                        
                        <div class="p-4 bg-blue-500/10 rounded-xl border border-blue-500/20">
                            <p class="text-[10px] text-blue-300 leading-tight">Ensure your MoMo number is correct. We use this for all your future payouts.</p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-5 rounded-2xl shadow-xl shadow-blue-900/40 transition-all transform active:scale-95 uppercase tracking-widest">
                    Create My Account
                </button>
            </form>

            <p class="text-center text-slate-400 text-sm mt-8">
                Already have an account? <a href="login.php" class="text-blue-400 font-bold hover:underline">Login</a>
            </p>
        </div>
    </div>

</body>
</html>
