// assets/js/admin_requests.js

// --- Global Variables ---
let targetRequestId = null;
let targetStatus = null;
let allRequestsData = [];
let requestsMap;

// ==========================================
// 1. GLOBAL FUNCTIONS
// ==========================================

// NEW: Function to Open Map and Fly to Location
window.focusMapLocation = function(lat, lng) {
    // 1. Open the Modal
    $('#requests-map-modal').removeClass('hidden').addClass('flex');
    $('body').addClass('overflow-hidden');

    // 2. Wait for modal to render, then initialize and fly
    setTimeout(function() {
        initRequestsMap(); // Ensure map is built
        if (requestsMap) {
            // Zoom in to the specific coordinates (Level 18 is close-up)
            requestsMap.setView([lat, lng], 18);
        }
    }, 300);
};

// Open Image Modal
window.viewProof = function(imagePath) {
    var modal = $('#proof-modal');
    var img = $('#proof-image');
    img.attr('src', imagePath);
    modal.removeClass('hidden').addClass('flex');
};

// Open Standard Status Modal
window.openStatusModal = function(id, status) {
    targetRequestId = id;
    targetStatus = status;

    var modal = $('#status-modal');
    var iconBg = $('#status-icon-bg');
    var icon = $('#status-icon');
    var title = $('#status-title');
    var msg = $('#status-message');
    var btn = $('#confirm-status-btn');

    iconBg.removeClass('bg-yellow-500/20 bg-green-500/20 text-yellow-500 text-green-500');
    btn.removeClass('bg-yellow-600 hover:bg-yellow-700 bg-green-600 hover:bg-green-700');

    if (status === 'In Progress') {
        iconBg.addClass('bg-yellow-500/20 text-yellow-500');
        icon.text('pending_actions');
        title.text('Accept Request?');
        msg.text('Mark request as "In Progress".');
        btn.addClass('bg-yellow-600 hover:bg-yellow-700').text('Accept Request');
    } else {
        iconBg.addClass('bg-green-500/20 text-green-500');
        icon.text('check_circle');
        title.text('Mark as Completed?');
        msg.text('Move request to archive.');
        btn.addClass('bg-green-600 hover:bg-green-700').text('Complete Request');
    }
    modal.removeClass('hidden').addClass('flex');
};

// Open Reject Modal
window.openRejectModal = function(id) {
    targetRequestId = id;
    $('#reject-reason-input').val('');
    $('#reject-modal').removeClass('hidden').addClass('flex');
};

// ==========================================
// 2. MAP LOGIC
// ==========================================
function initRequestsMap() {
    if (requestsMap) { requestsMap.invalidateSize(); return; }
    
    // Default Center (Mati City)
    const matiLat = 6.9567;
    const matiLng = 126.2174;
    
    requestsMap = L.map('requests-map', { center: [matiLat, matiLng], zoom: 13, minZoom: 11 });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(requestsMap);
    
    // Sort & Jitter Logic
    let sortedData = [...allRequestsData].sort((a, b) => {
        const score = s => (s === 'Pending' ? 3 : (s === 'In Progress' ? 2 : 1));
        return score(a.status) - score(b.status);
    });

    sortedData.forEach(req => {
        if (req.latitude && req.longitude) {
            var color = '#ef4444'; 
            if(req.status === 'In Progress') color = '#eab308'; 
            if(req.status === 'Completed') color = '#22c55e'; 

            // Jitter to prevent stacking
            let jitter = 0.00015;
            let lat = parseFloat(req.latitude) + (Math.random() - 0.5) * jitter;
            let lng = parseFloat(req.longitude) + (Math.random() - 0.5) * jitter;

            var popup = `<b>${req.request_type}</b><br>${req.first_name} ${req.last_name}<br><i>${req.description}</i>`;
            
            L.circleMarker([lat, lng], {
                radius: 8, fillColor: color, color: "#fff", weight: 2, opacity: 1, fillOpacity: 1
            }).addTo(requestsMap).bindPopup(popup);
        }
    });
}

// ==========================================
// 3. DOCUMENT READY
// ==========================================
$(document).ready(function() {
    loadRequests();
    setInterval(loadRequests, 30000); 

    // Closers
    $('#cancel-status-btn').on('click', function() { $('#status-modal').addClass('hidden').removeClass('flex'); });
    $('#close-proof-btn').on('click', function() { $('#proof-modal').addClass('hidden').removeClass('flex'); });
    $('.close-modal-btn').on('click', function() { $('#requests-map-modal').addClass('hidden').removeClass('flex'); $('body').removeClass('overflow-hidden'); });
    $('#close-reject-btn, #cancel-reject-btn').on('click', function() { $('#reject-modal').addClass('hidden').removeClass('flex'); });

    // Actions
    $('#confirm-status-btn').on('click', function() { performUpdate(targetStatus, null); });
    
    $('#confirm-reject-btn').on('click', function() {
        var reason = $('#reject-reason-input').val();
        if(!reason.trim()) { alert("Please enter a reason."); return; }
        performUpdate('Rejected', reason);
    });

    $('#open-requests-map-btn').on('click', function() {
        $('#requests-map-modal').removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden');
        setTimeout(function() { initRequestsMap(); }, 300);
    });
});

