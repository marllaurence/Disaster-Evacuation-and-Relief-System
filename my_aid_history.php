<?php
// my_aid_history.php
// --------------------------------------------------------
// Resident Page: View Received Relief Goods
// --------------------------------------------------------

include_once 'api/config/session.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header('Location: login.php');
    exit;
}

$user_full_name = htmlspecialchars($_SESSION['full_name']);
$user_role = htmlspecialchars($_SESSION['role']);
$profile_pic = $_SESSION['profile_picture_url'] ?? 'assets/img/default-avatar.png';
if (!file_exists($profile_pic)) $profile_pic = 'assets/img/default-avatar.png';
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Aid History - Disaster System</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    
    <script>
        tailwind.config = { darkMode: "class", theme: { extend: { colors: { "primary": "#137fec", "background-dark": "#101922" }, fontFamily: { "display": ["Public Sans"] } } } }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 24px; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #1a222c; }
        ::-webkit-scrollbar-thumb { background: #314d68; border-radius: 4px; }
    </style>
</head>
<body class="bg-[#f6f7f8] dark:bg-background-dark font-display text-white">
    <div class="relative flex w-full">
        
        <aside class="sticky top-0 flex h-screen w-64 flex-col bg-[#111418] p-4 shrink-0 border-r border-[#223649]">
            <div class="flex flex-col gap-4">
                
                <div class="flex items-center gap-3">
                    <img src="<?php echo $profile_pic; ?>" class="size-10 rounded-full bg-cover object-cover border border-slate-600">
                    <div class="flex flex-col">
                        <h1 class="text-base font-medium truncate w-40"><?php echo $user_full_name; ?></h1>
                        <p class="text-[#9dabb9] text-sm capitalize"><?php echo $user_role; ?></p>
                    </div>
                </div>

                <div class="flex flex-col gap-2 mt-4">
                    <a href="my_dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
                        <span class="material-symbols-outlined">dashboard</span>
                        <p class="text-sm font-medium">My Dashboard</p>
                    </a>
                    
                    <a href="my_household.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
                        <span class="material-symbols-outlined">home</span>
                        <p class="text-sm font-medium">My Household</p>
                    </a>
                    
                    <a href="my_aid_history.php" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/20 text-primary">
                        <span class="material-symbols-outlined">receipt_long</span>
                        <p class="text-sm font-medium">Aid History</p>
                    </a>
                    <a href="my_requests.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
    <span class="material-symbols-outlined">campaign</span>
    <p class="text-sm font-medium">My Requests</p>
</a>
                </div>
            </div>
            
            <div class="flex flex-col gap-4 mt-auto">
                <button id="sidebar-request-btn" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-10 px-4 bg-green-600 text-white text-sm font-bold hover:bg-green-700 transition-colors w-full">
                    <span class="material-symbols-outlined !text-[20px]">help</span>
                    <span class="truncate">Request Assistance</span>
                </button>

                <div class="flex flex-col gap-1">
                    <a href="my_settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
                        <span class="material-symbols-outlined">settings</span>
                        <p class="text-sm font-medium">Settings</p>
                    </a>
                    <a href="api/auth/logout_process.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
                        <span class="material-symbols-outlined">logout</span>
                        <p class="text-sm font-medium">Logout</p>
                    </a>
                </div>
            </div>
        </aside>

        <main class="flex-1 h-screen overflow-y-auto bg-background-dark p-8">
            <div class="max-w-5xl mx-auto">
                
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h1 class="text-3xl font-bold">Aid History</h1>
                        <p class="text-slate-400 mt-1">Record of all relief goods received by your household.</p>
                    </div>
                </div>

                <div class="bg-[#1a222c] rounded-xl border border-[#283039] overflow-hidden shadow-xl">
                    <table class="w-full text-left">
                        <thead class="bg-[#223649] text-white uppercase text-xs tracking-wider">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Item Name</th>
                                <th class="px-6 py-4 font-semibold">Date Received</th>
                                <th class="px-6 py-4 font-semibold text-right">Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="aid-history-table-body" class="divide-y divide-[#283039] text-sm">
                            <tr><td colspan="3" class="px-6 py-8 text-center text-slate-400">Loading history...</td></tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

    <div id="request-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 hidden backdrop-blur-sm p-4">
        <div class="bg-[#1a222c] w-full max-w-md p-6 rounded-xl border border-green-600/30 shadow-2xl transform transition-all scale-100">
            <div class="flex justify-between items-center mb-5 border-b border-[#283039] pb-3">
                <div class="flex items-center gap-3">
                    <div class="bg-green-600/20 p-2 rounded-lg">
                        <span class="material-symbols-outlined text-green-500">emergency_share</span>
                    </div>
                    <h3 class="text-xl font-bold text-white">Request Help</h3>
                </div>
                <button id="close-request-btn" class="text-slate-400 hover:text-white transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="request-form" class="flex flex-col gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-400">Type of Assistance</label>
                    <select name="request_type" required class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-3 focus:border-green-500 focus:outline-none">
                        <option value="" disabled selected>Select Request Type</option>
                        <option value="Food & Water">Food & Water</option>
                        <option value="Medical Assistance">Medical Assistance</option>
                        <option value="Rescue / Evacuation">Rescue / Evacuation</option>
                        <option value="Clothing / Shelter">Clothing / Shelter</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-400">Details (Location, Condition, etc.)</label>
                    <textarea name="description" required rows="4" class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-3 focus:border-green-500 focus:outline-none resize-none" placeholder="Please describe your situation..."></textarea>
                </div>
                <div class="mt-2 p-3 bg-green-900/20 border border-green-900/50 rounded-lg flex gap-3 items-start">
                    <span class="material-symbols-outlined text-green-500 text-sm mt-0.5">info</span>
                    <p class="text-xs text-green-200 leading-relaxed">Your location and contact info will be automatically sent to the command center.</p>
                </div>
                <div class="mt-4 flex gap-3">
                    <button type="button" id="cancel-request-btn" class="flex-1 bg-[#283039] hover:bg-[#323c4a] text-white font-medium rounded-lg text-sm px-5 py-2.5 text-center">Cancel</button>
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg text-sm px-5 py-2.5 text-center shadow-lg shadow-green-900/20">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <div id="success-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 hidden backdrop-blur-sm p-4">
        <div class="bg-[#1a222c] w-full max-w-sm p-6 rounded-xl border border-green-500/30 shadow-2xl transform transition-all scale-100 text-center">
            <div class="mx-auto flex items-center justify-center size-14 rounded-full bg-green-500/20 mb-4">
                <span class="material-symbols-outlined text-green-500 !text-3xl">check_circle</span>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Success!</h3>
            <p class="text-slate-300 text-sm mb-6">Action completed successfully.</p>
            <button id="close-success-btn" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg text-sm px-5 py-2.5 transition-colors">
                Okay, Great!
            </button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/my_dashboard.js"></script>
</body>
</html>