<?php
// my_requests.php
// --------------------------------------------------------
// Resident Requests Page (Matches Dashboard Design)
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
    <title>My Requests - Disaster System</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=container-queries"></script>
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
                    <img src="<?php echo $profile_pic_path; ?>" class="size-10 rounded-full bg-cover bg-center object-cover border border-slate-600">
                    <div class="flex flex-col">
                        <h1 class="text-white text-base font-medium leading-normal truncate w-40"><?php echo $user_full_name; ?></h1>
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
                    
                    <a href="my_aid_history.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
                        <span class="material-symbols-outlined">receipt_long</span>
                        <p class="text-sm font-medium">Aid History</p>
                    </a>

                    <a href="my_requests.php" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/20 text-primary">
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
            <div class="p-6 lg:p-8 max-w-7xl mx-auto">
                
                <div class="flex flex-wrap justify-between items-end gap-3 mb-8 border-b border-[#283039] pb-6">
                    <div>
                        <h1 class="text-white text-3xl font-black tracking-tight">Request Inbox</h1>
                        <p class="text-slate-400 mt-2 text-sm">Track the status of your assistance requests in real-time.</p>
                    </div>
                    
                    <button id="top-request-btn" class="bg-primary hover:bg-blue-600 text-white text-sm font-bold py-2.5 px-4 rounded-lg flex items-center gap-2 transition-colors shadow-lg shadow-blue-900/20">
                        <span class="material-symbols-outlined !text-[20px]">add_circle</span>
                        New Request
                    </button>
                </div>

                <div id="requests-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    <div class="col-span-full text-center py-20">
                        <span class="material-symbols-outlined text-4xl text-slate-600 animate-spin">progress_activity</span>
                        <p class="text-slate-500 mt-2">Loading requests...</p>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <div id="request-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 hidden backdrop-blur-sm p-4">
        <div class="bg-[#1a222c] w-full max-w-md p-6 rounded-xl border border-green-600/30 shadow-2xl transform transition-all scale-100">
            <div class="flex justify-between items-center mb-5 border-b border-[#283039] pb-3">
                <div class="flex items-center gap-3">
                    <div class="bg-green-600/20 p-2 rounded-lg"><span class="material-symbols-outlined text-green-500">emergency_share</span></div>
                    <h3 class="text-xl font-bold text-white">Request Help</h3>
                </div>
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
                    <textarea name="description" required rows="4" class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-3 focus:border-green-500 focus:outline-none resize-none" placeholder="Describe your situation..."></textarea>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-400">Attach Photo (Optional)</label>
                    <input type="file" name="request_photo" accept="image/*" class="bg-[#111418] border border-[#314d68] text-white text-sm rounded-lg p-2 focus:border-green-500 file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-green-600 file:text-white hover:file:bg-green-700 cursor-pointer">
                    <p class="text-[10px] text-slate-500 mt-1">Supported: JPG, PNG. Max size: 40MB</p>
                </div>

                <div class="mt-4 flex gap-3">
                    <button type="button" id="cancel-request-btn" class="flex-1 bg-[#283039] hover:bg-[#323c4a] text-white font-medium rounded-lg text-sm px-5 py-2.5">Cancel</button>
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg text-sm px-5 py-2.5 shadow-lg shadow-green-900/20">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <div id="success-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 hidden backdrop-blur-sm p-4">
        <div class="bg-[#1a222c] w-full max-w-sm p-6 rounded-xl border border-green-500/30 shadow-2xl text-center">
            <div class="mx-auto flex items-center justify-center size-14 rounded-full bg-green-500/20 mb-4">
                <span class="material-symbols-outlined text-green-500 !text-3xl">check_circle</span>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Success!</h3>
            <p class="text-slate-300 text-sm mb-6">Request submitted successfully.</p>
            <button id="close-success-btn" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg text-sm px-5 py-2.5 transition-colors">Okay, Great!</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/my_requests.js?v=<?php echo time(); ?>"></script>
    
    <script>
        // Simple inline script to handle the extra "Top Button" opening the modal
        $(document).ready(function() {
            $('#top-request-btn').on('click', function() {
                $('#request-modal').removeClass('hidden').addClass('flex');
            });
        });
    </script>
</body>
</html>