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
                        <h1 class="text-white text-base font-medium leading-normal truncate w-40"><?php echo $user_full_name; ?></h1>
                        <p class="text-[#9dabb9] text-xs font-medium uppercase tracking-wider"><?php echo $user_role; ?></p>
                    </div>
                </div>
                
                <nav class="flex flex-col gap-1">
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-white/5 text-[#9dabb9] hover:text-white transition-all group" href="dashboard.php">
                        <span class="material-symbols-outlined group-hover:text-primary transition-colors">dashboard</span>
                        <p class="text-sm font-medium">Dashboard</p>
                    </a>
                    
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-white/5 text-[#9dabb9] hover:text-white transition-all group" href="residents.php">
                        <span class="material-symbols-outlined group-hover:text-primary transition-colors">groups</span>
                        <p class="text-sm font-medium">Residents</p>
                    </a>

                    <a class="flex items-center gap-3 rounded-lg bg-primary/10 border border-primary/20 px-3 py-2.5 text-white" href="admin_requests.php">
                        <span class="material-symbols-outlined text-primary">emergency_share</span>
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
            <div class="mt-auto p-6 border-t border-[#283039] flex flex-col gap-2">
                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-white/5 text-[#9dabb9] hover:text-white transition-all" href="#">
                    <span class="material-symbols-outlined">settings</span>
                    <p class="text-sm font-medium">Settings</p>
                </a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-red-500/10 text-[#9dabb9] hover:text-red-400 transition-all" href="api/auth/logout_process.php">
                    <span class="material-symbols-outlined">logout</span>
                    <p class="text-sm font-medium">Logout</p>
                </a>
            </div>
        </aside>

        <main class="flex flex-1 flex-col h-screen overflow-hidden bg-background-dark relative">
            
            <header class="flex justify-between items-center p-6 lg:p-8 border-b border-[#283039] bg-[#1c2127]/50 backdrop-blur-sm sticky top-0 z-10">
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Emergency Requests</h1>
                    <p class="text-sm text-[#9dabb9]">Manage incoming calls for help from residents.</p>
                </div>
                <button onclick="loadRequests()" class="flex items-center gap-2 text-sm font-bold text-primary hover:text-white transition-colors bg-[#283039] hover:bg-[#3b4754] px-4 py-2 rounded-lg">
                    <span class="material-symbols-outlined text-[18px]">refresh</span> Refresh List
                </button>
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
                                    <th class="px-6 py-4 border-b border-[#283039] text-center">Status</th>
                                    <th class="px-6 py-4 border-b border-[#283039] text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody id="requests-table-body" class="divide-y divide-[#283039] text-sm text-slate-300">
                                <tr><td colspan="6" class="px-6 py-8 text-center text-slate-500 italic">Loading requests...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="status-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 hidden backdrop-blur-sm p-4 transition-opacity duration-300">
        <div class="bg-[#1c2127] w-full max-w-sm p-6 rounded-xl border border-[#283039] shadow-2xl text-center transform scale-100 transition-transform duration-300">
            
            <div id="status-icon-bg" class="mx-auto flex items-center justify-center size-14 rounded-full mb-4 transition-colors">
                <span id="status-icon" class="material-symbols-outlined !text-3xl"></span>
            </div>

            <h3 id="status-title" class="text-xl font-bold text-white mb-2">Update Status?</h3>
            <p id="status-message" class="text-[#9dabb9] text-sm mb-6">Are you sure you want to proceed?</p>

            <div class="flex gap-3 justify-center">
                <button id="cancel-status-btn" class="px-5 py-2.5 rounded-lg text-sm font-bold text-[#9dabb9] hover:text-white hover:bg-[#283039] transition-colors">
                    Cancel
                </button>
                <button id="confirm-status-btn" class="text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-lg transition-transform active:scale-95 flex items-center gap-2">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script src="assets/js/admin_requests.js"></script>

</body>
</html>