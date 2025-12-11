$(document).ready(function() {

    // --- Global Data Storage ---
    var allCenters = [];
    var map; // Add Map
    var marker; // Add Marker
    var centersMap; // The Big Map

    // --- Load Centers on Page Load ---
    loadCenters();

    // ==========================================
    // 1. MAP LOGIC (Add Center Map)
    // ==========================================
    function initMap() {
        if (map) return;
        const matiLat = 6.9567;
        const matiLng = 126.2174;

        map = L.map('center-map', {
            center: [matiLat, matiLng], zoom: 13, minZoom: 11,
            maxBounds: [[6.80, 126.00], [7.15, 126.50]], maxBoundsViscosity: 1.0 
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, attribution: '© OpenStreetMap'
        }).addTo(map);

        map.on('click', function(e) {
            if (marker) marker.setLatLng(e.latlng); else marker = L.marker(e.latlng).addTo(map);
            $('#latitude').val(e.latlng.lat.toFixed(8)); $('#longitude').val(e.latlng.lng.toFixed(8));
        });
    }
    initMap();

    // ==========================================
    // 2. BIG MAP LOGIC (VIEW ALL)
    // ==========================================
    function initCentersMap() {
        if (centersMap) { centersMap.invalidateSize(); return; }
        
        centersMap = L.map('all-centers-map', { center: [6.9567, 126.2174], zoom: 13, minZoom: 11 });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(centersMap);
        
        // Plot Centers
        allCenters.forEach(c => {
            if (c.latitude && c.longitude) {
                var color = c.is_active == 1 ? '#22c55e' : '#ef4444'; // Green or Red
                var popup = `<b>${c.center_name}</b><br>${c.address}<br>Cap: ${c.capacity}`;
                
                L.circleMarker([c.latitude, c.longitude], {
                    radius: 8, fillColor: color, color: "#fff", weight: 2, opacity: 1, fillOpacity: 1
                }).addTo(centersMap).bindPopup(popup);
            }
        });
    }

    // Button Handler
    $('#open-all-centers-map-btn').on('click', function() {
        $('#all-centers-map-modal').removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden');
        setTimeout(function() { initCentersMap(); }, 300);
    });


    // ==========================================
    // 3. LOAD CENTERS FUNCTION
    // ==========================================
    // ==========================================
    // 3. LOAD CENTERS FUNCTION (UPDATED)
    // ==========================================
    function loadCenters() {
        $.ajax({
            url: 'api/evacuation/get_centers.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var tbody = $('#centers-table-body');
                tbody.empty();
                allCenters = data; 

                if (data.length === 0) {
                    tbody.html('<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">No centers found.</td></tr>');
                    return;
                }

                data.forEach(function(center) {
                    var statusBadge = center.is_active == 1 
                        ? '<span class="bg-green-500/10 text-green-400 border border-green-500/20 px-2 py-1 rounded text-[10px] uppercase font-bold tracking-wide">Active</span>'
                        : '<span class="bg-red-500/10 text-red-400 border border-red-500/20 px-2 py-1 rounded text-[10px] uppercase font-bold tracking-wide">Inactive</span>';

                    var occupancy = center.current_occupancy || 0;
                    var remaining = center.capacity - occupancy;
                    var remainingColor = remaining < 10 ? 'text-red-400' : (remaining < 50 ? 'text-yellow-400' : 'text-green-400');

                    var locationDisplay = '<span class="text-slate-600 text-xs italic">No Pin</span>';
                    if (center.latitude && center.longitude) {
                        locationDisplay = `
                            <a href="http://maps.google.com/?q=${center.latitude},${center.longitude}" target="_blank" class="flex items-center gap-1 text-primary hover:text-white transition-colors group">
                                <span class="material-symbols-outlined text-[16px] group-hover:animate-bounce">location_on</span>
                                <span class="text-xs font-medium">View Map</span>
                            </a>`;
                    }

                    var addressText = center.address ? center.address : '<span class="text-slate-600 italic text-xs">No Address</span>';

                    // --- THIS IS THE UPDATED ROW HTML ---
                    var row = `
                        <tr class="border-b border-[#283039] hover:bg-[#222831] transition-colors">
                            <td class="px-6 py-4 text-white font-medium text-sm">${center.center_name}</td>
                            <td class="px-6 py-4">${statusBadge}</td>
                            <td class="px-6 py-4 text-center text-gray-300 text-sm">${occupancy} / ${center.capacity}</td>
                            <td class="px-6 py-4 text-center font-bold text-sm ${remainingColor}">${remaining}</td>
                            <td class="px-6 py-4 text-gray-400 text-sm max-w-xs truncate" title="${center.address}">${addressText}</td>
                            <td class="px-6 py-4">${locationDisplay}</td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-3">
                                    
                                    <a href="check_in.php?center_id=${center.id}&center_name=${encodeURIComponent(center.center_name)}" 
                                       class="text-slate-400 hover:text-green-500 transition-colors p-1" 
                                       title="Check In Resident">
                                        <span class="material-symbols-outlined text-[20px]">how_to_reg</span>
                                    </a>

                                    <button onclick="window.openEditModal(${center.id})" class="text-slate-400 hover:text-yellow-400 transition-colors p-1" title="Edit Center">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>

                                    <button onclick="window.openDeleteModal(${center.id})" class="text-slate-400 hover:text-red-400 transition-colors p-1" title="Delete Center">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            }
        });
    }

    // --- ADD SUBMIT ---
    $('#add-center-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({ url: 'api/evacuation/add_center.php', type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(res) { 
                var msg = $('#form-message');
                msg.text(res.message).removeClass('text-green-400 text-red-400').addClass(res.success ? 'text-green-400' : 'text-red-400');
                if (res.success) { $('#add-center-form')[0].reset(); if(marker && map) { map.removeLayer(marker); marker = null; } loadCenters(); }
            }
        });
    });

    // --- MODAL LOGIC ---
    window.openEditModal = function(id) {
        var center = allCenters.find(c => c.id == id);
        if (center) {
            $('#edit_center_id').val(center.id);
            $('#edit_center_name').val(center.center_name);
            $('#edit_address').val(center.address);
            $('#edit_capacity').val(center.capacity);
            $('#edit_is_active').prop('checked', center.is_active == 1);
            $('#edit-modal').removeClass('hidden').addClass('flex');
        }
    };

    $('#edit-center-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        if (!$('#edit_is_active').is(':checked')) { formData.push({name: 'is_active', value: 0}); }
        $.ajax({ url: 'api/evacuation/update_center.php', type: 'POST', data: formData, dataType: 'json',
            success: function(res) { if (res.success) { alert("Updated!"); $('#edit-modal').addClass('hidden').removeClass('flex'); loadCenters(); } else alert(res.message); }
        });
    });

    window.openDeleteModal = function(id) { $('#delete_center_id').val(id); $('#delete-modal').removeClass('hidden').addClass('flex'); };
    
    $('#confirm-delete-btn').on('click', function() {
        $.ajax({ url: 'api/evacuation/delete_center.php', type: 'POST', data: { id: $('#delete_center_id').val() }, dataType: 'json',
            success: function(res) { if (res.success) { $('#delete-modal').addClass('hidden').removeClass('flex'); loadCenters(); } else alert(res.message); }
        });
    });

    // --- CLOSE ALL ---
    $('.close-modal-btn, .cancel-modal-btn, .cancel-delete-modal-btn').on('click', function() {
        $('#edit-modal').addClass('hidden').removeClass('flex');
        $('#delete-modal').addClass('hidden').removeClass('flex');
        $('#all-centers-map-modal').addClass('hidden').removeClass('flex');
        $('body').removeClass('overflow-hidden');
    });

});