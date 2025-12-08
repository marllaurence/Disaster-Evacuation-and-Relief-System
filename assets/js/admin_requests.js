// assets/js/admin_requests.js

// --- Global Variables ---
let targetRequestId = null;
let targetStatus = null;
let allRequestsData = [];
let requestsMap;

// ==========================================
// 1. GLOBAL FUNCTIONS
// ==========================================

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
    modal.removeClass('hidden');
};

// ==========================================
// 2. MAP LOGIC (LIVE MAP)
// ==========================================
function initRequestsMap() {
    if (requestsMap) { requestsMap.invalidateSize(); return; }
    
    const matiLat = 6.9567;
    const matiLng = 126.2174;
    
    requestsMap = L.map('requests-map', { center: [matiLat, matiLng], zoom: 13, minZoom: 11 });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(requestsMap);
    
    // Loop through requests and plot
    allRequestsData.forEach(req => {
        if (req.latitude && req.longitude) {
            var color = '#ef4444'; // Red (Pending)
            if(req.status === 'In Progress') color = '#eab308'; // Yellow
            if(req.status === 'Completed') color = '#22c55e'; // Green

            var popup = `<b>${req.request_type}</b><br>${req.first_name} ${req.last_name}<br><i>${req.description}</i>`;
            
            L.circleMarker([req.latitude, req.longitude], {
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
    setInterval(loadRequests, 30000); // Auto-refresh

    $('#cancel-status-btn').on('click', function() {
        $('#status-modal').addClass('hidden');
    });

    $('#confirm-status-btn').on('click', function() {
        performUpdate();
    });

    // MAP BUTTON
    $('#open-requests-map-btn').on('click', function() {
        $('#requests-map-modal').removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden');
        setTimeout(function() { initRequestsMap(); }, 300);
    });

    $('.close-modal-btn').on('click', function() {
        $('#requests-map-modal').addClass('hidden').removeClass('flex');
        $('body').removeClass('overflow-hidden');
    });
});

// ==========================================
// 4. DATA LOADING
// ==========================================

function performUpdate() {
    var btn = $('#confirm-status-btn');
    btn.text('Updating...').prop('disabled', true);

    $.ajax({
        url: 'api/admin/update_request_status.php',
        type: 'POST',
        data: { id: targetRequestId, status: targetStatus },
        dataType: 'json',
        success: function(res) {
            if(res.success) { $('#status-modal').addClass('hidden'); loadRequests(); } 
            else { alert("Error: " + res.message); }
        },
        complete: function() { btn.text('Confirm').prop('disabled', false); }
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
            allRequestsData = data; // Store for map

            if (data.length === 0) { tbody.html('<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500 italic">No active requests.</td></tr>'); return; }

            data.forEach(function(req) {
                var statusBadge = '';
                if(req.status === 'Pending') statusBadge = '<span class="bg-red-500/10 text-red-400 px-2 py-1 rounded border border-red-500/20 text-[10px] uppercase font-bold">Pending</span>';
                else if(req.status === 'In Progress') statusBadge = '<span class="bg-yellow-500/10 text-yellow-400 px-2 py-1 rounded border border-yellow-500/20 text-[10px] uppercase font-bold">In Progress</span>';
                else statusBadge = '<span class="bg-green-500/10 text-green-400 px-2 py-1 rounded border border-green-500/20 text-[10px] uppercase font-bold">Completed</span>';

                var actions = '';
                if(req.status === 'Pending') actions = `<button onclick="window.openStatusModal(${req.id}, 'In Progress')" class="text-white bg-yellow-600 hover:bg-yellow-700 px-3 py-1.5 rounded text-xs font-bold flex items-center gap-1">ACCEPT</button>`;
                else if (req.status === 'In Progress') actions = `<button onclick="window.openStatusModal(${req.id}, 'Completed')" class="text-white bg-green-600 hover:bg-green-700 px-3 py-1.5 rounded text-xs font-bold flex items-center gap-1">DONE</button>`;
                else actions = `<span class="text-slate-600 text-xs italic">Archived</span>`;

                var loc = (req.latitude && req.longitude) ? `<a href="https://www.google.com/maps?q=${req.latitude},${req.longitude}" target="_blank" class="flex items-center gap-1 text-primary hover:text-white text-xs font-bold uppercase"><span class="material-symbols-outlined text-[16px]">location_on</span> Map</a>` : `<span class="text-slate-500 text-xs">No GPS</span>`;

                tbody.append(`
                    <tr class="hover:bg-[#222831] transition-colors border-b border-[#283039]">
                        <td class="px-6 py-4 font-medium text-white">${req.first_name} ${req.last_name}<div class="text-[10px] text-slate-500">${new Date(req.created_at).toLocaleString()}</div></td>
                        <td class="px-6 py-4"><div class="text-slate-300 text-xs font-bold">${req.zone_purok || 'Unknown'}</div>${loc}</td>
                        <td class="px-6 py-4 text-slate-300 text-sm">${req.request_type}</td>
                        <td class="px-6 py-4 text-slate-400 text-sm max-w-xs truncate" title="${req.description}">${req.description}</td>
                        <td class="px-6 py-4 text-center">${statusBadge}</td>
                        <td class="px-6 py-4 text-right flex justify-end">${actions}</td>
                    </tr>
                `);
            });
            
            // Refresh Map if open
            if(requestsMap) { requestsMap.eachLayer(l => { if(l instanceof L.CircleMarker) requestsMap.removeLayer(l); }); initRequestsMap(); }
        }
    });
}