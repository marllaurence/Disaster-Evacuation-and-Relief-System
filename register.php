<?php
// Include the new session manager
include_once 'api/config/session.php';

// If the user is ALREADY logged in, redirect them to the dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Disaster Response & Evacuation System - Register</title>
    <script src="https://cdn.tailwindcss.com?plugins=container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;700;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#0d7ff2",
                        "background-light": "#f5f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: { "display": ["Public Sans", "sans-serif"] },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 24px; }
        #register-map { height: 250px; width: 100%; border-radius: 0.5rem; z-index: 1; border: 1px solid #314d68; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display">
    <div class="flex h-screen w-full overflow-hidden">
            <aside class="hidden w-[360px] flex-col justify-between bg-[#101a23] p-8 border-r border-solid border-[#223649] lg:flex">
                <div class="flex flex-col gap-10">
                    <div class="flex items-center gap-3 text-white">
                        <div class="size-6 text-primary">
                            <svg fill="none" viewbox="0 0 48 48" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" d="M24 4H42V17.3333V30.6667H24V44H6V30.6667V17.3333H24V4Z" fill="currentColor" fill-rule="evenodd"></path></svg>
                        </div>
                        <h2 class="text-white text-lg font-bold leading-tight tracking-[-0.015em]">Disaster Response</h2>
                    </div>
                    <div class="flex flex-col gap-4">
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 bg-center bg-no-repeat aspect-square bg-cover rounded-full size-12" style='background-image: url("assets/img/login-bg.jpg");'></div>
                            <div class="flex flex-col">
                                <h1 class="text-white text-base font-medium leading-normal">Your safety is our priority</h1>
                                <p class="text-[#90adcb] text-sm font-normal leading-normal">This system helps us assist you effectively during an emergency.</p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 mt-6">
                            <div class="flex items-center gap-3 px-3 py-2 rounded-lg bg-[#223649]">
                                <span class="material-symbols-outlined text-white" style="font-variation-settings: 'FILL' 1;">edit</span>
                                <p class="text-white text-sm font-medium leading-normal">Create Profile</p>
                            </div>
                            <div class="flex items-center gap-3 px-3 py-2">
                                <span class="material-symbols-outlined text-white">list</span>
                                <p class="text-white text-sm font-medium leading-normal">Key Features</p>
                            </div>
                            <div class="flex items-center gap-3 px-3 py-2">
                                <span class="material-symbols-outlined text-white">phone</span>
                                <p class="text-white text-sm font-medium leading-normal">Emergency Contacts</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-3 px-3 py-2">
                        <span class="material-symbols-outlined text-white">help</span>
                        <p class="text-white text-sm font-medium leading-normal">Help/FAQ</p>
                    </div>
                </div>
            </aside>
            <main class="flex-1 overflow-y-auto">
                <header class="flex items-center justify-end whitespace-nowrap border-b border-solid border-b-[#223649] px-10 py-5">
                    <a href="login.php" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em]">
                        <span class="truncate">Login</span>
                    </a>
                </header>
                <div class="p-10">
                    <div class="max-w-4xl mx-auto">
                        <div class="flex flex-wrap justify-between gap-3 mb-8">
                            <div class="flex min-w-72 flex-col gap-3">
                                <p class="text-white text-4xl font-black leading-tight tracking-[-0.033em]">Create Your Emergency Profile</p>
                                <p class="text-[#90adcb] text-base font-normal leading-normal">Please fill in your details to register for assistance.</p>
                            </div>
                        </div>
                        <div class="bg-[#182634] p-8 rounded-xl border border-solid border-[#223649]">
                            <form id="register-form" class="flex flex-col gap-8">
                                <div>
                                    <h2 class="text-white text-[22px] font-bold leading-tight tracking-[-0.015em] pb-3 pt-5 mb-2">Personal Information</h2>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <label class="flex flex-col">
                                            <p class="text-white text-base font-medium leading-normal pb-2">First Name</p>
                                            <input id="first_name" name="first_name" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-white focus:outline-0 focus:ring-0 border border-[#314d68] bg-[#101a23] focus:border-primary h-14 placeholder:text-[#90adcb] p-[15px] text-base font-normal leading-normal" placeholder="Enter your first name" required />
                                        </label>
                                        <label class="flex flex-col">
                                            <p class="text-white text-base font-medium leading-normal pb-2">Last Name</p>
                                            <input id="last_name" name="last_name" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-white focus:outline-0 focus:ring-0 border border-[#314d68] bg-[#101a23] focus:border-primary h-14 placeholder:text-[#90adcb] p-[15px] text-base font-normal leading-normal" placeholder="Enter your last name" required />
                                        </label>
                                        
                                        <label class="flex flex-col">
                                            <p class="text-white text-base font-medium leading-normal pb-2">Birthdate</p>
                                            <input type="date" name="birthdate" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-white focus:outline-0 focus:ring-0 border border-[#314d68] bg-[#101a23] focus:border-primary h-14 placeholder:text-[#90adcb] p-[15px] text-base font-normal leading-normal" required />
                                        </label>
                                        <label class="flex flex-col">
                                            <p class="text-white text-base font-medium leading-normal pb-2">Gender</p>
                                            <select name="gender" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-white focus:outline-0 focus:ring-0 border border-[#314d68] bg-[#101a23] focus:border-primary h-14 placeholder:text-[#90adcb] p-[15px] text-base font-normal leading-normal">
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <h2 class="text-white text-[22px] font-bold leading-tight tracking-[-0.015em] pb-3 pt-5 mb-2">Location & Address</h2>
                                    
                                    <div class="flex flex-col gap-2 mb-4">
                                        <p class="text-[#90adcb] text-sm">Pin your house on the map (Mati City)</p>
                                        <div id="register-map"></div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                                        <label class="flex flex-col">
                                            <p class="text-white text-base font-medium leading-normal pb-2">Latitude</p>
                                            <input id="latitude" name="latitude" readonly class="bg-[#101a23] border border-[#314d68] text-gray-400 rounded-lg h-14 px-4 cursor-not-allowed" />
                                        </label>
                                        <label class="flex flex-col">
                                            <p class="text-white text-base font-medium leading-normal pb-2">Longitude</p>
                                            <input id="longitude" name="longitude" readonly class="bg-[#101a23] border border-[#314d68] text-gray-400 rounded-lg h-14 px-4 cursor-not-allowed" />
                                        </label>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <label class="flex flex-col">
                                            <p class="text-white text-base font-medium leading-normal pb-2">Zone / Purok</p>
                                            <input name="zone_purok" class="form-input bg-[#101a23] border border-[#314d68] text-white rounded-lg h-14 px-4 focus:border-primary outline-none" placeholder="e.g. Zone 1, Purok Baybay" required />
                                        </label>
                                        
                                        <label class="flex flex-col">
                                            <p class="text-white text-base font-medium leading-normal pb-2">Complete Address / Notes</p>
                                            <input id="address" name="address" class="form-input bg-[#101a23] border border-[#314d68] text-white rounded-lg h-14 px-4 focus:border-primary outline-none" placeholder="House No., Street Name" required />
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <h2 class="text-white text-[22px] font-bold leading-tight tracking-[-0.015em] pb-3 pt-5 mb-2">Account Credentials</h2>
                                    <div class="grid grid-cols-1 gap-6">
                                        <label class="flex flex-col">
                                            <p class="text-white text-base font-medium leading-normal pb-2">Email Address</p>
                                            <input id="email" name="email" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-white focus:outline-0 focus:ring-0 border border-[#314d68] bg-[#101a23] focus:border-primary h-14 placeholder:text-[#90adcb] p-[15px] text-base font-normal leading-normal" placeholder="you@example.com" type="email" required />
                                        </label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <label class="flex flex-col">
                                                <p class="text-white text-base font-medium leading-normal pb-2">Create Password</p>
                                                <input id="password" name="password" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-white focus:outline-0 focus:ring-0 border border-[#314d68] bg-[#101a23] focus:border-primary h-14 placeholder:text-[#90adcb] p-[15px] text-base font-normal leading-normal" placeholder="Enter a secure password" type="password" required />
                                            </label>
                                            <label class="flex flex-col">
                                                <p class="text-white text-base font-medium leading-normal pb-2">Confirm Password</p>
                                                <input id="password_confirm" name="password_confirm" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-white focus:outline-0 focus:ring-0 border border-[#314d68] bg-[#101a23] focus:border-primary h-14 placeholder:text-[#90adcb] p-[15px] text-base font-normal leading-normal" placeholder="Confirm your password" type="password" required />
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div id="register-message" class="hidden flex items-center gap-3 rounded-lg border p-3 mb-6">
                                        <span id="register-message-icon" class="material-symbols-outlined"></span>
                                        <p id="register-message-text" class="text-sm font-medium"></p>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <input id="terms_agreement" name="terms_agreement" class="form-checkbox h-5 w-5 rounded border-[#314d68] bg-[#101a23] text-primary focus:ring-primary focus:ring-offset-background-dark" type="checkbox" required />
                                        <label class="text-[#90adcb] text-sm" for="terms_agreement">I agree to the <a class="text-primary hover:underline" href="#">Terms of Service</a> and <a class="text-primary hover:underline" href="#">Privacy Policy</a>.</label>
                                    </div>
                                    <button id="submit-button" class="flex w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg h-14 px-4 bg-primary text-white text-lg font-bold leading-normal tracking-[0.015em] mt-8" type="submit">
                                        <span class="truncate">Create Account</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div> 
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="assets/js/register.js"></script>

    <script>
        $(document).ready(function() {
            const matiLat = 6.9567;
            const matiLng = 126.2174;

            var map = L.map('register-map', {
                center: [matiLat, matiLng],
                zoom: 13,
                minZoom: 11,
                maxBounds: [[6.80, 126.00], [7.15, 126.50]],
                maxBoundsViscosity: 1.0
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19, attribution: '© OpenStreetMap'
            }).addTo(map);

            var marker;

            map.on('click', function(e) {
                if (marker) marker.setLatLng(e.latlng);
                else marker = L.marker(e.latlng).addTo(map);
                
                $('#latitude').val(e.latlng.lat.toFixed(8));
                $('#longitude').val(e.latlng.lng.toFixed(8));
            });
        });
    </script>
</body>
</html>