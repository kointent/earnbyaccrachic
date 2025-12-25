<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earn | AccraChic Affiliate Program</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .hero-gradient { background: radial-gradient(circle at top right, #4c1d95, #1e1b4b, #0f172a); }
        .bg-glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); }
        .feature-card:hover { border-color: rgba(59, 130, 246, 0.3); transform: translateY(-5px); }
    </style>
</head>
<body class="hero-gradient text-slate-100 font-sans min-h-screen">

    <nav class="p-6">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="text-xl font-black tracking-widest text-white italic">ACCRA<span class="text-blue-500">CHIC</span></div>
            
            <div class="hidden md:flex space-x-8 items-center">
                <a href="login.php" class="hover:text-blue-400 transition font-medium">Login</a>
                <a href="register.php" class="bg-blue-600 hover:bg-blue-500 px-6 py-3 rounded-full font-bold shadow-lg shadow-blue-900/40 transition">Join Network</a>
            </div>

            <button id="mobile-menu-btn" class="md:hidden p-2 text-white">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </div>

        <div id="mobile-menu" class="hidden md:hidden mt-4 bg-glass rounded-2xl p-6 flex flex-col space-y-4 border border-white/10">
            <a href="login.php" class="text-lg font-medium border-b border-white/10 pb-2">Login</a>
            <a href="register.php" class="text-lg font-bold text-blue-400">Join Network</a>
        </div>
    </nav>

    <header class="max-w-6xl mx-auto text-center py-12 md:py-24 px-6">
        <h1 class="text-4xl md:text-7xl font-extrabold mb-6 leading-tight">
            Monetize Your Influence with <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-violet-500">accrachic.com</span>
        </h1>
        <p class="text-lg md:text-xl text-slate-400 max-w-3xl mx-auto mb-10">
            Get paid for every single person you refer to read our trending content. Join the most profitable PPV affiliate program in Ghana.
        </p>

        <form action="register.php" method="GET" class="bg-glass border border-white/10 p-2 rounded-2xl flex flex-col md:flex-row gap-2 max-w-lg mx-auto mb-20">
            <input type="email" name="pre_email" required placeholder="Enter email address" 
                   class="bg-transparent px-6 py-4 outline-none w-full text-white placeholder:text-slate-500">
            <button type="submit" class="bg-white text-black px-8 py-4 rounded-xl font-bold hover:bg-blue-50 transition">
                Get Started
            </button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 text-left">
            <div class="feature-card bg-glass border border-white/5 p-8 rounded-3xl transition-all">
                <i data-lucide="banknote" class="text-blue-400 mb-4 w-8 h-8"></i>
                <h3 class="text-lg font-bold mb-2">High Payouts</h3>
                <p class="text-slate-400 text-sm">Get paid for every valid click you generate from your network.</p>
            </div>
            <div class="feature-card bg-glass border border-white/5 p-8 rounded-3xl transition-all">
                <i data-lucide="bar-chart-3" class="text-violet-400 mb-4 w-8 h-8"></i>
                <h3 class="text-lg font-bold mb-2">Real-Time Stats</h3>
                <p class="text-slate-400 text-sm">Track your performance with a powerful, transparent dashboard.</p>
            </div>
            <div class="feature-card bg-glass border border-white/5 p-8 rounded-3xl transition-all">
                <i data-lucide="smartphone" class="text-emerald-400 mb-4 w-8 h-8"></i>
                <h3 class="text-lg font-bold mb-2">Reliable Payments</h3>
                <p class="text-slate-400 text-sm">Transparent system with timely payouts directly to your MoMo.</p>
            </div>
            <div class="feature-card bg-glass border border-white/5 p-8 rounded-3xl transition-all">
                <i data-lucide="trending-up" class="text-orange-400 mb-4 w-8 h-8"></i>
                <h3 class="text-lg font-bold mb-2">High Traffic Blog</h3>
                <p class="text-slate-400 text-sm">Lifestyle, fashion, and beauty content that attracts thousands.</p>
            </div>
        </div>
    </header>

    <footer class="py-12 text-center text-slate-500 text-sm border-t border-white/5 mt-10">
        <p class="mb-2 italic">Ready to Start Earning? Sign up today.</p>
        <p>&copy; 2025 Kointent Technologies. All rights reserved.</p>
    </footer>

    <script>
        lucide.createIcons();
        
        // HAMBURGER TOGGLE LOGIC
        document.getElementById('mobile-menu-btn').addEventListener('click', () => {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
    </script>
</body>
</html>
