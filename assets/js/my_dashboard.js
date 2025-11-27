$(document).ready(function() {
    console.log("Dashboard JS Loaded - V108 (Saved Location Mode)");

    // --- GLOBAL VARIABLES ---
    var userSavedLat = null;
    var userSavedLng = null;
    var userMap;
    var userLocationMarker;


    // ==========================================
    // 3. EVACUATION HISTORY LOGIC (Add this if missing)
    // ==========================================
    
    // OPEN MODAL
    $(document).on('click', '#view-evac-history-btn', function(e) {
        e.preventDefault();
        console.log("Evacuation History Clicked"); // Debug check

        // Show Modal
        $('#evacuation-history-modal').removeClass('hidden');
        $('body').addClass('overflow-hidden'); // Prevent background scrolling
        
        // Set loading state
        var tbody = $('#evacuation-history-table');
        tbody.html('<tr><td colspan="4" class="text-center py-4 text-slate-400">Loading records...</td></tr>');
        
        // Fetch Data
        $.ajax({
            url: 'api/resident/get_evacuation_history.php',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                tbody.empty();
                
                if (res.success && res.history.length > 0) {
                    res.history.forEach(function(log) {
                        // Format Date
                        var checkIn = new Date(log.check_in_time).toLocaleString('en-US', {
                            month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit'
                        });
                        
                        // Status Badge
                        var statusBadge = (log.status === 'Checked Out' || log.check_out_time) 
                            ? '<span class="px-2 py-1 rounded bg-slate-700 text-slate-300 text-xs font-bold">Checked Out</span>' 
                            : '<span class="px-2 py-1 rounded bg-green-500/20 text-green-500 text-xs font-bold animate-pulse">Active</span>';

                        var row = `
                            <tr class="hover:bg-white/5 transition-colors border-b border-slate-700 last:border-0">
                                <td class="px-4 py-3 text-white text-sm font-medium">${log.first_name}</td>
                                <td class="px-4 py-3 text-slate-300 text-sm">${log.center_name}</td>
                                <td class="px-4 py-3 text-slate-300 text-sm">${checkIn}</td>
                                <td class="px-4 py-3">${statusBadge}</td>
                            </tr>`;
                        tbody.append(row);
                    });
                } else if (res.success && res.history.length === 0) {
                    tbody.html('<tr><td colspan="4" class="px-4 py-8 text-center text-slate-400 italic">No evacuation records found.</td></tr>');
                } else {
                    tbody.html('<tr><td colspan="4" class="px-4 py-4 text-center text-red-400">Error loading data.</td></tr>');
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                tbody.html('<tr><td colspan="4" class="px-4 py-4 text-center text-red-400">System Error. Check console.</td></tr>');
            }
        });
    });

    // CLOSE MODAL
    $(document).on('click', '#close-evac-modal-btn, #close-evac-btn-bottom', function(e) {
        e.preventDefault();
        $('#evacuation-history-modal').addClass('hidden');
        $('body').removeClass('overflow-hidden');
    });
    // ==========================================
    // 1. DATA LOADERS
    // ==========================================
    
    // LOAD HOUSEHOLD (And Save Coordinates)
    function loadMyHousehold() {
        $.ajax({
            url: 'api/resident/get_my_household.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var tableBody = $('#my-household-list');
                
                if (response.success) {
                    if (response.household) {
                        $('#household-address').text(response.household.address_notes);
                        
                        // --- SAVE COORDINATES FOR THE MAP ---
                        if (response.household.latitude && response.household.longitude) {
                            userSavedLat = parseFloat(response.household.latitude);
                            userSavedLng = parseFloat(response.household.longitude);
                        }
                    }
                    
                    tableBody.empty();
                    
                    if(response.members.length > 0) {
                        window.myMembers = response.members; // Save for editing
                        response.members.forEach(function(member, index) {
                            // (Your existing row generation code remains the same)
                            var row = `
                                <tr class="group hover:bg-white/5 transition-colors border-b border-[#283039] last:border-0">
                                    <td class="px-6 py-4 text-white text-sm font-medium">${member.first_name} ${member.last_name}</td>
                                    <td class="px-6 py-4 text-slate-300 text-sm">${member.birthdate || 'N/A'}</td>
                                    <td class="px-6 py-4 text-slate-300 text-sm capitalize">${member.gender || 'N/A'}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button class="edit-member-btn text-blue-400 hover:text-blue-300 transition-colors p-1" data-index="${index}">
                                                <span class="material-symbols-outlined text-[20px]">edit</span>
                                            </button>
                                            <button class="delete-member-btn text-red-400 hover:text-red-300 transition-colors p-1" data-id="${member.id}">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>`;
                            tableBody.append(row);
                        });
                    } else {
                        tableBody.append('<tr><td colspan="4" class="px-6 py-8 text-center text-slate-400 italic">No family members added yet.</td></tr>');
                    }
                }
            }
        });
    }

    // ==========================================
    // 2. EVACUATION MAP LOGIC (Updated "Locate Me")
    // ==========================================
    
    // Open Map Modal
    $('.view-map-btn, #view-map-btn').on('click', function(e) {
        e.preventDefault();
        $('#evac-map-modal').removeClass('hidden');
        $('body').addClass('overflow-hidden');
        setTimeout(function() { initUserMap(); }, 300);
    });

    // "LOCATE ME" BUTTON (Uses Saved Database Coordinates)
    $('#locate-me-btn').on('click', function() {
        var btn = $(this);
        
        if (userSavedLat && userSavedLng) {
            // We have coordinates!
            btn.html('<span class="material-symbols-outlined !text-[16px]">home_pin</span> My House');

            if (!userMap) initUserMap();

            // Remove old marker if exists
            if (userLocationMarker) userMap.removeLayer(userLocationMarker);

            // Add a "Home" Icon/Marker
            userLocationMarker = L.marker([userSavedLat, userSavedLng]).addTo(userMap)
                .bindPopup("<b>My House</b><br>Your registered location.")
                .openPopup();

            // Fly to location
            userMap.setView([userSavedLat, userSavedLng], 16);

        } else {
            // No coordinates in DB
            alert("No location recorded for your household. Please contact Admin to update your location.");
        }
    });

    function initUserMap() {
        const matiLat = 6.9567;
        const matiLng = 126.2174;

        if (!userMap) {
            userMap = L.map('user-evac-map', {
                center: [matiLat, matiLng],
                zoom: 13,
                minZoom: 11
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19, attribution: '© OpenStreetMap'
            }).addTo(userMap);
        } else {
            userMap.invalidateSize();
        }

        // Fetch Evacuation Centers
        $.ajax({
            url: 'api/resident/get_active_centers.php',
            type: 'GET', dataType: 'json',
            success: function(centers) {
                centers.forEach(function(c) {
                    var lat = parseFloat(c.latitude);
                    var lng = parseFloat(c.longitude);
                    if (lat && lng) {
                        // Green/Blue Markers for Centers
                        var popupContent = `<b>${c.center_name}</b><br>${c.address}<br>Occ: ${c.occupancy}/${c.capacity}`;
                        L.circleMarker([lat, lng], {
                            color: '#ffffff', fillColor: '#22c55e', fillOpacity: 1, radius: 8, weight: 2
                        }).addTo(userMap).bindPopup(popupContent);
                    }
                });
            }
        });
    }

    // ==========================================
    // 3. OTHER HANDLERS (Modals, Forms, etc - Same as before)
    // ==========================================

    // ... (Keep your existing code for Aid History, Add Member, Request Assistance, etc.) ...
    // I will include the Request Assistance code here just to be safe:

    $('#sidebar-request-btn, #main-request-btn').on('click', function(e) {
        e.preventDefault();
        $('#request-modal').removeClass('hidden');
        $('body').addClass('overflow-hidden');
    });

    $('#request-form').on('submit', function(e) {
        e.preventDefault();
        var btn = $(this).find('button[type="submit"]');
        btn.text('Sending...').prop('disabled', true);

        $.ajax({
            url: 'api/resident/submit_request.php', type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#request-modal').addClass('hidden'); $('#request-form')[0].reset(); $('body').removeClass('overflow-hidden');
                    $('#success-modal h3').text('Request Sent!'); $('#success-modal p').text('Help is on the way.'); $('#success-modal').removeClass('hidden');
                } else { alert(res.message); }
            },
            complete: function() { btn.text('Submit Request').prop('disabled', false); }
        });
    });
    
    // Close Modals
    $(document).on('click', '#close-modal-btn, #cancel-modal-btn, #close-evac-modal-btn, #close-evac-btn-bottom, #close-request-btn, #cancel-request-btn, #close-success-btn, #close-map-btn', function(e) {
        e.preventDefault();
        $('.fixed').addClass('hidden');
        $('body').removeClass('overflow-hidden');
    });

    // Initial Loads
    loadMyHousehold(); // This now saves the coordinates!
    loadMyAidHistory(); // Function assumed to be in your file from previous step
});

