<?php
// Include the new session manager
include_once 'api/config/session.php';
// "No Loophole" Bouncer:
// Check if user is logged in AND is an admin.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // If not, redirect to the login page
    header('Location: login.php');
    exit; // Stop the script from running
}

// --- We also need the user's info for the sidebar ---
$user_full_name = htmlspecialchars($_SESSION['full_name']);
$user_role = htmlspecialchars($_SESSION['role']);

// --- THIS IS THE LINE YOU ARE MISSING ---
// New Code (Correct)
// We check for 'center_id' because that matches the link in evacuation.js
$center_id = htmlspecialchars($_GET['center_id'] ?? 0);
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Check-In - Disaster System</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=container-queries"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#137fec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Public Sans", "sans-serif"]
                    },
                },
            },
        }
    </script>
    
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            font-size: 24px;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-white">

    <div class="flex h-screen w-full">
    
            <aside class="flex w-64 flex-col bg-[#1c2127] p-4 text-white">
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
                         <a class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-white/10" href="admin_requests.php">
                        <span class="material-symbols-outlined group-hover:text-red-400 transition-colors">emergency_share</span><p class="text-sm font-medium">Requests</p>
                    </a>
                        <a class="flex items-center gap-3 rounded-lg bg-primary/20 px-3 py-2" href="evacuation.php">
                            <span class="material-symbols-outlined text-primary">warehouse</span>
                            <p class="text-primary text-sm font-medium leading-normal">Evacuation</p>
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

        <main class="flex flex-1 flex-col gap-6 p-6 overflow-y-auto">
            
            <a href="evacuation.php" class="flex items-center gap-2 text-[#9dabb9] hover:text-primary w-fit">
                <span class="material-symbols-outlined">arrow_back</span>
                Back to All Centers
            </a>

            <input type="hidden" id="center_id" value="<?php echo $center_id; ?>">

            <div class="flex flex-wrap items-baseline gap-4">
                <h1 id="center-name-title" class="text-2xl font-bold leading-tight text-white">Loading Center...</h1>
                <div id="center-occupancy-stats" class="text-lg text-[#9dabb9]">
                    (0 / 0 Occupied)
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
                <div class="lg:col-span-1">
                    <div class="flex flex-col rounded-xl bg-[#1c2127] p-4">
                        <h2 class="text-lg font-semibold text-white mb-4">Check-In a Resident</h2>
                        
                        <form id="search-resident-form" class="flex flex-col gap-4">
                            <div>
                                <label for="search_name" class="block text-sm font-medium text-[#9dabb9] mb-1">Search by Last Name:</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#9dabb9]">
                                        search
                                    </span>
                                    <input type="text" id="search_name" name="search_name" 
                                           class="w-full h-11 px-4 pl-10 rounded-lg border-none bg-gray-100 text-gray-900 dark:bg-[#283039] dark:text-white placeholder:text-[#9dabb9] focus:ring-2 focus:ring-primary" 
                                           placeholder="Type a last name..." required>
                                </div>
                            </div>
                            <button type="submit" class="flex min-w-[84px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-11 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary/80">
                                <span class="material-symbols-outlined">search</span>
                                <span class="truncate">Search</span>
                            </button>
                        </form>
                        
                        <div id="search-results-message" class="mt-4 text-sm"></div>
                        <div id="search-results-container" class_="mt-4 flex flex-col gap-2">
                            </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="flex flex-col rounded-xl bg-[#1c2127]">
                        <h2 class="text-lg font-semibold text-white p-4">Currently at this Center</h2>
                        
                        <div class="overflow-x-auto">
                            <div class="inline-block min-w-full align-middle">
                                <div class="overflow-hidden rounded-b-xl border-t border-[#3b4754]">
                                    <table id="evacuees-table" class="w-full min-w-full">
                                        <thead class="bg-[#283039]">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">Name</th>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">Time In</th>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="evacuees-table-body">
                                            </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
            </div>
        </main>
    </div>
<div id="check-out-modal" class="fixed inset-0 z-50 items-center justify-center p-4 hidden bg-black/50">
        <div class="flex flex-col rounded-xl bg-[#1c2127] p-6 shadow-xl w-full max-w-md">
            <h2 class="text-xl font-bold text-white">Confirm Check-Out</h2>
            <p class="mt-2 text-sm text-[#9dabb9]">
                Are you sure you want to check out <span class="font-bold resident-name">this resident</span>? This action cannot be undone.
            </p>

            <div class="mt-6 flex justify-end gap-4">
                <button type="button" class="cancel-checkout-btn flex-1 rounded-lg bg-[#283039] px-4 py-2.5 text-sm font-medium text-white hover:bg-[#3b4754]">
                    Cancel
                </button>
                
                <button type="button" id="confirm-checkout-btn" class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700">
                    Confirm Check-Out
                </button>
            </div>
        </div>
    </div>
    </body>
</html>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/check_in.js"></script>

</body>
</html>