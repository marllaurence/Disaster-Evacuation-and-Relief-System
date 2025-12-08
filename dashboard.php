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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Command Center - Disaster System</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#137fec", "background-light": "#f6f7f8", "background-dark": "#101922", "panel-dark": "#111a22", "border-dark": "#324d67" },
                    fontFamily: { "display": ["Public Sans", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        
        /* FIXED MAP OVERLAP */
        #dashboard-map { height: 100%; min-height: 500px; width: 100%; z-index: 0; } 
        
        /* Sticky Header Z-Index must be higher than map */
        .sticky-header { position: sticky; top: 0; z-index: 50; }
        
        /* Sidebar Z-Index must be highest */
        aside { z-index: 60; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #111a22; }
        ::-webkit-scrollbar-thumb { background: #324d67; border-radius: 10px; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-gray-900 dark:text-gray-100 overflow-hidden">

    <div class="flex h-screen w-full">
    
        <aside class="flex w-64 flex-col bg-[#1c2127] p-4 text-white border-r border-[#283039] shrink-0 transition-all duration-300">
            <div class="flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <div class="bg-gradient-to-br from-primary to-blue-600 aspect-square rounded-xl size-10 flex items-center justify-center shadow-lg shadow-blue-900/20">
                        <span class="material-symbols-outlined text-white">security</span>
                    </div>
                    <div class="flex flex-col">
                        <h1 class="text-white text-base font-medium leading-normal"><?php echo $user_full_name; ?></h1>
                        <p class="text-[#9dabb9] text-sm font-normal leading-normal capitalize"><?php echo $user_role; ?></p>
                    </div>
                </div>
                
                <nav class="flex flex-col gap-2">
                    <a class="flex items-center gap-3 rounded-lg bg-primary/20 px-3 py-2" href="dashboard.php">
                        <span class="material-symbols-outlined text-primary fill">dashboard</span><p class="text-primary text-sm font-medium">Dashboard</p>
                    </a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="residents.php">
                        <span class="material-symbols-outlined text-white">groups</span><p class="text-white text-sm font-medium">Residents</p>
                    </a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-white/10" href="admin_requests.php">
                        <span class="material-symbols-outlined group-hover:text-red-400 transition-colors">emergency_share</span><p class="text-sm font-medium">Requests</p>
                    </a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="evacuation.php">
                        <span class="material-symbols-outlined text-white">warehouse</span><p class="text-white text-sm font-medium">Evacuation</p>
                    </a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="relief.php">
                        <span class="material-symbols-outlined text-white">volunteer_activism</span><p class="text-white text-sm font-medium">Relief</p>
                    </a>
                </nav>
            </div>
            <div class="mt-auto flex flex-col gap-4">
                <div class="flex flex-col gap-1 border-t border-white/10 pt-4">
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="#">
                        <span class="material-symbols-outlined text-white">settings</span><p class="text-white text-sm font-medium">Settings</p>
                    </a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="api/auth/logout_process.php">
                        <span class="material-symbols-outlined text-white">logout</span><p class="text-white text-sm font-medium">Logout</p>
                    </a>
                </div>
            </div>
        </aside>

        <main class="flex flex-1 flex-col overflow-y-auto">
            
            <div class="p-6 border-b border-border-dark bg-[#1c2127] sticky-header shadow-md">
                <h1 class="text-3xl font-bold text-white tracking-tight">Command Center</h1>
                <p class="text-gray-400 text-sm mt-1">Real-time monitoring and resource management.</p>
            </div>

            <div class="p-6 flex flex-col gap-6">
                
                <div class="flex flex-wrap gap-4">
                    <div class="flex min-w-[158px] flex-1 flex-col gap-2 rounded-xl bg-panel-dark p-6 border border-border-dark shadow-sm">
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Total Households</p>
                        <p id="stats-total-households" class="text-white tracking-tight text-3xl font-bold leading-tight">0</p>
                    </div>
                    <div class="flex min-w-[158px] flex-1 flex-col gap-2 rounded-xl bg-panel-dark p-6 border border-border-dark shadow-sm">
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Total Residents</p>
                        <p id="stats-total-residents" class="text-white tracking-tight text-3xl font-bold leading-tight">0</p>
                    </div>
                    <div class="flex min-w-[158px] flex-1 flex-col gap-2 rounded-xl bg-panel-dark p-6 border border-border-dark shadow-sm">
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Active Evacuees</p>
                        <p id="stats-residents-evacuated" class="text-primary tracking-tight text-3xl font-bold leading-tight">0</p>
                    </div>
                    <div class="flex min-w-[158px] flex-1 flex-col gap-2 rounded-xl bg-panel-dark p-6 border border-border-dark shadow-sm">
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Affected Families</p>
                        <p id="stats-affected-households" class="text-red-400 tracking-tight text-3xl font-bold leading-tight">0</p>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-6 h-[600px]">
                    
                    <div class="flex w-full lg:w-3/5 flex-col rounded-xl bg-panel-dark border border-border-dark overflow-hidden shadow-lg relative">
                        <div id="dashboard-map" class="w-full h-full bg-[#192633]"></div>

                        <div class="absolute bottom-4 left-4 z-[10] flex flex-col gap-2 rounded-lg bg-[#192633]/90 backdrop-blur-sm p-3 border border-border-dark shadow-lg">
                            <h4 class="text-white font-bold text-xs uppercase mb-1">Legend</h4>
                            <div class="flex items-center gap-2 text-white text-xs"><span class="w-2 h-2 rounded-full bg-green-500"></span> Evacuation Center</div>
                            <div class="flex items-center gap-2 text-white text-xs"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Resident (Safe)</div>
                            <div class="flex items-center gap-2 text-white text-xs"><span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span> Affected (Request)</div>
                        </div>
                    </div>

                    <div class="flex w-full lg:w-2/5 flex-col gap-6">
                        <div class="flex h-1/2 flex-col rounded-xl bg-panel-dark border border-border-dark shadow-md overflow-hidden">
                            <div class="flex items-center justify-between p-4 border-b border-border-dark bg-[#192633]">
                                <h2 class="text-sm font-bold text-white uppercase">Evacuation Status</h2>
                                <a href="evacuation.php" class="text-xs font-bold text-primary hover:text-white">MANAGE</a>
                            </div>
                            <div class="flex-1 overflow-auto">
                                <table id="centers-table" class="w-full text-left">
                                    <tbody id="centers-table-body" class="text-sm text-gray-300"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="flex h-1/2 flex-col rounded-xl bg-panel-dark border border-border-dark shadow-md overflow-hidden">
                            <div class="flex items-center justify-between p-4 border-b border-border-dark bg-[#192633]">
                                <h2 class="text-sm font-bold text-white uppercase">Relief Inventory</h2>
                                <a href="relief.php" class="text-xs font-bold text-green-500 hover:text-white">MANAGE</a>
                            </div>
                            <div class="flex-1 overflow-auto">
                                <table id="inventory-table" class="w-full text-left">
                                    <tbody id="inventory-table-body" class="text-sm text-gray-300"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>