// Function for Aid History (Just in case it was overwritten)
function loadMyAidHistory() {
    $.ajax({ url: 'api/resident/get_my_aid_history.php', type: 'GET', dataType: 'json', success: function(res) {
        var listHtml = ''; var tableHtml = '';
        if (Array.isArray(res) && res.length > 0) {
            res.forEach(function(item) {
                var d = item.date_distributed ? new Date(item.date_distributed).toLocaleDateString() : 'N/A';
                listHtml += `<li class="flex justify-between p-2 rounded hover:bg-white/5"><div><p class="text-white font-medium">${item.item_name}</p><p class="text-xs text-slate-400">${d}</p></div><p class="text-white font-bold">x${item.quantity}</p></li>`;
                tableHtml += `<tr class="border-b border-[#283039]"><td class="px-6 py-4 text-white">${item.item_name}</td><td class="px-6 py-4 text-slate-300">${d}</td><td class="px-6 py-4 text-right text-green-400 font-bold">x${item.quantity}</td></tr>`;
            });
        } else {
            listHtml = '<li class="text-center py-4 text-slate-400">No aid recorded.</li>';
            tableHtml = '<tr><td colspan="3" class="text-center py-8 text-slate-400">No history found.</td></tr>';
        }
        $('#my-aid-history-list').html(listHtml);
        $('#aid-history-table-body').html(tableHtml);
    }});
}

// Edit/Delete Member Logic (Event Delegation)
$(document).on('click', '.edit-member-btn', function() {
    var index = $(this).data('index');
    if (window.myMembers && window.myMembers[index]) {
        var m = window.myMembers[index];
        $('#member_id').val(m.id);
        $('input[name="first_name"]').val(m.first_name);
        $('input[name="last_name"]').val(m.last_name);
        $('input[name="birthdate"]').val(m.birthdate);
        $('select[name="gender"]').val(m.gender);
        $('textarea[name="remarks"]').val(m.remarks);
        $('input[name="is_pwd"]').prop('checked', m.is_pwd == 1);
        $('input[name="is_senior"]').prop('checked', m.is_senior == 1);
        $('#add-member-modal').removeClass('hidden');
    }
});

$(document).on('click', '.delete-member-btn', function() {
    var id = $(this).data('id');
    if(confirm('Delete member?')) {
        $.ajax({ url: 'api/resident/delete_member.php', type: 'POST', data: { member_id: id }, dataType: 'json',
            success: function(res) { if(res.success) location.reload(); }
        });
    }
});