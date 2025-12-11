<?php
// my_dashboard.php
// --------------------------------------------------------
// Main Resident Dashboard
// --------------------------------------------------------

include_once 'api/config/session.php';

// Security: Check if user is logged in AND is a resident
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header('Location: login.php');
    exit;
}

// User Info
$user_full_name = htmlspecialchars($_SESSION['full_name']);
$user_role = htmlspecialchars($_SESSION['role']);

// Profile Picture Logic
$profile_pic_url = $_SESSION['profile_picture_url'] ?? null;
$profile_pic_path = 'assets/img/default-avatar.png';

if (!empty($profile_pic_url) && file_exists($profile_pic_url)) {
    $profile_pic_path = $profile_pic_url;
}
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>My Dashboard - Disaster System</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=container-queries"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#137fec", "background-light": "#f6f7f8", "background-dark": "#101922" },
                    fontFamily: { "display": ["Public Sans"] },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 24px; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #1a222c; }
        ::-webkit-scrollbar-thumb { background: #314d68; border-radius: 4px; }
        html, body { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-white">
    
    <div class="relative flex w-full">

        <aside class="sticky top-0 flex h-screen w-64 flex-col bg-[#111418] p-4 shrink-0 border-r border-[#223649] overflow-y-auto">
            <div class="flex flex-col gap-4">
                
                <div class="flex items-center gap-3">
                    <button id="upload-pic-btn" class="relative group cursor-pointer" title="Change Profile Picture">
                        <img src="<?php echo $profile_pic_path; ?>" class="size-10 rounded-full bg-cover bg-center object-cover border border-slate-600">
                        <div class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="material-symbols-outlined text-white !text-lg">upload</span>
                        </div>
                    </button>
                    <div class="flex flex-col">
                        <h1 class="text-white text-base font-medium leading-normal truncate w-40"><?php echo $user_full_name; ?></h1>
                        <p class="text-[#9dabb9] text-sm capitalize"><?php echo $user_role; ?></p>
                    </div>
                </div>
                
                <form id="pic-upload-form" class="hidden">
                    <input type="file" id="profile-pic-input" name="profile_pic" accept="image/png, image/jpeg">
                </form>

                <div class="flex flex-col gap-2 mt-4">
                    <a href="my_dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/20 text-primary">
                        <span class="material-symbols-outlined">dashboard</span>
                        <p class="text-sm font-medium">My Dashboard</p>
                    </a>
                    
                    <a href="my_household.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
                        <span class="material-symbols-outlined">home</span>
                        <p class="text-sm font-medium">My Household</p>
                    </a>
                    
                    <a href="my_aid_history.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
                        <span class="material-symbols-outlined">receipt_long</span>
                        <p class="text-sm font-medium">Aid History</p>
                    </a>
                    <a href="my_requests.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
                        <span class="material-symbols-outlined">campaign</span>
                        <p class="text-sm font-medium">My Requests</p>
                    </a>
                </div>
            </div>

            <div class="flex flex-col gap-4 mt-auto pt-4">
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

        <main class="flex-1 bg-background-dark h-screen overflow-y-auto">
            <div class="p-6 lg:p-8">
                
                <div class="flex flex-wrap justify-between gap-3 mb-6">
                    <h1 class="text-white text-4xl font-black leading-tight tracking-[-0.033em]">
                        Welcome, <?php echo htmlspecialchars(explode(' ', $user_full_name)[0]); ?>!
                    </h1>
                </div>

                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <div class="rounded-xl bg-[#1a222c] p-6 border border-[#283039] md:col-span-2 shadow-lg">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h2 class="text-xl font-bold text-white">My Household</h2>
                                <p id="household-address" class="text-slate-300 mt-1 text-sm">Loading...</p>
                            </div>
                            <button id="open-add-member-modal" class="bg-primary/10 hover:bg-primary/20 text-primary text-sm font-bold py-2 px-3 rounded-lg flex items-center gap-2 transition-colors">
                                <span class="material-symbols-outlined !text-[18px]">person_add</span>
                                Add Member
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto rounded-lg border border-[#314d68]/30">
                            <table class="w-full min-w-full text-left">
                                <thead class="bg-[#223649] text-white">
                                    <tr>
                                        <th class="px-4 py-3 text-sm font-medium">Name</th>
                                        <th class="px-4 py-3 text-sm font-medium">Birthdate</th>
                                        <th class="px-4 py-3 text-sm font-medium">Gender</th>
                                        <th class="px-4 py-3 text-sm font-medium text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="my-household-list" class="divide-y divide-[#314d68]/30 bg-[#1a222c]">
                                    <tr><td colspan="4" class="px-4 py-4 text-center text-slate-400">Loading members...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="rounded-xl bg-[#1a222c] p-6 border border-[#283039] shadow-lg">
                        <h2 class="text-xl font-bold text-white mb-4">Aid Received</h2>
                        <ul id="my-aid-history-list" class="flex flex-col gap-3 max-h-64 overflow-y-auto pr-2">
                            <li class="px-2 py-4 text-center text-slate-400">Loading history...</li>
                        </ul>
                    </div>

                    <div class="rounded-xl bg-[#1a222c] p-6 border border-[#283039]">
                        <h2 class="text-xl font-bold text-white">Evacuation History</h2>
                        <p class="text-slate-300 mt-2 text-sm">Check your family's evacuation check-in and check-out records.</p>
                        <button id="view-evac-history-btn" class="mt-4 text-sm font-bold text-primary hover:underline">View Records</button>
                    </div>

                    <div class="rounded-xl bg-primary/20 p-6 border border-primary">
                        <h2 class="text-xl font-bold text-white">Request Assistance</h2>
                        <p class="text-slate-300 mt-2 text-sm">Submit a new request for food, medical, or other assistance.</p>
                        <button id="main-request-btn" class="mt-4 text-sm font-bold text-primary hover:underline">Submit Request</button>
                    </div>
                    
                    <div class="rounded-xl bg-[#1a222c] p-6 border border-[#283039]">
                        <h2 class="text-xl font-bold text-white">Evacuation Centers</h2>
                        <p class="text-slate-300 mt-2 text-sm">Find the nearest open evacuation center.</p>
                        <button id="view-map-btn" class="mt-4 text-sm font-bold text-primary hover:underline">View Map</button>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <div id="add-member-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 hidden backdrop-blur-sm p-4">
        <div class="bg-[#1a222c] w-full max-w-md p-6 rounded-xl border border-[#283039] shadow-2xl transform transition-all scale-100 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-5 border-b border-[#283039] pb-3">
                <h3 class="text-xl font-bold text-white">Add Family Member</h3>
                <button id="close-modal-btn" type="button" class="text-slate-400 hover:text-white transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="add-member-form" class="flex flex-col gap-4">
                <input type="hidden" name="member_id" id="member_id">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1"><label class="text-xs font-medium text-slate-400">First Name</label><input type="text" name="first_name" required class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-2.5 focus:border-primary focus:outline-none"></div>
                    <div class="flex flex-col gap-1"><label class="text-xs font-medium text-slate-400">Last Name</label><input type="text" name="last_name" required class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-2.5 focus:border-primary focus:outline-none"></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1"><label class="text-xs font-medium text-slate-400">Birthdate</label><input type="date" name="birthdate" class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-2.5 focus:border-primary focus:outline-none"></div>
                    <div class="flex flex-col gap-1"><label class="text-xs font-medium text-slate-400">Gender</label><select name="gender" class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-2.5 focus:border-primary focus:outline-none"><option value="Male">Male</option><option value="Female">Female</option><option value="Other">Other</option></select></div>
                </div>
                <div class="flex flex-col gap-1"><label class="text-xs font-medium text-slate-400">Remarks</label><textarea name="remarks" rows="2" class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-2.5 focus:border-primary focus:outline-none resize-none"></textarea></div>
                <div class="flex gap-4 mt-1 p-2 bg-[#111418] rounded-lg border border-[#314d68]">
                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_pwd" value="1" class="w-4 h-4 rounded border-gray-600 bg-[#1a222c] text-primary focus:ring-primary"><span class="text-sm text-white select-none">PWD</span></label>
                    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_senior" value="1" class="w-4 h-4 rounded border-gray-600 bg-[#1a222c] text-primary focus:ring-primary"><span class="text-sm text-white select-none">Senior</span></label>
                </div>
                <div class="mt-4 flex gap-3"><button type="button" id="cancel-modal-btn" class="flex-1 bg-[#283039] hover:bg-[#323c4a] text-white font-medium rounded-lg text-sm px-5 py-2.5 text-center">Cancel</button><button type="submit" class="flex-1 bg-primary hover:bg-blue-600 text-white font-medium rounded-lg text-sm px-5 py-2.5 text-center shadow-lg">Save Member</button></div>
            </form>
        </div>
    </div>

    <div id="evacuation-history-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 hidden backdrop-blur-sm p-4">
        <div class="bg-[#1a222c] w-full max-w-2xl p-6 rounded-xl border border-[#283039] shadow-2xl transform transition-all scale-100 max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center mb-5 border-b border-[#283039] pb-3 shrink-0">
                <h3 class="text-xl font-bold text-white">Evacuation History</h3>
                <button id="close-evac-modal-btn" class="text-slate-400 hover:text-white transition-colors"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="overflow-y-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#223649] text-white sticky top-0"><tr><th class="px-4 py-3 text-sm font-medium">Family Member</th><th class="px-4 py-3 text-sm font-medium">Center</th><th class="px-4 py-3 text-sm font-medium">Check In</th><th class="px-4 py-3 text-sm font-medium">Status</th></tr></thead>
                    <tbody id="evacuation-history-table" class="divide-y divide-[#314d68]/30 text-sm text-slate-300"><tr><td colspan="4" class="px-4 py-4 text-center">Loading records...</td></tr></tbody>
                </table>
            </div>
            <div class="mt-5 pt-3 border-t border-[#283039] text-right shrink-0"><button id="close-evac-btn-bottom" class="bg-[#283039] hover:bg-[#323c4a] text-white font-medium rounded-lg text-sm px-5 py-2.5">Close</button></div>
        </div>
    </div>

    <div id="request-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 hidden backdrop-blur-sm p-4">
        <div class="bg-[#1a222c] w-full max-w-md p-6 rounded-xl border border-green-600/30 shadow-2xl transform transition-all scale-100">
            <div class="flex justify-between items-center mb-5 border-b border-[#283039] pb-3">
                <div class="flex items-center gap-3"><div class="bg-green-600/20 p-2 rounded-lg"><span class="material-symbols-outlined text-green-500">emergency_share</span></div><h3 class="text-xl font-bold text-white">Request Help</h3></div>
                <button id="close-request-btn" class="text-slate-400 hover:text-white transition-colors"><span class="material-symbols-outlined">close</span></button>
            </div>
            
            <form id="request-form" class="flex flex-col gap-4" enctype="multipart/form-data">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-400">Type of Assistance</label>
                    <select name="request_type" required class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-3 focus:border-green-500 focus:outline-none">
                        <option value="" disabled selected>Select Request Type</option>
                        <option value="Food & Water">Food & Water</option>
                        <option value="Medical Assistance">Medical Assistance</option>
                        <option value="Rescue / Evacuation">Rescue / Evacuation</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-400">Details</label>
                    <textarea name="description" required rows="3" class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-3 focus:border-green-500 focus:outline-none resize-none" placeholder="Describe situation..."></textarea>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-400">Attach Photo (Optional)</label>
                    <input type="file" name="request_photo" accept="image/*" class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-2 focus:border-green-500 file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-green-600 file:text-white hover:file:bg-green-700">
                </div>

                <div class="mt-4 flex gap-3">
                    <button type="button" id="cancel-request-btn" class="flex-1 bg-[#283039] text-white rounded-lg py-2.5">Cancel</button>
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg py-2.5 shadow-lg">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <div id="success-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 hidden backdrop-blur-sm p-4">
        <div class="bg-[#1a222c] w-full max-w-sm p-6 rounded-xl border border-green-500/30 shadow-2xl text-center">
            <div class="mx-auto flex items-center justify-center size-14 rounded-full bg-green-500/20 mb-4"><span class="material-symbols-outlined text-green-500 !text-3xl">check_circle</span></div>
            <h3 class="text-xl font-bold text-white mb-2">Success!</h3><p class="text-slate-300 text-sm mb-6">Action completed successfully.</p>
            <button id="close-success-btn" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg text-sm px-5 py-2.5 transition-colors">Okay, Great!</button>
        </div>
    </div>

    <div id="evac-map-modal" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/90 hidden backdrop-blur-sm p-4">
        <div class="bg-[#1c2127] w-full max-w-4xl h-[80vh] rounded-xl border border-[#283039] shadow-2xl flex flex-col">
            <div class="flex justify-between items-center p-4 border-b border-[#283039] bg-[#222831] shrink-0">
                <div class="flex flex-col">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-orange-500">location_on</span> 
                        Evacuation Centers
                    </h3>
                    <p class="text-xs text-slate-400">Green markers indicate active centers.</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <button id="locate-me-btn" class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition-colors shadow-lg">
                        <span class="material-symbols-outlined !text-[16px]">my_location</span> Locate Me
                    </button>
                    <button id="close-map-btn" class="text-slate-400 hover:text-white transition-colors p-1 rounded hover:bg-white/10">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            </div>
            <div id="user-evac-map" class="flex-1 bg-[#111418] w-full h-full"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/my_dashboard.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</body>
</html>