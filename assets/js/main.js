// ==========================================
// 1. GLOBAL SETUP
// ==========================================
window.allHouseholdsData = []; 
var editMap, editMarker;
var bigMap; 

// GLOBAL EDIT FUNCTION
window.editHousehold = function(id) {
    var household = window.allHouseholdsData.find(h => h.id == id);
    if (household) {
        $('#edit_household_id').val(household.id);
        $('#edit_head_name').val(household.household_head_name);
        $('#edit_zone').val(household.zone_purok);
        $('#edit_address').val(household.address_notes);
        $('#edit_latitude').val(household.latitude);
        $('#edit_longitude').val(household.longitude);

        $('#edit-household-modal').removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden');
        setTimeout(function() { initEditMap(household.latitude, household.longitude); }, 300);
    } else { alert("Error: Data not found."); }
};

// GLOBAL DELETE FUNCTION
window.deleteHousehold = function(id, encodedName) {
    $('#delete-household-name').text(decodeURIComponent(encodedName));
    $('#confirm-delete-btn').data('id', id);
    $('#delete-confirm-modal').removeClass('hidden').addClass('flex');
};

function initEditMap(lat, lng) {
    if (editMap) {
        editMap.invalidateSize();
        if (lat && lng) { editMap.setView([lat, lng], 15); setEditMarker([lat, lng]); } 
        else { editMap.setView([6.9567, 126.2174], 13); if(editMarker) editMap.removeLayer(editMarker); }
        return;
    }
    editMap = L.map('edit-household-map', { center: [lat||6.9567, lng||126.2174], zoom: 13, minZoom: 11 });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(editMap);
    editMap.on('click', function(e) { setEditMarker(e.latlng); });
    if(lat && lng) setEditMarker([lat, lng]);
}

function setEditMarker(location) {
    if (editMarker) editMarker.setLatLng(location); else editMarker = L.marker(location).addTo(editMap);
    $('#edit_latitude').val(location.lat ? location.lat.toFixed(8) : location[0].toFixed(8));
    $('#edit_longitude').val(location.lng ? location.lng.toFixed(8) : location[1].toFixed(8));
}

