<?php
// my_settings.php
include_once 'api/config/session.php';
include_once 'api/config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header('Location: login.php');
    exit;
}

$user_full_name = htmlspecialchars($_SESSION['full_name']);
$user_role = htmlspecialchars($_SESSION['role']);
$profile_pic = $_SESSION['profile_picture_url'] ?? 'assets/img/default-avatar.png';
if (!file_exists($profile_pic)) $profile_pic = 'assets/img/default-avatar.png';

// Check for resident ID session safety
if (!isset($_SESSION['resident_id'])) {
    $uid = $_SESSION['user_id'];
    $q = $conn->query("SELECT id FROM residents WHERE user_id = $uid");
    if ($r = $q->fetch_assoc()) $_SESSION['resident_id'] = $r['id'];
}
$resident_id = $_SESSION['resident_id'];

// Join Residents, Users, AND Households to get all info
$sql = "SELECT r.first_name, r.last_name, u.username as email,
               h.address_notes, h.zone_purok, h.latitude, h.longitude
        FROM residents r 
        JOIN users u ON u.resident_id = r.id 
        LEFT JOIN households h ON r.household_id = h.id
        WHERE r.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Settings - Disaster System</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <script>
        tailwind.config = { darkMode: "class", theme: { extend: { colors: { "primary": "#137fec", "background-dark": "#101922" }, fontFamily: { "display": ["Public Sans"] } } } }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        #settings-map { height: 250px; width: 100%; border-radius: 0.5rem; z-index: 1; border: 1px solid #3b4754; }
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
                    <img src="<?php echo $profile_pic; ?>" id="sidebar-profile-img" class="size-10 rounded-full bg-cover object-cover border border-slate-600">
                    <div class="flex flex-col"><h1 class="text-base font-medium truncate w-40"><?php echo $user_full_name; ?></h1><p class="text-[#9dabb9] text-sm capitalize"><?php echo $user_role; ?></p></div>
                </div>
                <div class="flex flex-col gap-2 mt-4">
                    <a href="my_dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
                        <span class="material-symbols-outlined">dashboard</span><p class="text-sm font-medium">My Dashboard</p>
                    </a>
                    <a href="my_household.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
                        <span class="material-symbols-outlined">home</span><p class="text-sm font-medium">My Household</p>
                    </a>
                    <a href="my_aid_history.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
                        <span class="material-symbols-outlined">receipt_long</span><p class="text-sm font-medium">Aid History</p>
                    </a>
                    <a href="my_requests.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
    <span class="material-symbols-outlined">campaign</span>
    <p class="text-sm font-medium">My Requests</p>
