// ==========================================
// 1. GLOBAL SETUP
// ==========================================
window.allHouseholdsData = []; // Global Data Storage
var editMap, editMarker; // Edit Map Variables

// GLOBAL EDIT FUNCTION
window.editHousehold = function(id) {
    console.log("Edit clicked for ID: " + id); 

    var household = window.allHouseholdsData.find(h => h.id == id);

    if (household) {
        // Fill Text Inputs
        $('#edit_household_id').val(household.id);
        $('#edit_head_name').val(household.household_head_name);
        $('#edit_zone').val(household.zone_purok);
        $('#edit_address').val(household.address_notes);
        
        // Fill Coordinates
        $('#edit_latitude').val(household.latitude);
        $('#edit_longitude').val(household.longitude);

        // Show Modal
        $('#edit-household-modal').removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden');

        // Initialize Edit Map
        setTimeout(function() {
            initEditMap(household.latitude, household.longitude);
        }, 300);
    } else {
        alert("Error: Data not found. Refresh page.");
    }
};

// Helper: Initialize the Edit Map
function initEditMap(lat, lng) {
    if (editMap) {
        editMap.invalidateSize();
        if (lat && lng) {
            var loc = [lat, lng];
            editMap.setView(loc, 15);
            setEditMarker(loc);
        } else {
            editMap.setView([6.9567, 126.2174], 13); // Default Mati
            if (editMarker) editMap.removeLayer(editMarker);
        }
        return;
    }

    editMap = L.map('edit-household-map', {
        center: [lat || 6.9567, lng || 126.2174],
        zoom: 13, minZoom: 11,
        maxBounds: [[6.80, 126.00], [7.15, 126.50]], 
        maxBoundsViscosity: 1.0 
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19, attribution: '© OpenStreetMap'
    }).addTo(editMap);

    editMap.on('click', function(e) {
        setEditMarker(e.latlng);
    });

    if (lat && lng) setEditMarker([lat, lng]);
}

function setEditMarker(location) {
    if (editMarker) editMarker.setLatLng(location);
    else editMarker = L.marker(location).addTo(editMap);
    
    var lat = location.lat || location[0];
    var lng = location.lng || location[1];
    
    $('#edit_latitude').val(lat.toFixed(8)); 
    $('#edit_longitude').val(lng.toFixed(8));
}

// GLOBAL DELETE FUNCTION
window.deleteHousehold = function(id, encodedName) {
    var name = decodeURIComponent(encodedName);
    console.log("Delete clicked for: " + name);

    $('#delete-household-name').text(name);
    $('#confirm-delete-btn').data('id', id);
    $('#delete-confirm-modal').removeClass('hidden').addClass('flex');
};


