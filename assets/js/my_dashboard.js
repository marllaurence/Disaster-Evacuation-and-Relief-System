$(document).ready(function() {
    console.log("Dashboard JS Loaded - V111 (Fixed Aid History Table)");

    // --- GLOBAL VARIABLES ---
    var userSavedLat = null;
    var userSavedLng = null;
    var userMap;
    var userLocationMarker;

    // ==========================================
    // 1. DATA LOADERS
    // ==========================================
    
    // LOAD HOUSEHOLD
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
                        if (response.household.latitude && response.household.longitude) {
                            userSavedLat = parseFloat(response.household.latitude);
                            userSavedLng = parseFloat(response.household.longitude);
                        }
                    }
                    
                    tableBody.empty();
                    
                    if(response.members.length > 0) {
                        window.myMembers = response.members; // Save for editing
                        response.members.forEach(function(member, index) {
                            var row = `
                                <tr class="group hover:bg-white/5 transition-colors border-b border-[#283039] last:border-0">
                                    <td class="px-4 py-3 text-white text-sm font-medium">${member.first_name} ${member.last_name}</td>
                                    <td class="px-4 py-3 text-slate-300 text-sm">${member.birthdate || 'N/A'}</td>
                                    <td class="px-4 py-3 text-slate-300 text-sm capitalize">${member.gender || 'N/A'}</td>
                                    <td class="px-4 py-3 text-right">
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

    // LOAD AID HISTORY (FIXED: NOW UPDATES BOTH LIST AND TABLE)
    function loadMyAidHistory() {
        $.ajax({
            url: 'api/resident/get_my_aid_history.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                
                // 1. Dashboard Widget (Small List)
                var widgetList = $('#my-aid-history-list');
                if (widgetList.length) {
                    widgetList.empty();
                    if (Array.isArray(response) && response.length > 0) {
                        response.forEach(function(item) {
                            var d = item.date_distributed ? new Date(item.date_distributed).toLocaleDateString() : 'N/A';
                            widgetList.append(`<li class="flex justify-between p-2 rounded hover:bg-white/5"><div><p class="text-white font-medium">${item.item_name}</p><p class="text-xs text-slate-400">${d}</p></div><p class="text-white font-bold">x${item.quantity}</p></li>`);
                        });
                    } else {
                        widgetList.html('<li class="px-2 py-4 text-center text-slate-400">No aid recorded.</li>');
                    }
                }

                // 2. Full Page Table (The one stuck on Loading)
                var fullTable = $('#aid-history-table-body');
                if (fullTable.length) {
                    fullTable.empty(); // Removes "Loading history..."
                    
                    if (Array.isArray(response) && response.length > 0) {
                        response.forEach(function(item) {
                            var d = item.date_distributed ? new Date(item.date_distributed).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A';
                            var row = `
                                <tr class="hover:bg-white/5 border-b border-[#283039]">
                                    <td class="px-6 py-4 font-medium text-white">${item.item_name}</td>
                                    <td class="px-6 py-4 text-slate-300">${d}</td>
                                    <td class="px-6 py-4 text-right font-bold text-green-400">x${item.quantity}</td>
                                </tr>`;
                            fullTable.append(row);
                        });
                    } else {
                        fullTable.html('<tr><td colspan="3" class="px-6 py-8 text-center text-slate-400 italic">No aid history found.</td></tr>');
                    }
                }
            },
            error: function() {
                 $('#my-aid-history-list').html('<li class="text-center py-4 text-red-400">Error loading.</li>');
                 $('#aid-history-table-body').html('<tr><td colspan="3" class="text-center py-4 text-red-400">System Error.</td></tr>');
            }
        });
    }

    // ==========================================
    // 2. ADD / EDIT MEMBER LOGIC
    // ==========================================

    // OPEN ADD MODAL
    $(document).on('click', '#open-add-member-modal', function(e) {
        e.preventDefault();
        $('#member_id').val(''); 
        $('#add-member-form')[0].reset();
        $('#add-member-modal h3').text('Add Family Member');
        $('#add-member-modal').removeClass('hidden');
        $('body').addClass('overflow-hidden');
    });

    // OPEN EDIT MODAL
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
            
            $('#add-member-modal h3').text('Edit Family Member');
            $('#add-member-modal').removeClass('hidden');
            $('body').addClass('overflow-hidden');
        }
    });

    // SUBMIT FORM (Add/Update)
    $('#add-member-form').on('submit', function(e) {
        e.preventDefault();
        var btn = $(this).find('button[type="submit"]');
        btn.text('Saving...').prop('disabled', true);

        var id = $('#member_id').val();
        var apiUrl = id ? 'api/resident/update_member.php' : 'api/resident/add_member.php';

        $.ajax({
            url: apiUrl, type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(response) {
                if(response.success) {
                    $('.fixed').addClass('hidden'); 
                    $('body').removeClass('overflow-hidden');
                    loadMyHousehold(); 
                    alert(response.message);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            complete: function() { btn.text('Save').prop('disabled', false); }
        });
    });

    // DELETE MEMBER
    $(document).on('click', '.delete-member-btn', function() {
        var id = $(this).data('id');
        if(confirm('Delete this member?')) {
            $.ajax({ url: 'api/resident/delete_member.php', type: 'POST', data: { member_id: id }, dataType: 'json',
                success: function(res) { 
                    if(res.success) loadMyHousehold(); 
                    else alert(res.message);
                }
            });
        }
    });

    // ==========================================
    // 3. MAP, REQUEST & HISTORY LOGIC
    // ==========================================
    
    // View Map Modal
    $(document).on('click', '.view-map-btn, #view-map-btn', function(e) {
        e.preventDefault();
        $('#evac-map-modal').removeClass('hidden');
        $('body').addClass('overflow-hidden');
        setTimeout(function() { initUserMap(); }, 300);
    });

    // Locate Me
    $('#locate-me-btn').on('click', function() {
        var btn = $(this);
        if (userSavedLat && userSavedLng) {
            btn.html('<span class="material-symbols-outlined !text-[16px]">home_pin</span> My House');
            if (!userMap) initUserMap();
            if (userLocationMarker) userMap.removeLayer(userLocationMarker);
            userLocationMarker = L.marker([userSavedLat, userSavedLng]).addTo(userMap).bindPopup("<b>My House</b>").openPopup();
            userMap.setView([userSavedLat, userSavedLng], 16);
        } else {
            alert("No location recorded. Please contact Admin.");
        }
    });

    function initUserMap() {
        const matiLat = 6.9567;
        const matiLng = 126.2174;
        if (!userMap) {
            userMap = L.map('user-evac-map', { center: [matiLat, matiLng], zoom: 13 });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(userMap);
        } else { userMap.invalidateSize(); }

        $.ajax({ url: 'api/resident/get_active_centers.php', type: 'GET', dataType: 'json', success: function(centers) {
            centers.forEach(function(c) {
                if (c.latitude && c.longitude) {
                    var popup = `<b>${c.center_name}</b><br>${c.address}<br>Occ: ${c.occupancy}/${c.capacity}`;
                    L.circleMarker([parseFloat(c.latitude), parseFloat(c.longitude)], { color: '#fff', fillColor: '#22c55e', fillOpacity: 1, radius: 8, weight: 2 }).addTo(userMap).bindPopup(popup);
                }
            });
        }});
    }

    // Evacuation History
    $(document).on('click', '#view-evac-history-btn', function(e) {
        e.preventDefault();
        $('#evacuation-history-modal').removeClass('hidden');
        $('body').addClass('overflow-hidden');
        var tbody = $('#evacuation-history-table');
        tbody.html('<tr><td colspan="4" class="text-center py-4 text-slate-400">Loading...</td></tr>');
        $.ajax({
            url: 'api/resident/get_evacuation_history.php', type: 'GET', dataType: 'json',
            success: function(res) {
                tbody.empty();
                if (res.success && res.history.length > 0) {
                    res.history.forEach(function(log) {
                        var checkIn = new Date(log.check_in_time).toLocaleDateString();
                        var status = log.check_out_time ? '<span class="text-slate-500">Checked Out</span>' : '<span class="text-green-400 animate-pulse">Active</span>';
                        tbody.append(`<tr><td class="px-4 py-3">${log.first_name}</td><td class="px-4 py-3 text-slate-300">${log.center_name}</td><td class="px-4 py-3 text-slate-300">${checkIn}</td><td class="px-4 py-3">${status}</td></tr>`);
                    });
                } else { tbody.html('<tr><td colspan="4" class="text-center py-8 text-slate-400">No records found.</td></tr>'); }
            }
        });
    });

    // Request Assistance
    $(document).on('click', '#sidebar-request-btn, #main-request-btn', function(e) {
        e.preventDefault();
        $('#request-modal').removeClass('hidden');
        $('body').addClass('overflow-hidden');
    });

   // Request Assistance Submit Logic
    $('#request-form').on('submit', function(e) {
        e.preventDefault();
        
        // 1. visual feedback (Disable button so they don't click twice)
        var btn = $(this).find('button[type="submit"]');
        var originalText = btn.text();
        btn.prop('disabled', true).text('Sending...');

        var formData = new FormData(this);

        $.ajax({
            url: 'api/resident/submit_request.php',
            type: 'POST',
            data: formData,
            contentType: false, // REQUIRED for files
            processData: false, // REQUIRED for files
            dataType: 'json',   // Expect JSON back
            success: function(response) {
                if (response.success) {
                    // A. Hide the Request Input Modal
                    $('#request-modal').addClass('hidden');
                    
                    // B. Show the Success Message Modal
                    $('#success-modal').removeClass('hidden');
                    
                    // C. Clear the form for next time
                    $('#request-form')[0].reset();
                } else {
                    alert("Server Error: " + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", xhr.responseText);
                alert("System Error. Check console (F12) for details.");
            },
            complete: function() {
                // Re-enable the button
                btn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Close Modals
    $(document).on('click', '#close-modal-btn, #cancel-modal-btn, #close-evac-modal-btn, #close-evac-btn-bottom, #close-request-btn, #cancel-request-btn, #close-success-btn, #close-map-btn', function(e) {
        e.preventDefault();
        $('.fixed').addClass('hidden');
        $('body').removeClass('overflow-hidden');
    });

    // Initial Loads
    loadMyHousehold();
    loadMyAidHistory();
});