</a>
                </div>
            </div>
            <div class="flex flex-col gap-4 mt-auto">
                <button id="sidebar-request-btn" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-10 px-4 bg-green-600 text-white text-sm font-bold hover:bg-green-700 transition-colors w-full">
                    <span class="material-symbols-outlined !text-[20px]">help</span><span class="truncate">Request Assistance</span>
                </button>
                <div class="flex flex-col gap-1">
                    <a href="my_settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/20 text-primary font-bold">
                        <span class="material-symbols-outlined">settings</span><p class="text-sm font-medium">Settings</p>
                    </a>
                    <a href="api/auth/logout_process.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#9dabb9] hover:bg-[#283039] hover:text-white transition-colors">
                        <span class="material-symbols-outlined">logout</span><p class="text-sm font-medium">Logout</p>
                    </a>
                </div>
            </div>
        </aside>

        <main class="flex-1 h-screen overflow-y-auto bg-background-dark p-8">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-3xl font-bold mb-2">Account Settings</h1>
                <p class="text-slate-400 mb-8">Update your profile, address, and security preferences.</p>

                <div class="bg-[#1a222c] rounded-xl border border-[#283039] p-8 shadow-lg">
                    
                    <div class="flex items-center gap-6 mb-8 pb-8 border-b border-[#283039]">
                        <div class="relative group cursor-pointer" id="settings-pic-container">
                            <img src="<?php echo $profile_pic; ?>" id="settings-profile-img" class="w-24 h-24 rounded-full bg-cover object-cover border-4 border-[#283039] shadow-xl">
                            <div class="absolute inset-0 bg-black/50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="material-symbols-outlined text-white">camera_alt</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Profile Photo</h3>
                            <p class="text-sm text-slate-400 mb-2">Click the image to upload a new photo.</p>
                            <p class="text-xs text-slate-500">Max size 5MB (JPG, PNG)</p>
                        </div>
                        <input type="file" id="settings-file-input" class="hidden" accept="image/png, image/jpeg">
                    </div>

                    <form id="update-profile-form" class="flex flex-col gap-8">
                        <div>
                            <h3 class="text-lg font-bold text-white mb-4 border-b border-[#283039] pb-2">Personal Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-bold text-[#9dabb9]">First Name</label>
                                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?>" required class="bg-[#111418] border border-[#3b4754] text-white text-sm rounded-lg p-3 focus:border-primary focus:outline-none">
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-bold text-[#9dabb9]">Last Name</label>
                                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?>" required class="bg-[#111418] border border-[#3b4754] text-white text-sm rounded-lg p-3 focus:border-primary focus:outline-none">
                                </div>
                                <div class="flex flex-col gap-1.5 md:col-span-2">
                                    <label class="text-xs font-bold text-[#9dabb9]">Email Address</label>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required class="bg-[#111418] border border-[#3b4754] text-white text-sm rounded-lg p-3 focus:border-primary focus:outline-none">
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-white mb-4 border-b border-[#283039] pb-2">Household Location</h3>
                            
                            <div class="flex flex-col gap-2 mb-4">
                                <label class="text-xs font-bold text-[#9dabb9]">Update Map Pin</label>
                                <div id="settings-map"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-bold text-[#9dabb9]">Latitude</label>
                                    <input type="text" id="latitude" name="latitude" value="<?php echo htmlspecialchars($user_data['latitude'] ?? ''); ?>" readonly class="bg-[#1c2127] border border-[#3b4754] text-gray-400 text-sm rounded-lg p-3 cursor-not-allowed">
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-bold text-[#9dabb9]">Longitude</label>
                                    <input type="text" id="longitude" name="longitude" value="<?php echo htmlspecialchars($user_data['longitude'] ?? ''); ?>" readonly class="bg-[#1c2127] border border-[#3b4754] text-gray-400 text-sm rounded-lg p-3 cursor-not-allowed">
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-bold text-[#9dabb9]">Zone / Purok</label>
                                    <input type="text" name="zone_purok" value="<?php echo htmlspecialchars($user_data['zone_purok'] ?? ''); ?>" class="bg-[#111418] border border-[#3b4754] text-white text-sm rounded-lg p-3 focus:border-primary focus:outline-none">
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-bold text-[#9dabb9]">Address Notes</label>
                                    <input type="text" name="address_notes" value="<?php echo htmlspecialchars($user_data['address_notes'] ?? ''); ?>" class="bg-[#111418] border border-[#3b4754] text-white text-sm rounded-lg p-3 focus:border-primary focus:outline-none">
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-white mb-4 border-b border-[#283039] pb-2">Change Password</h3>
                            <div class="flex flex-col gap-1.5 mb-2">
                                <label class="text-xs font-bold text-[#9dabb9]">New Password (Leave blank to keep current)</label>
                                <input type="password" name="password" placeholder="Enter new password" class="bg-[#111418] border border-[#3b4754] text-white text-sm rounded-lg p-3 focus:border-primary focus:outline-none">
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-[#9dabb9]">Confirm New Password</label>
                                <input type="password" name="password_confirm" placeholder="Confirm new password" class="bg-[#111418] border border-[#3b4754] text-white text-sm rounded-lg p-3 focus:border-primary focus:outline-none">
                            </div>
                        </div>

                        <div id="settings-message" class="text-sm text-center font-bold"></div>

                        <div class="flex justify-end pt-4 border-t border-[#283039]">
                            <button type="submit" id="save-btn" class="bg-primary hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg shadow-lg transition-all hover:scale-105">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="assets/js/settings.js"></script>
</body>
</html>