<?php
// dashboard.php
include_once 'api/config/session.php';

// Security: Admin Only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$user_full_name = htmlspecialchars($_SESSION['full_name']);
$user_role = htmlspecialchars($_SESSION['role']);
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Dashboard - Disaster System</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#137fec", "background-light": "#f6f7f8", "background-dark": "#101922" },
                    fontFamily: { "display": ["Public Sans", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 24px; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #1c2127; }
        ::-webkit-scrollbar-thumb { background: #314d68; border-radius: 10px; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-white overflow-hidden">

    <div class="flex h-screen w-full">
    
        <aside class="flex w-64 flex-col bg-[#1c2127] border-r border-[#283039] shrink-0 transition-all duration-300">
            <div class="flex flex-col gap-6 p-6">
                <div class="flex items-center gap-3">
                    <div class="bg-gradient-to-br from-primary to-blue-600 aspect-square rounded-xl size-10 flex items-center justify-center shadow-lg shadow-blue-900/20">
                        <span class="material-symbols-outlined text-white">security</span>
                    </div>
                    <div class="flex flex-col">
                        <h1 class="text-white text-sm font-bold tracking-wide"><?php echo $user_full_name; ?></h1>
                        <p class="text-[#9dabb9] text-xs font-medium uppercase tracking-wider"><?php echo $user_role; ?></p>
                    </div>
                </div>
                
                <nav class="flex flex-col gap-1">
                    <a class="flex items-center gap-3 rounded-lg bg-primary/10 border border-primary/20 px-3 py-2.5 text-white" href="dashboard.php">
                        <span class="material-symbols-outlined text-primary">dashboard</span>
                        <p class="text-sm font-medium">Dashboard</p>
                    </a>
                    
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-white/5 text-[#9dabb9] hover:text-white transition-all group" href="residents.php">
                        <span class="material-symbols-outlined group-hover:text-primary transition-colors">groups</span>
                        <p class="text-sm font-medium">Residents</p>
                    </a>

                    <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-white/5 text-[#9dabb9] hover:text-white transition-all group" href="admin_requests.php">
                        <span class="material-symbols-outlined group-hover:text-red-400 transition-colors">emergency_share</span>
                        <p class="text-sm font-medium">Requests</p>
                    </a>

                    <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-white/5 text-[#9dabb9] hover:text-white transition-all group" href="evacuation.php">
                        <span class="material-symbols-outlined group-hover:text-orange-400 transition-colors">warehouse</span>
                        <p class="text-sm font-medium">Evacuation</p>
                    </a>

                    <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-white/5 text-[#9dabb9] hover:text-white transition-all group" href="relief.php">
                        <span class="material-symbols-outlined group-hover:text-green-400 transition-colors">volunteer_activism</span>
                        <p class="text-sm font-medium">Relief</p>
                    </a>
                </nav>
            </div>
            <div class="mt-auto p-6 border-t border-[#283039]">
                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-red-500/10 text-[#9dabb9] hover:text-red-400 transition-all" href="api/auth/logout_process.php">
                    <span class="material-symbols-outlined">logout</span>
                    <p class="text-sm font-medium">Logout</p>
                </a>
            </div>
        </aside>

        <main class="flex flex-1 flex-col gap-6 p-6 overflow-y-auto bg-background-dark">
            
            <h1 class="text-2xl font-bold leading-tight text-white">Command Center Dashboard</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="flex flex-col gap-1 rounded-xl bg-[#1c2127] p-5 border border-[#283039] border-l-4 border-l-primary shadow-sm hover:shadow-md transition-shadow">
                    <p class="text-[#9dabb9] text-xs font-bold uppercase tracking-wider">Total Households</p>
                    <p id="stats-total-households" class="text-white text-3xl font-bold">0</p>
                </div>
                <div class="flex flex-col gap-1 rounded-xl bg-[#1c2127] p-5 border border-[#283039] border-l-4 border-l-green-500 shadow-sm hover:shadow-md transition-shadow">
                    <p class="text-[#9dabb9] text-xs font-bold uppercase tracking-wider">Total Residents</p>
                    <p id="stats-total-residents" class="text-white text-3xl font-bold">0</p>
                </div>
                <div class="flex flex-col gap-1 rounded-xl bg-[#1c2127] p-5 border border-[#283039] border-l-4 border-l-orange-500 shadow-sm hover:shadow-md transition-shadow">
                    <p class="text-[#9dabb9] text-xs font-bold uppercase tracking-wider">Affected</p>
                    <p id="stats-affected-households" class="text-white text-3xl font-bold">0</p>
                </div>
                <div class="flex flex-col gap-1 rounded-xl bg-[#1c2127] p-5 border border-[#283039] border-l-4 border-l-red-500 shadow-sm hover:shadow-md transition-shadow">
                    <p class="text-[#9dabb9] text-xs font-bold uppercase tracking-wider">Evacuated</p>
                    <p id="stats-residents-evacuated" class="text-white text-3xl font-bold">0</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
                <div class="lg:col-span-1 bg-[#1c2127] rounded-xl border border-[#283039] overflow-hidden flex flex-col h-96">
                    <h2 class="text-lg font-semibold text-white p-4 border-b border-[#283039]">Evacuation Center Status</h2>
                    <div class="overflow-auto flex-1">
                        <table id="centers-table" class="w-full text-left border-collapse">
                            <thead class="bg-[#222831] text-[#9dabb9] sticky top-0 z-10 uppercase text-xs tracking-wider font-semibold">
                                <tr>
                                    <th class="px-4 py-3 text-left">Center Name</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                    <th class="px-4 py-3 text-left">Occupancy</th>
                                </tr>
                            </thead>
                            <tbody id="centers-table-body" class="divide-y divide-[#3b4754] text-sm text-slate-300">
                                </tbody>
                        </table>
                    </div>
                </div>

                <div class="lg:col-span-1 bg-[#1c2127] rounded-xl border border-[#283039] overflow-hidden flex flex-col h-96">
                    <h2 class="text-lg font-semibold text-white p-4 border-b border-[#283039]">Inventory Stock Levels</h2>
                    <div class="overflow-auto flex-1">
                        <table id="inventory-table" class="w-full text-left border-collapse">
                            <thead class="bg-[#222831] text-[#9dabb9] sticky top-0 z-10 uppercase text-xs tracking-wider font-semibold">
                                <tr>
                                    <th class="px-4 py-3 text-left">Item Name</th>
                                    <th class="px-4 py-3 text-left">Stock</th>
                                    <th class="px-4 py-3 text-left">Unit</th>
                                </tr>
                            </thead>
                            <tbody id="inventory-table-body" class="divide-y divide-[#3b4754] text-sm text-slate-300">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/dashboard.js"></script>

</body>
</html>