// ==========================================
// 2. DOCUMENT READY
// ==========================================
$(document).ready(function() {
    console.log("Main.js Loaded - V107 (Clickable Maps)");

    var map; // Add Map
    var marker; // Add Marker
    var addModal = $('#add-household-modal');

    // --- ADD MAP LOGIC ---
    function initMap() {
        if (map) return;
        map = L.map('household-map', { center: [6.9567, 126.2174], zoom: 13, minZoom: 11 });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
        map.on('click', function(e) {
            if (marker) marker.setLatLng(e.latlng); else marker = L.marker(e.latlng).addTo(map);
            $('#latitude').val(e.latlng.lat.toFixed(8)); $('#longitude').val(e.latlng.lng.toFixed(8));
        });
    }

    // Open Add Modal
    $('#open-add-household-btn').on('click', function() {
        addModal.removeClass('hidden');
        $('body').addClass('overflow-hidden');
        setTimeout(function() { if (!map) initMap(); else map.invalidateSize(); }, 300);
    });

    // Close All Modals
    $(document).on('click', '.close-modal-btn, #close-modal-btn, #cancel-modal-btn, .close-delete-modal-btn', function() {
        $('.fixed').addClass('hidden').removeClass('flex');
        $('body').removeClass('overflow-hidden');
    });

    // Load Table
    function loadHouseholds() {
        $.ajax({
            url: 'api/resident/get_households.php', type: 'GET', dataType: 'json',
            success: function(data) {
                window.allHouseholdsData = data;
                var tableBody = $('#households-table-body');
                tableBody.empty(); 

                if (data.length === 0) { tableBody.html('<tr><td colspan="6" class="text-center py-8 text-slate-500">No data.</td></tr>'); return; }

                data.forEach(function(h) {
                    var loc = '<span class="text-slate-600 text-xs italic">No Pin</span>';
                    
                    // --- UPDATED MAP LINK LOGIC ---
                    if (h.latitude && h.longitude) {
                        loc = `
                            <a href="https://www.google.com/maps?q=${h.latitude},${h.longitude}" target="_blank" class="flex items-center gap-2 group hover:bg-white/5 p-1.5 rounded-lg transition-colors" title="View on Google Maps">
                                <div class="p-1 rounded bg-red-500/10 border border-red-500/20 group-hover:bg-red-500/20 transition-colors">
                                    <span class="material-symbols-outlined text-red-500 !text-[18px] group-hover:scale-110 transition-transform">location_on</span>
                                </div>
                                <div class="flex flex-col text-[10px] font-mono text-slate-300 leading-tight group-hover:text-white">
                                    <span>${parseFloat(h.latitude).toFixed(5)}</span>
                                    <span>${parseFloat(h.longitude).toFixed(5)}</span>
                                </div>
                            </a>`;
                    }

                    var safeName = encodeURIComponent(h.household_head_name);

                    var row = `
                        <tr class="border-b border-[#283039] hover:bg-[#222831] transition-colors">
                            <td class="px-6 py-4 text-[#9dabb9] text-sm">${h.id}</td>
                            <td class="px-6 py-4 text-white font-medium text-sm">${h.household_head_name}</td>
                            <td class="px-6 py-4 text-[#9dabb9] text-sm">${h.zone_purok || '-'}</td>
                            <td class="px-6 py-4 text-center"><span class="bg-[#283039] text-white text-xs px-2 py-1 rounded border border-slate-600 font-bold">${h.member_count}</span></td>
                            
                            <td class="px-6 py-4">${loc}</td>
                            
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="household_details.php?id=${h.id}" class="text-primary text-xs font-bold uppercase hover:text-blue-400 mr-2">Manage</a>
                                    <button onclick="window.editHousehold(${h.id})" class="text-slate-400 hover:text-yellow-400 transition-colors p-1">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    <button onclick="window.deleteHousehold(${h.id}, '${safeName}')" class="text-slate-400 hover:text-red-400 transition-colors p-1">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    tableBody.append(row);
                });
            }
        });
    }

    // Submit Handlers
    $('#add-household-form').on('submit', function(e) { e.preventDefault(); submitForm('api/resident/add_household.php', $(this)); });
    $('#edit-household-form').on('submit', function(e) { e.preventDefault(); submitForm('api/resident/update_household.php', $(this)); });
    
    function submitForm(url, form) {
        $.ajax({ url: url, type: 'POST', data: form.serialize(), dataType: 'json',
            success: function(res) { if(res.success) { alert(res.message || "Success!"); $('.fixed').addClass('hidden').removeClass('flex'); $('body').removeClass('overflow-hidden'); form[0].reset(); if(marker && map) { map.removeLayer(marker); marker = null; } loadHouseholds(); loadDashboardStats(); } else alert(res.message); }
        });
    }

    $('#confirm-delete-btn').on('click', function() {
        $.ajax({ url: 'api/resident/delete_household.php', type: 'POST', data: { id: $(this).data('id') }, dataType: 'json',
            success: function(res) { if(res.success) { $('#delete-confirm-modal').addClass('hidden').removeClass('flex'); loadHouseholds(); loadDashboardStats(); } else alert(res.message); }
        });
    });

    function loadDashboardStats() {
        $.ajax({ url: 'api/resident/get_resident_stats.php', type: 'GET', dataType: 'json',
            success: function(d) { $('#stats-total-households').text(d.total_households); $('#stats-total-residents').text(d.total_residents); $('#stats-affected-households').text(d.affected_households); $('#stats-residents-evacuated').text(d.residents_evacuated); }
        });
    }

    loadHouseholds();
    loadDashboardStats();
});