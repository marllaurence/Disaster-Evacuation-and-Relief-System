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

// --- "No Loophole" Fix: Get the Item ID from the URL ---
$item_id = $_GET['item_id'] ?? 0; // Get the ID, default to 0
if ($item_id == 0) {
    // If no item is specified, we can't distribute.
    // Send them back to the main relief page.
    header('Location: relief.php');
    exit;
}
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Distribute Aid - Disaster System</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
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
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10" href="evacuation.php">
                        <span class="material-symbols-outlined text-white">warehouse</span>
                        <p class="text-white text-sm font-medium leading-normal">Evacuation</p>
                    </a>
                    <a class="flex items-center gap-3 rounded-lg bg-primary/20 px-3 py-2" href="relief.php">
                        <span class="material-symbols-outlined text-primary">volunteer_activism</span>
                        <p class="text-primary text-sm font-medium leading-normal">Relief</p>
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
            
            <a href="relief.php" class="flex items-center gap-2 text-[#9dabb9] hover:text-primary w-fit">
                <span class="material-symbols-outlined">arrow_back</span>
                Back to Inventory
            </a>

            <input type="hidden" id="item_id" value="<?php echo htmlspecialchars($item_id); ?>">

            <div class="flex flex-wrap items-baseline gap-4">
                <h1 id="item-name-title" class="text-2xl font-bold leading-tight text-white">Distributing: Loading...</h1>
                <div class="text-lg text-[#9dabb9]">
                    (Stock: <span id="item-stock-count" class="font-bold text-white">0</span> 
                    <span id="item-stock-unit">...</span>)
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
                <div class="lg:col-span-1">
                    <div class="flex flex-col rounded-xl bg-[#1c2127] p-4">
                        <h2 class="text-lg font-semibold text-white mb-4">Log a New Distribution</h2>
                        
                        <form id="search-household-form" class="flex flex-col gap-4">
                            <div>
                                <label for="search_name" class="block text-sm font-medium text-[#9dabb9] mb-1">1. Search for Household:</label>
                                <input type="text" id="search_name" name="search_name" 
                                       class="w-full h-11 px-4 rounded-lg border-none bg-gray-100 text-gray-900 dark:bg-[#283039] dark:text-white placeholder:text-[#9dabb9] focus:ring-2 focus:ring-primary" 
                                       placeholder="Type household head's name..." required>
                            </div>
                            <button type="submit" class="flex min-w-[84px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-11 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary/80">
                                <span class="material-symbols-outlined">search</span>
                                <span class="truncate">Search</span>
                            </button>
                        </form>
                        
                        <div id="search-results-message" class="mt-4 text-sm"></div>
                        <div id="search-results-container" class="mt-2 flex flex-col gap-2">
                            </div>

                        <div class="mt-6 border-t border-[#3b4754] pt-4">
                            <div class="flex justify-between items-center">
                                <label class="block text-sm font-medium text-[#9dabb9] mb-2">2. Selected Households:</label>
                                <button type="button" id="clear-selection-btn" class="text-red-400 text-sm font-bold hover:underline">Clear All</button>
                            </div>
                            
                            <div id="selected-households-list" class="flex flex-col gap-2 mt-2">
                                </div>
                            
                            <p id="no-household-selected" class="text-sm text-[#9dabb9]">No households selected.</p>

                            <div class="mt-4">
                                <label for="quantity" class="block text-sm font-medium text-[#9dabb9] mb-1">3. Quantity <span class="font-normal italic">per household</span>:</label>
                                <input type="number" id="quantity" name="quantity" min="1" value="1"
                                       class="w-full h-11 px-4 rounded-lg border-none bg-gray-100 text-gray-900 dark:bg-[#283039] dark:text-white placeholder:text-[#9dabb9] focus:ring-2 focus:ring-primary" required>
                            </div>

                            <button type="button" id="confirm-distribution-btn" class="mt-6 w-full flex min-w-[84px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-11 px-4 bg-green-600 text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-green-700 disabled:opacity-50" disabled>
                                <span class="material-symbols-outlined">check_circle</span>
                                <span class="truncate">Confirm Distribution</span>
                            </button>
                            <div id="form-message" class="mt-4 text-sm"></div>
                        </div>
                        </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="flex flex-col rounded-xl bg-[#1c2127]">
                        <h2 class="text-lg font-semibold text-white p-4">Recent Distribution Log (This Item)</h2>
                        
                        <div class="overflow-x-auto">
                            <div class="inline-block min-w-full align-middle">
                                <div class="overflow-hidden rounded-b-xl border-t border-[#3b4754]">
                                    <table id="log-table" class="w-full min-w-full">
                                        <thead class="bg-[#283039]">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">Household</th>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">Qty</th>
                                                <th class="px-4 py-3 text-left text-white text-sm font-medium leading-normal">Time</th>
                                            </tr>
                                        </thead>
                                        <tbody id="log-table-body">
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/distribute_aid.js"></script>

</body>
</html>