<?php
// admin_requests.php
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
    <title>Requests - Disaster System</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script>
        tailwind.config = { darkMode: "class", theme: { extend: { colors: { "primary": "#137fec", "background-light": "#f6f7f8", "background-dark": "#101922" }, fontFamily: { "display": ["Public Sans", "sans-serif"] } } } }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 24px; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #1c2127; }
        ::-webkit-scrollbar-thumb { background: #314d68; border-radius: 10px; }
        #requests-map { height: 500px; width: 100%; z-index: 1; border-radius: 0.5rem; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-white overflow-hidden">
    <div class="flex h-screen w-full">
        
        <aside class="flex w-64 flex-col bg-[#1c2127] p-4 text-white border-r border-[#283039] shrink-0 transition-all duration-300">
            <div class="flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10 bg-primary flex items-center justify-center">
                        <span class="material-symbols-outlined">security</span>
                    </div>
                    <div class="flex flex-col">
                        <h1 class="text-white text-base font-medium leading-normal"><?php echo $user_full_name; ?></h1>
                        <p class="text-[#9dabb9] text-sm font-normal leading-normal"><?php echo $user_role; ?></p>
                    </div>
                </div>
                
                <nav class="flex flex-col gap-2">
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="dashboard.php">
                        <span class="material-symbols-outlined text-white">dashboard</span>
                        <p class="text-white text-sm font-medium leading-normal">Dashboard</p>
                    </a>
                    
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="residents.php">
                        <span class="material-symbols-outlined text-white">groups</span>
                        <p class="text-white text-sm font-medium leading-normal">Residents</p>
                    </a>

                    <a class="flex items-center gap-3 rounded-lg bg-primary/20 px-3 py-2" href="admin_requests.php">
                        <span class="material-symbols-outlined text-primary">emergency_share</span>
                        <p class="text-primary text-sm font-medium leading-normal">Requests</p>
                    </a>

                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="evacuation.php">
                        <span class="material-symbols-outlined text-white">warehouse</span>
                        <p class="text-white text-sm font-medium leading-normal">Evacuation</p>
                    </a>

                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="relief.php">
                        <span class="material-symbols-outlined text-white">volunteer_activism</span>
                        <p class="text-white text-sm font-medium leading-normal">Relief</p>
                    </a>
                </nav>
            </div>
            <div class="mt-auto flex flex-col gap-4">
                <div class="flex flex-col gap-1 border-t border-white/10 pt-4">
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="#">
                        <span class="material-symbols-outlined text-white">settings</span>
                        <p class="text-white text-sm font-medium leading-normal">Settings</p>
                    </a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="api/auth/logout_process.php">
                        <span class="material-symbols-outlined text-white">logout</span>
                        <p class="text-white text-sm font-medium leading-normal">Logout</p>
                    </a>
                </div>
            </div>
        </aside>

        <main class="flex flex-1 flex-col h-screen overflow-hidden bg-background-dark relative">
            <header class="flex justify-between items-center p-6 lg:p-8 border-b border-[#283039] bg-[#1c2127]/50 backdrop-blur-sm sticky top-0 z-10">
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Emergency Requests</h1>
                    <p class="text-sm text-[#9dabb9]">Manage incoming calls for help from residents.</p>
                </div>
                <div class="flex gap-3">
                    <button id="open-requests-map-btn" class="bg-[#e3a008] hover:bg-yellow-600 text-white font-semibold py-2.5 px-5 rounded-lg flex items-center gap-2 transition-all shadow-lg hover:scale-[1.02] active:scale-95">
                        <span class="material-symbols-outlined">map</span> Live Map
                    </button>
                    <button onclick="loadRequests()" class="flex items-center gap-2 text-sm font-bold text-primary hover:text-white transition-colors bg-[#283039] hover:bg-[#3b4754] px-4 py-2.5 rounded-lg">
                        <span class="material-symbols-outlined text-[18px]">refresh</span> Refresh
                    </button>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6 lg:p-8">
                <div class="bg-[#1c2127] rounded-xl border border-[#283039] overflow-hidden shadow-xl flex flex-col h-full">
                    <div class="overflow-auto flex-1">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-[#222831] text-[#9dabb9] sticky top-0 z-10 uppercase text-xs tracking-wider font-semibold">
                                <tr>
                                    <th class="px-6 py-4 border-b border-[#283039] text-left">Resident</th>
                                    <th class="px-6 py-4 border-b border-[#283039] text-left">Location</th>
                                    <th class="px-6 py-4 border-b border-[#283039] text-left">Type</th>
                                    <th class="px-6 py-4 border-b border-[#283039] text-left">Message</th>
                                    <th class="px-6 py-4 border-b border-[#283039] text-center">Proof</th>
                                    <th class="px-6 py-4 border-b border-[#283039] text-center">Status</th>
                                    <th class="px-6 py-4 border-b border-[#283039] text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody id="requests-table-body" class="divide-y divide-[#283039] text-sm text-slate-300">
                                <tr><td colspan="7" class="px-6 py-8 text-center text-slate-500 italic">Loading requests...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="status-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 hidden backdrop-blur-sm p-4 transition-opacity duration-300">
        <div class="bg-[#1c2127] w-full max-w-sm p-6 rounded-xl border border-[#283039] shadow-2xl text-center transform scale-100 transition-transform duration-300">
            <div id="status-icon-bg" class="mx-auto flex items-center justify-center size-14 rounded-full mb-4 transition-colors"><span id="status-icon" class="material-symbols-outlined !text-3xl"></span></div>
            <h3 id="status-title" class="text-xl font-bold text-white mb-2">Update Status?</h3>
            <p id="status-message" class="text-[#9dabb9] text-sm mb-6">Are you sure you want to proceed?</p>
            <div class="flex gap-3 justify-center">
                <button id="cancel-status-btn" class="px-5 py-2.5 rounded-lg text-sm font-bold text-[#9dabb9] hover:text-white hover:bg-[#283039] transition-colors">Cancel</button>
                <button id="confirm-status-btn" class="text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-lg transition-transform active:scale-95 flex items-center gap-2">Confirm</button>
            </div>
        </div>
    </div>

    <div id="requests-map-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 hidden backdrop-blur-sm p-4">
        <div class="bg-[#1c2127] w-full max-w-6xl h-[85vh] rounded-2xl border border-[#283039] shadow-2xl flex flex-col">
            <div class="flex justify-between items-center p-4 border-b border-[#283039] bg-[#222831]">
                <div class="flex items-center gap-3"><div class="bg-yellow-500/20 p-2 rounded-lg"><span class="material-symbols-outlined text-yellow-500">map</span></div><h3 class="text-xl font-bold text-white">Live Emergency Map</h3></div>
                <button class="close-modal-btn text-slate-400 hover:text-white"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="flex-1 relative">
                <div id="requests-map" class="w-full h-full bg-[#111418]"></div>
                <div class="absolute bottom-4 left-4 bg-[#1c2127]/90 p-3 rounded-lg border border-[#3b4754] z-[500]">
                    <p class="text-xs text-slate-400 font-bold mb-1">LEGEND</p>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-500 border border-white"></span><span class="text-xs text-white">Pending</span></div>
                    <div class="flex items-center gap-2 mt-1"><span class="w-3 h-3 rounded-full bg-yellow-500 border border-white"></span><span class="text-xs text-white">In Progress</span></div>
                    <div class="flex items-center gap-2 mt-1"><span class="w-3 h-3 rounded-full bg-green-500 border border-white"></span><span class="text-xs text-white">Completed</span></div>
                </div>
            </div>
        </div>
    </div>

    <div id="proof-modal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 hidden backdrop-blur-sm p-4" onclick="document.getElementById('proof-modal').classList.add('hidden'); document.getElementById('proof-modal').classList.remove('flex');">
        <div class="relative max-w-4xl w-full max-h-[90vh] flex flex-col items-center justify-center" onclick="event.stopPropagation();">
            <button id="close-proof-btn" class="absolute -top-10 right-0 text-white hover:text-red-500 transition-colors bg-black/50 rounded-full p-1">
                <span class="material-symbols-outlined !text-3xl">close</span>
            </button>
            <img id="proof-image" src="" alt="Proof of Request" class="rounded-lg shadow-2xl max-h-[85vh] object-contain border border-[#314d68] bg-black">
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="assets/js/admin_requests.js?v=<?php echo time(); ?>"></script>
</body>
</html>