// ==========================================
// 2. DOCUMENT READY
// ==========================================
$(document).ready(function() {
    console.log("Main.js Loaded - V112 (Fixed Success Close)");

    var map, marker;
    var addModal = $('#add-household-modal');
    var allMapModal = $('#all-households-map-modal');
    var successModal = $('#success-modal');

    // --- 1. ADD HOUSEHOLD MAP ---
    function initMap() {
        if (map) return;
        map = L.map('household-map', { center: [6.9567, 126.2174], zoom: 13, minZoom: 11 });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
        map.on('click', function(e) {
            if (marker) marker.setLatLng(e.latlng); else marker = L.marker(e.latlng).addTo(map);
            $('#latitude').val(e.latlng.lat.toFixed(8)); $('#longitude').val(e.latlng.lng.toFixed(8));
        });
    }

    // --- 2. BIG MAP LOGIC ---
    function initBigMap() {
        if (bigMap) { bigMap.invalidateSize(); return; }
        
        bigMap = L.map('all-households-map', { center: [6.9567, 126.2174], zoom: 13, minZoom: 11 });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(bigMap);
        
        window.allHouseholdsData.forEach(h => {
            if (h.latitude && h.longitude) {
                var popup = `<b>${h.household_head_name}</b><br>${h.zone_purok}<br>Members: ${h.member_count}`;
                L.circleMarker([h.latitude, h.longitude], {
                    radius: 6, fillColor: "#3b82f6", color: "#fff", weight: 1, opacity: 1, fillOpacity: 0.8
                }).addTo(bigMap).bindPopup(popup);
            }
        });
    }

    // --- MODAL OPENERS ---
    $('#open-add-household-btn').on('click', function() {
        addModal.removeClass('hidden'); $('body').addClass('overflow-hidden');
        setTimeout(function() { if (!map) initMap(); else map.invalidateSize(); }, 300);
    });

    $('#open-all-map-btn').on('click', function() {
        allMapModal.removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden');
        setTimeout(function() { initBigMap(); }, 300);
    });

    // --- CLOSE ALL MODALS (FIXED LINE BELOW) ---
    // Added '#close-success-btn' to this list
    $(document).on('click', '.close-modal-btn, #close-modal-btn, #cancel-modal-btn, #close-success-btn', function() {
        $('.fixed').addClass('hidden').removeClass('flex');
        $('body').removeClass('overflow-hidden');
    });

    // --- DATA LOADING ---
    function loadHouseholds() {
        $.ajax({
            url: 'api/resident/get_households.php', type: 'GET', dataType: 'json',
            success: function(data) {
                var tableBody = $('#households-table-body');
                tableBody.empty(); 
                window.allHouseholdsData = data;

                if (data.length === 0) { tableBody.html('<tr><td colspan="6" class="text-center py-8 text-slate-500">No data.</td></tr>'); return; }

                data.forEach(function(h) {
                    var loc = (h.latitude && h.longitude) ? 
                        `<a href="https://www.google.com/maps?q=${h.latitude},${h.longitude}" target="_blank" class="flex items-center gap-2 group hover:bg-white/5 p-1.5 rounded-lg transition-colors"><div class="p-1 rounded bg-red-500/10 border border-red-500/20 group-hover:bg-red-500/20"><span class="material-symbols-outlined text-red-500 !text-[18px]">location_on</span></div><div class="flex flex-col text-[10px] font-mono text-slate-300"><span>${parseFloat(h.latitude).toFixed(5)}</span><span>${parseFloat(h.longitude).toFixed(5)}</span></div></a>` : 
                        '<span class="text-slate-600 text-xs italic">No Pin</span>';
                    var safeName = encodeURIComponent(h.household_head_name);

                    tableBody.append(`
                        <tr class="border-b border-[#283039] hover:bg-[#222831] transition-colors">
                            <td class="px-6 py-4 text-white font-medium text-sm">${h.household_head_name}</td>
                            <td class="px-6 py-4 text-[#9dabb9] text-sm">${h.zone_purok || '-'}</td>
                            <td class="px-6 py-4 text-center"><span class="bg-[#283039] text-white text-xs px-2 py-1 rounded border border-slate-600 font-bold">${h.member_count}</span></td>
                            <td class="px-6 py-4">${loc}</td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="household_details.php?id=${h.id}" class="text-primary text-xs font-bold uppercase hover:text-blue-400 mr-2">Manage</a>
                                    <button onclick="window.editHousehold(${h.id})" class="text-slate-400 hover:text-yellow-400"><span class="material-symbols-outlined text-[20px]">edit</span></button>
                                    <button onclick="window.deleteHousehold(${h.id}, '${safeName}')" class="text-slate-400 hover:text-red-400"><span class="material-symbols-outlined text-[20px]">delete</span></button>
                                </div>
                            </td>
                        </tr>
                    `);
                });
                
                if(bigMap) { bigMap.eachLayer(l => { if(l instanceof L.CircleMarker) bigMap.removeLayer(l); }); initBigMap(); }
            }
        });
    }

    // --- FORMS ---
    $('#add-household-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({ url: 'api/resident/add_household.php', type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(res) { 
                if(res.success) { 
                    // Hide Add Modal
                    addModal.addClass('hidden'); 
                    
                    // Show Success Modal
                    $('#success-modal').removeClass('hidden').addClass('flex');
                    
                    $('#add-household-form')[0].reset(); 
                    if(marker && map) { map.removeLayer(marker); marker = null; } 
                    loadHouseholds(); 
                    loadDashboardStats(); 
                } 
                else alert(res.message); 
            }
        });
    });

    $('#edit-household-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({ url: 'api/resident/update_household.php', type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(res) { if(res.success) { alert("Updated!"); $('#edit-household-modal').addClass('hidden').removeClass('flex'); loadHouseholds(); } else alert(res.message); }
        });
    });

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