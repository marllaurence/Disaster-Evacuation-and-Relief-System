<?php
// evacuation.php
include_once 'api/config/session.php';

// Security Check
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
    <title>Evacuation Management - Disaster System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script>
        tailwind.config = { darkMode: "class", theme: { extend: { colors: { "primary": "#137fec", "background-dark": "#101922" }, fontFamily: { "display": ["Public Sans"] } } } }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 24px; }
        #center-map { height: 250px; width: 100%; border-radius: 0.5rem; z-index: 1; border: 1px solid #3b4754; }
        #all-centers-map { height: 500px; width: 100%; z-index: 1; border-radius: 0.5rem; }
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: #0f161d; border-radius: 4px; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; border: 2px solid #0f161d; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        thead th { position: sticky; top: 0; z-index: 20; }
    </style>
</head>
<body class="bg-[#f6f7f8] dark:bg-background-dark font-display text-white overflow-hidden">

<div class="flex h-screen w-full">

    <aside class="flex w-64 flex-col bg-[#1c2127] p-4 text-white border-r border-[#283039] shrink-0 transition-all duration-300">
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-3">
                <div class="bg-primary rounded-full size-10 flex items-center justify-center"><span class="material-symbols-outlined">security</span></div>
                <div class="flex flex-col"><h1 class="text-base font-medium"><?php echo $user_full_name; ?></h1><p class="text-[#9dabb9] text-sm font-normal leading-normal"><?php echo $user_role; ?></p></div>
            </div>
            
            <nav class="flex flex-col gap-2">
                <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="dashboard.php">
                    <span class="material-symbols-outlined text-white">dashboard</span><p class="text-white text-sm font-medium leading-normal">Dashboard</p>
                </a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="residents.php">
                    <span class="material-symbols-outlined text-white">groups</span><p class="text-white text-sm font-medium leading-normal">Residents</p>
                </a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-white/5 text-[#9dabb9] hover:text-white transition-all group" href="admin_requests.php">
                    <span class="material-symbols-outlined group-hover:text-red-400 transition-colors">emergency_share</span><p class="text-sm font-medium">Requests</p>
                </a>
                <a class="flex items-center gap-3 rounded-lg bg-primary/20 px-3 py-2" href="evacuation.php">
                    <span class="material-symbols-outlined text-primary">warehouse</span><p class="text-primary text-sm font-medium leading-normal">Evacuation</p>
                </a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="relief.php">
                    <span class="material-symbols-outlined text-white">volunteer_activism</span><p class="text-white text-sm font-medium leading-normal">Relief</p>
                </a>
            </nav>
        </div>
        <div class="mt-auto flex flex-col gap-4">
            <div class="flex flex-col gap-1 border-t border-white/10 pt-4">
                <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="#">
                    <span class="material-symbols-outlined text-white">settings</span><p class="text-white text-sm font-medium leading-normal">Settings</p>
                </a>
                <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="api/auth/logout_process.php">
                    <span class="material-symbols-outlined text-white">logout</span><p class="text-white text-sm font-medium leading-normal">Logout</p>
                </a>
            </div>
        </div>
    </aside>

    <main class="flex flex-1 flex-col h-screen overflow-hidden bg-background-dark relative">
        <header class="flex justify-between items-center p-6 lg:p-8 border-b border-[#283039] bg-[#1c2127]/50 backdrop-blur-sm sticky top-0 z-10">
            <div><h1 class="text-2xl font-bold text-white tracking-tight">Evacuation Centers</h1><p class="text-sm text-[#9dabb9]">Manage safe zones and occupancy.</p></div>
            <div class="flex gap-3"><button id="open-all-centers-map-btn" class="bg-[#e3a008] hover:bg-yellow-600 text-white font-semibold py-2.5 px-5 rounded-lg flex items-center gap-2 transition-all shadow-lg hover:scale-[1.02] active:scale-95"><span class="material-symbols-outlined">map</span> View Map</button></div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 lg:p-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1">
                    <div class="bg-[#1c2127] p-6 rounded-xl border border-[#283039] sticky top-0">
                        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-primary">add_location_alt</span> Add New Center</h2>
                        <form id="add-center-form" class="flex flex-col gap-4">
                            <div class="flex flex-col gap-1"><label class="text-xs font-bold text-[#9dabb9] uppercase">Center Name</label><input type="text" name="center_name" placeholder="e.g. City Gymnasium" class="w-full h-10 px-3 rounded-lg bg-[#111418] border border-[#3b4754] text-white focus:border-primary outline-none text-sm" required></div>
                            <div class="flex flex-col gap-1"><label class="text-xs font-bold text-[#9dabb9] uppercase">Address</label><input type="text" name="address" placeholder="Street, Purok" class="w-full h-10 px-3 rounded-lg bg-[#111418] border border-[#3b4754] text-white focus:border-primary outline-none text-sm" required></div>
                            <div class="flex flex-col gap-1"><label class="text-xs font-bold text-[#9dabb9] uppercase">Capacity</label><input type="number" name="capacity" placeholder="Max people" class="w-full h-10 px-3 rounded-lg bg-[#111418] border border-[#3b4754] text-white focus:border-primary outline-none text-sm" required></div>
                            <div class="flex flex-col gap-2"><label class="text-xs font-bold text-[#9dabb9] uppercase">Pin Location</label><div id="center-map" class="border border-[#3b4754]"></div><div class="grid grid-cols-2 gap-2"><input type="text" id="latitude" name="latitude" readonly placeholder="Lat" class="w-full h-8 px-2 rounded bg-[#111418] text-gray-500 text-xs font-mono cursor-not-allowed"><input type="text" id="longitude" name="longitude" readonly placeholder="Lng" class="w-full h-8 px-2 rounded bg-[#111418] text-gray-500 text-xs font-mono cursor-not-allowed"></div></div>
                            <label class="flex items-center gap-2 text-sm text-white cursor-pointer"><input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 text-primary rounded bg-[#111418] border-[#3b4754]"> Set as Active Status</label>
                            <button type="submit" class="bg-primary hover:bg-blue-600 rounded-lg h-10 text-white text-sm font-bold flex items-center justify-center gap-2 shadow-lg transition-all">Save Center</button>
                        </form>
                        <div id="form-message" class="mt-4 text-xs text-center"></div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-[#1c2127] rounded-xl border border-[#283039] overflow-hidden shadow-xl flex flex-col h-[600px]">
                        <div class="p-5 border-b border-[#283039]"><h2 class="text-lg font-bold text-white">Center Status</h2></div>
                        <div class="overflow-x-auto overflow-y-auto flex-1 pb-4">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-[#222831] text-[#9dabb9] uppercase text-xs font-bold sticky top-0 z-10">
                                    <tr><th class="px-6 py-4 text-left">Name</th><th class="px-6 py-4 text-left">Status</th><th class="px-6 py-4 text-center">Occupancy</th><th class="px-6 py-4 text-center">Remaining</th><th class="px-6 py-4 text-left">Address</th><th class="px-6 py-4 text-left">Location</th><th class="px-6 py-4 text-right">Actions</th></tr>
                                </thead>
                                <tbody id="centers-table-body" class="divide-y divide-[#283039] text-sm text-slate-300"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<div id="edit-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 hidden backdrop-blur-sm p-4">
    <div class="w-full max-w-md rounded-xl bg-[#1c2127] p-6 shadow-xl border border-[#283039]">
       <div class="flex justify-between mb-4"><h2 class="text-lg font-bold text-white">Edit Center</h2><button class="cancel-modal-btn text-gray-400"><span class="material-symbols-outlined">close</span></button></div>
       <form id="edit-center-form" class="space-y-4">
           <input type="hidden" name="id" id="edit_center_id">
           <div><label class="text-xs text-gray-400">Name</label><input type="text" name="center_name" id="edit_center_name" class="w-full rounded-lg p-2 bg-[#111418] text-white border border-[#3b4754]"></div>
           <div><label class="text-xs text-gray-400">Address</label><input type="text" name="address" id="edit_address" class="w-full rounded-lg p-2 bg-[#111418] text-white border border-[#3b4754]"></div>
           <div><label class="text-xs text-gray-400">Capacity</label><input type="number" name="capacity" id="edit_capacity" class="w-full rounded-lg p-2 bg-[#111418] text-white border border-[#3b4754]"></div>
           <div class="flex gap-2 items-center"><input type="checkbox" name="is_active" id="edit_is_active" value="1" class="h-4 w-4"><label class="text-white text-sm">Active Status</label></div>
           <div class="flex justify-end gap-2"><button type="button" class="cancel-modal-btn bg-[#283039] text-white px-4 py-2 rounded-lg text-sm font-bold">Cancel</button><button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold">Update</button></div>
       </form>
    </div>
</div>

<div id="delete-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 hidden backdrop-blur-sm p-4">
    <div class="w-full max-w-sm rounded-xl bg-[#1c2127] p-6 shadow-xl border border-red-500/30 text-center">
        <div class="mx-auto flex items-center justify-center size-14 rounded-full bg-red-500/20 mb-4"><span class="material-symbols-outlined text-red-500 !text-3xl">warning</span></div>
        <h2 class="text-lg font-bold text-white mb-2">Delete Center?</h2>
        <p class="text-gray-400 text-sm mb-4">This action cannot be undone.</p>
        <input type="hidden" id="delete_center_id">
        <div class="flex justify-center gap-2"><button class="cancel-delete-modal-btn bg-[#283039] text-white px-4 py-2 rounded-lg text-sm font-bold">Cancel</button><button id="confirm-delete-btn" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-lg">Delete</button></div>
    </div>
</div>

<div id="all-centers-map-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 hidden backdrop-blur-sm p-4">
    <div class="bg-[#1c2127] w-full max-w-6xl h-[85vh] rounded-2xl border border-[#283039] shadow-2xl flex flex-col">
        <div class="flex justify-between items-center p-4 border-b border-[#283039] bg-[#222831]">
            <div class="flex items-center gap-3"><div class="bg-yellow-500/20 p-2 rounded-lg"><span class="material-symbols-outlined text-yellow-500">map</span></div><h3 class="text-xl font-bold text-white">Evacuation Map</h3></div>
            <button class="close-modal-btn text-slate-400 hover:text-white"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="flex-1 relative">
            <div id="all-centers-map" class="w-full h-full bg-[#111418]"></div>
            <div class="absolute bottom-4 left-4 bg-[#1c2127]/90 p-3 rounded-lg border border-[#3b4754] z-[500]">
                <p class="text-xs text-slate-400 font-bold mb-1">LEGEND</p>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-green-500 border border-white"></span><span class="text-xs text-white">Active Center</span></div>
                <div class="flex items-center gap-2 mt-1"><span class="w-3 h-3 rounded-full bg-red-500 border border-white"></span><span class="text-xs text-white">Inactive Center</span></div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="assets/js/evacuation.js"></script>
</body>
</html>