// ==========================================
// 4. DATA LOADING
// ==========================================
function performUpdate(status, reason) {
    var btn = (status === 'Rejected') ? $('#confirm-reject-btn') : $('#confirm-status-btn');
    var originalText = btn.text();
    btn.text('Updating...').prop('disabled', true);

    $.ajax({
        url: 'api/admin/update_request_status.php',
        type: 'POST',
        data: { id: targetRequestId, status: status, reason: reason },
        dataType: 'json',
        success: function(res) {
            if(res.success) { 
                $('#status-modal').addClass('hidden').removeClass('flex'); 
                $('#reject-modal').addClass('hidden').removeClass('flex');
                loadRequests(); 
            } 
            else { alert("Error: " + res.message); }
        },
        complete: function() { btn.text(originalText).prop('disabled', false); }
    });
}

function loadRequests() {
    $.ajax({
        url: 'api/admin/get_all_requests.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var tbody = $('#requests-table-body');
            tbody.empty();
            allRequestsData = data; 

            if (data.length === 0) { tbody.html('<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500 italic">No active requests.</td></tr>'); return; }

            data.forEach(function(req) {
                // Status Badge logic
                var statusBadge = '';
                if(req.status === 'Pending') statusBadge = '<span class="bg-red-500/10 text-red-400 px-2 py-1 rounded border border-red-500/20 text-[10px] uppercase font-bold">Pending</span>';
                else if(req.status === 'In Progress') statusBadge = '<span class="bg-yellow-500/10 text-yellow-400 px-2 py-1 rounded border border-yellow-500/20 text-[10px] uppercase font-bold">In Progress</span>';
                else if(req.status === 'Rejected') statusBadge = '<span class="bg-red-900/20 text-red-500 px-2 py-1 rounded border border-red-500/20 text-[10px] uppercase font-bold">Rejected</span>';
                else statusBadge = '<span class="bg-green-500/10 text-green-400 px-2 py-1 rounded border border-green-500/20 text-[10px] uppercase font-bold">Completed</span>';

                // Actions Logic
                var actions = '';
                if(req.status === 'Pending') {
                    actions = `
                        <div class="flex gap-2 justify-end">
                            <button onclick="window.openRejectModal(${req.id})" class="text-white bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded text-xs font-bold flex items-center gap-1">REJECT</button>
                            <button onclick="window.openStatusModal(${req.id}, 'In Progress')" class="text-white bg-yellow-600 hover:bg-yellow-700 px-3 py-1.5 rounded text-xs font-bold flex items-center gap-1">ACCEPT</button>
                        </div>
                    `;
                }
                else if (req.status === 'In Progress') {
                    actions = `<button onclick="window.openStatusModal(${req.id}, 'Completed')" class="text-white bg-green-600 hover:bg-green-700 px-3 py-1.5 rounded text-xs font-bold flex items-center gap-1">DONE</button>`;
                }
                else if (req.status === 'Rejected') {
                    actions = `<span class="text-red-400 text-xs italic cursor-help" title="Reason: ${req.rejection_reason || 'No reason'}">Rejected</span>`;
                }
                else {
                    actions = `<span class="text-slate-600 text-xs italic">Archived</span>`;
                }

                // --- LOCATION LINK FIX ---
                // We use onclick="window.focusMapLocation(...)" instead of href="#"
                var loc = '';
                if (req.latitude && req.longitude) {
                    loc = `<button onclick="window.focusMapLocation(${req.latitude}, ${req.longitude})" class="flex items-center gap-1 text-primary hover:text-white text-xs font-bold uppercase mt-1 transition-colors"><span class="material-symbols-outlined text-[16px]">location_on</span> Map</button>`;
                } else {
                    loc = `<span class="text-xs text-slate-600 italic">No GPS</span>`;
                }

                // Photo Button
                var photoBtn = '<span class="text-slate-600 text-xs">No Photo</span>';
                if (req.image_proof && req.image_proof !== 'NULL' && req.image_proof !== '') {
                    photoBtn = `<button onclick="window.viewProof('${req.image_proof}')" class="text-blue-400 hover:text-white text-xs font-bold border border-blue-500/30 hover:bg-blue-600 px-2 py-1 rounded flex items-center gap-1 transition-colors"><span class="material-symbols-outlined text-[16px]">image</span> View</button>`;
                }

                tbody.append(`
                    <tr class="hover:bg-[#222831] transition-colors border-b border-[#283039]">
                        <td class="px-6 py-4 font-medium text-white">
                            ${req.first_name} ${req.last_name}
                            <div class="text-[10px] text-slate-500">${new Date(req.created_at).toLocaleString()}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-slate-300 text-xs font-bold">${req.zone_purok || 'Unknown'}</div>
                            ${loc}
                        </td>
                        <td class="px-6 py-4 text-slate-300 text-sm font-bold">${req.request_type}</td>
                        <td class="px-6 py-4 text-slate-400 text-sm max-w-xs truncate" title="${req.description}">${req.description}</td>
                        <td class="px-6 py-4 text-center">${photoBtn}</td> 
                        <td class="px-6 py-4 text-center">${statusBadge}</td>
                        <td class="px-6 py-4 text-right">${actions}</td>
                    </tr>
                `);
            });
            
            // Re-render map points if open
            if(requestsMap) { requestsMap.eachLayer(l => { if(l instanceof L.CircleMarker) requestsMap.removeLayer(l); }); initRequestsMap(); }
        }
    });
}