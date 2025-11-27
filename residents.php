<?php
// residents.php
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
    <title>Residents - Disaster System</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

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
        #household-map { height: 250px; width: 100%; z-index: 1; border-radius: 0.5rem; }
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
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-white/5 text-[#9dabb9] hover:text-white transition-all group" href="dashboard.php">
                        <span class="material-symbols-outlined group-hover:text-primary transition-colors">dashboard</span>
                        <p class="text-sm font-medium">Dashboard</p>
                    </a>
                    <a class="flex items-center gap-3 rounded-lg bg-primary/10 border border-primary/20 px-3 py-2.5 text-white" href="residents.php">
                        <span class="material-symbols-outlined text-primary">groups</span>
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
                    <h1 class="text-2xl font-bold text-white tracking-tight">Resident Management</h1>
                    <p class="text-sm text-[#9dabb9]">Monitor households and population statistics.</p>
                </div>
                <button id="open-add-household-btn" class="bg-primary hover:bg-blue-600 text-white font-semibold py-2.5 px-5 rounded-lg flex items-center gap-2 transition-all shadow-lg shadow-blue-900/30 hover:scale-[1.02] active:scale-95">
                    <span class="material-symbols-outlined">add_location_alt</span> Add Household
                </button>
            </header>
            
            <div class="flex-1 overflow-y-auto p-6 lg:p-8 flex flex-col gap-6">
                
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

                <div class="bg-[#1c2127] rounded-xl border border-[#283039] overflow-hidden flex flex-col shadow-xl flex-1 min-h-0">
                    <div class="overflow-auto flex-1">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-[#283039] text-white sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#9dabb9] uppercase tracking-wider">ID</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#9dabb9] uppercase tracking-wider">Household Head</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#9dabb9] uppercase tracking-wider">Zone/Purok</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-[#9dabb9] uppercase tracking-wider">Members</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#9dabb9] uppercase tracking-wider">Location</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-[#9dabb9] uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                            <tbody id="households-table-body" class="divide-y divide-[#283039] text-sm text-slate-300">
                                <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500 italic">Loading data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="add-household-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 hidden backdrop-blur-sm p-4 transition-all duration-300">
        <div class="bg-[#1c2127] w-full max-w-3xl p-0 rounded-2xl border border-[#283039] shadow-2xl transform scale-100 overflow-hidden flex flex-col max-h-[90vh]">
            <div class="flex justify-between items-center p-5 border-b border-[#283039] bg-[#222831]">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/20 p-2 rounded-lg"><span class="material-symbols-outlined text-primary">add_location_alt</span></div>
                    <h3 class="text-lg font-bold text-white">New Household</h3>
                </div>
                <button class="close-modal-btn text-slate-400 hover:text-white transition-colors rounded-lg hover:bg-white/10 p-1"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="overflow-y-auto p-6 flex-1">
                <form id="add-household-form" class="flex flex-col gap-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-[#9dabb9] uppercase tracking-wide">Household Head</label>
                            <input type="text" name="household_head_name" required class="bg-[#111418] border border-[#3b4754] text-white text-sm rounded-lg p-3 focus:border-primary focus:outline-none">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-[#9dabb9] uppercase tracking-wide">Zone / Purok</label>
                            <input type="text" name="zone_purok" class="bg-[#111418] border border-[#3b4754] text-white text-sm rounded-lg p-3 focus:border-primary focus:outline-none">
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 rounded-lg border border-[#314d68] p-1 bg-[#111418]">
                        <label class="text-xs font-medium text-[#9dabb9] px-2 pt-1">Pin Location (Mati City)</label>
                        <div id="household-map" class="rounded-md border border-[#283039]"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="flex flex-col gap-1.5"><label class="text-xs font-bold text-[#9dabb9]">Latitude</label><input type="text" id="latitude" name="latitude" readonly class="bg-[#1c2127] border border-[#3b4754] text-gray-400 text-sm rounded-lg p-3 cursor-not-allowed font-mono"></div>
                        <div class="flex flex-col gap-1.5"><label class="text-xs font-bold text-[#9dabb9]">Longitude</label><input type="text" id="longitude" name="longitude" readonly class="bg-[#1c2127] border border-[#3b4754] text-gray-400 text-sm rounded-lg p-3 cursor-not-allowed font-mono"></div>
                        <div class="flex flex-col gap-1.5"><label class="text-xs font-bold text-[#9dabb9]">Notes</label><input type="text" name="address_notes" class="bg-[#111418] border border-[#3b4754] text-white text-sm rounded-lg p-3 focus:border-primary focus:outline-none"></div>
                    </div>
                </form>
            </div>
            <div class="p-5 border-t border-[#283039] bg-[#222831] flex justify-end gap-3">
                <button type="button" class="close-modal-btn px-5 py-2.5 rounded-lg text-sm font-bold text-slate-300 hover:bg-white/10">Cancel</button>
                <button type="submit" form="add-household-form" class="bg-primary hover:bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-lg">Save</button>
            </div>
        </div>
    </div>

    <div id="edit-household-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 hidden backdrop-blur-sm p-4">
        <div class="bg-[#1c2127] w-full max-w-lg p-6 rounded-xl border border-[#283039] shadow-2xl">
            <div class="flex justify-between items-center mb-5 border-b border-[#283039] pb-3">
                <h3 class="text-xl font-bold text-white">Edit Household</h3>
                <button class="close-modal-btn text-slate-400 hover:text-white"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form id="edit-household-form" class="flex flex-col gap-5">
    <input type="hidden" id="edit_household_id" name="id">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold text-[#9dabb9]">Household Head</label>
            <input type="text" id="edit_head_name" name="household_head_name" class="bg-[#111418] border border-[#3b4754] text-white text-sm rounded-lg p-3 focus:border-yellow-500 focus:outline-none">
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold text-[#9dabb9]">Zone / Purok</label>
            <input type="text" id="edit_zone" name="zone_purok" class="bg-[#111418] border border-[#3b4754] text-white text-sm rounded-lg p-3 focus:border-yellow-500 focus:outline-none">
        </div>
    </div>

    <div class="flex flex-col gap-2 rounded-lg border border-[#3b4754] p-1 bg-[#111418]">
        <label class="text-xs font-medium text-[#9dabb9] px-2 pt-1">Update Location</label>
        <div id="edit-household-map" class="rounded-md border border-[#283039]" style="height: 250px; width: 100%; z-index: 1;"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold text-[#9dabb9]">Latitude</label>
            <input type="text" id="edit_latitude" name="latitude" readonly class="bg-[#1c2127] border border-[#3b4754] text-gray-400 text-sm rounded-lg p-3 cursor-not-allowed font-mono">
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold text-[#9dabb9]">Longitude</label>
            <input type="text" id="edit_longitude" name="longitude" readonly class="bg-[#1c2127] border border-[#3b4754] text-gray-400 text-sm rounded-lg p-3 cursor-not-allowed font-mono">
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold text-[#9dabb9]">Address Notes</label>
            <textarea id="edit_address" name="address_notes" class="bg-[#111418] border border-[#3b4754] text-white text-sm rounded-lg p-3 focus:border-yellow-500 focus:outline-none resize-none"></textarea>
        </div>
    </div>

    <div class="mt-4 flex gap-3 justify-end">
        <button type="button" class="close-modal-btn px-5 py-2.5 rounded-lg text-sm font-bold text-slate-300 hover:bg-white/10">Cancel</button>
        <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-lg">Update Changes</button>
    </div>
</form>
        </div>
    </div>

    <div id="delete-confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 hidden backdrop-blur-sm p-4">
        <div class="bg-[#1c2127] w-full max-w-sm p-6 rounded-xl border border-red-500/30 shadow-2xl text-center">
            <div class="mx-auto flex items-center justify-center size-14 rounded-full bg-red-500/20 mb-4"><span class="material-symbols-outlined text-red-500 !text-3xl">warning</span></div>
            <h3 class="text-xl font-bold text-white mb-2">Delete Household?</h3>
            <p class="text-slate-300 text-sm mb-6">Are you sure you want to delete <span id="delete-household-name" class="font-bold text-white"></span>?</p>
            <div class="flex gap-3 justify-center">
                <button class="close-modal-btn px-5 py-2.5 rounded-lg text-sm font-bold text-slate-300 hover:bg-white/10">Cancel</button>
                <button id="confirm-delete-btn" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-lg">Delete</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="assets/js/main.js?v=103"></script>

</body>
</html>