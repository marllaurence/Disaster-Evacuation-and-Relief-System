<?php
// my_household.php
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
    <title>My Household - Disaster System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script>
        tailwind.config = { darkMode: "class", theme: { extend: { colors: { "primary": "#137fec", "background-dark": "#101922" }, fontFamily: { "display": ["Public Sans"] } } } }
    </script>
</head>
<body class="bg-[#f6f7f8] dark:bg-background-dark font-display text-white">
    <div class="relative flex w-full">
        
        <aside class="sticky top-0 flex h-screen w-64 flex-col bg-[#111418] p-4 shrink-0 border-r border-[#223649]">
            <div class="flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <img src="<?php echo $profile_pic; ?>" class="size-10 rounded-full bg-cover object-cover border border-slate-600">
                    <div class="flex flex-col"><h1 class="text-base font-medium truncate w-40"><?php echo $user_full_name; ?></h1><p class="text-[#9dabb9] text-sm capitalize"><?php echo $user_role; ?></p></div>
                </div>
                <div class="flex flex-col gap-2 mt-4">
                    <a href="my_dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
                        <span class="material-symbols-outlined">dashboard</span><p class="text-sm font-medium">My Dashboard</p>
                    </a>
                    <a href="my_household.php" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/20 text-primary">
                        <span class="material-symbols-outlined">home</span><p class="text-sm font-medium">My Household</p>
                    </a>
                    <a href="my_aid_history.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
                        <span class="material-symbols-outlined">receipt_long</span><p class="text-sm font-medium">Aid History</p>
                    </a>
                </div>
            </div>
            
            <div class="flex flex-col gap-4 mt-auto">
                <button id="sidebar-request-btn" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-10 px-4 bg-green-600 text-white text-sm font-bold hover:bg-green-700 transition-colors w-full">
                    <span class="material-symbols-outlined !text-[20px]">help</span>
                    <span class="truncate">Request Assistance</span>
                </button>

                <div class="flex flex-col gap-1">
                    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
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
                        <h1 class="text-3xl font-bold">My Household</h1>
                        <p class="text-slate-400 mt-1">Manage your family members and their details.</p>
                    </div>
                    <button id="open-add-member-modal" class="bg-primary hover:bg-blue-600 text-white font-bold py-2.5 px-5 rounded-lg flex items-center gap-2 transition-colors shadow-lg">
                        <span class="material-symbols-outlined">person_add</span> Add Member
                    </button>
                </div>

                <div class="bg-[#1a222c] p-6 rounded-xl border border-[#283039] mb-6 flex items-start gap-4">
                    <div class="p-3 bg-[#223649] rounded-lg"><span class="material-symbols-outlined text-slate-300">location_on</span></div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Registered Address</h3>
                        <p id="household-address" class="text-slate-300 text-sm mt-1">Loading...</p>
                    </div>
                </div>

                <div class="bg-[#1a222c] rounded-xl border border-[#283039] overflow-hidden shadow-xl">
                    <table class="w-full text-left">
                        <thead class="bg-[#223649] text-white uppercase text-xs tracking-wider">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Name</th>
                                <th class="px-6 py-4 font-semibold">Birthdate</th>
                                <th class="px-6 py-4 font-semibold">Gender</th>
                                <th class="px-6 py-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="my-household-list" class="divide-y divide-[#283039] text-sm">
                            <tr><td colspan="4" class="px-6 py-8 text-center text-slate-400">Loading members...</td></tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

    <div id="add-member-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 hidden backdrop-blur-sm p-4">
        <div class="bg-[#1a222c] w-full max-w-md p-6 rounded-xl border border-[#283039] shadow-2xl">
            <div class="flex justify-between items-center mb-5 border-b border-[#283039] pb-3">
                <h3 class="text-xl font-bold text-white">Family Member</h3>
                <button id="close-modal-btn" class="text-slate-400 hover:text-white"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form id="add-member-form" class="flex flex-col gap-4">
                <input type="hidden" name="member_id" id="member_id">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1"><label class="text-xs text-slate-400">First Name</label><input type="text" name="first_name" required class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-2.5 outline-none focus:border-primary"></div>
                    <div class="flex flex-col gap-1"><label class="text-xs text-slate-400">Last Name</label><input type="text" name="last_name" required class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-2.5 outline-none focus:border-primary"></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1"><label class="text-xs text-slate-400">Birthdate</label><input type="date" name="birthdate" class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-2.5 outline-none focus:border-primary"></div>
                    <div class="flex flex-col gap-1"><label class="text-xs text-slate-400">Gender</label><select name="gender" class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-2.5 outline-none focus:border-primary"><option value="Male">Male</option><option value="Female">Female</option><option value="Other">Other</option></select></div>
                </div>
                <div class="flex flex-col gap-1"><label class="text-xs text-slate-400">Remarks</label><textarea name="remarks" rows="2" class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-2.5 outline-none resize-none"></textarea></div>
                <div class="flex gap-4 p-2 bg-[#111418] rounded-lg border border-[#314d68]">
                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_pwd" value="1" class="w-4 h-4 rounded bg-[#1a222c] text-primary"><span class="text-sm select-none">PWD</span></label>
                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_senior" value="1" class="w-4 h-4 rounded bg-[#1a222c] text-primary"><span class="text-sm select-none">Senior</span></label>
                </div>
                <div class="mt-4 flex gap-3"><button type="button" id="cancel-modal-btn" class="flex-1 bg-[#283039] hover:bg-[#323c4a] text-white rounded-lg text-sm py-2.5">Cancel</button><button type="submit" class="flex-1 bg-primary hover:bg-blue-600 text-white rounded-lg text-sm py-2.5">Save</button></div>
            </form>
        </div>
    </div>

    <div id="request-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 hidden backdrop-blur-sm p-4">
        <div class="bg-[#1a222c] w-full max-w-md p-6 rounded-xl border border-green-600/30 shadow-2xl transform transition-all scale-100">
            <div class="flex justify-between items-center mb-5 border-b border-[#283039] pb-3">
                <div class="flex items-center gap-3"><div class="bg-green-600/20 p-2 rounded-lg"><span class="material-symbols-outlined text-green-500">emergency_share</span></div><h3 class="text-xl font-bold text-white">Request Help</h3></div>
                <button id="close-request-btn" class="text-slate-400 hover:text-white transition-colors"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form id="request-form" class="flex flex-col gap-4">
                <div class="flex flex-col gap-1"><label class="text-xs font-medium text-slate-400">Type of Assistance</label><select name="request_type" required class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-3 focus:border-green-500 focus:outline-none"><option value="" disabled selected>Select Request Type</option><option value="Food & Water">Food & Water</option><option value="Medical Assistance">Medical Assistance</option><option value="Rescue / Evacuation">Rescue / Evacuation</option><option value="Clothing / Shelter">Clothing / Shelter</option><option value="Other">Other</option></select></div>
                <div class="flex flex-col gap-1"><label class="text-xs font-medium text-slate-400">Details</label><textarea name="description" required rows="4" class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-3 focus:border-green-500 focus:outline-none resize-none" placeholder="Please describe your situation..."></textarea></div>
                <div class="mt-4 flex gap-3"><button type="button" id="cancel-request-btn" class="flex-1 bg-[#283039] hover:bg-[#323c4a] text-white font-medium rounded-lg text-sm px-5 py-2.5 text-center">Cancel</button><button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg text-sm px-5 py-2.5 text-center shadow-lg">Submit Request</button></div>
            </form>
        </div>
    </div>

    <div id="success-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 hidden backdrop-blur-sm p-4">
        <div class="bg-[#1a222c] w-full max-w-sm p-6 rounded-xl border border-green-500/30 shadow-2xl text-center">
            <div class="mx-auto flex items-center justify-center size-14 rounded-full bg-green-500/20 mb-4"><span class="material-symbols-outlined text-green-500 !text-3xl">check_circle</span></div>
            <h3 class="text-xl font-bold text-white mb-2">Success!</h3><p class="text-slate-300 text-sm mb-6">Action completed successfully.</p>
            <button id="close-success-btn" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg text-sm px-5 py-2.5">Okay, Great!</button>
        </div>
    </div>

   <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script src="assets/js/my_dashboard.js?v=2"></script>
</body>
</html>