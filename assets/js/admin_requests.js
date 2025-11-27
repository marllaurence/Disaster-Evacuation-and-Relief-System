// assets/js/admin_requests.js

// --- Global Variables to store state ---
let targetRequestId = null;
let targetStatus = null;

// ==========================================
// 1. GLOBAL FUNCTIONS (Called by HTML buttons)
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

    // Reset Styles
    iconBg.removeClass('bg-yellow-500/20 bg-green-500/20 text-yellow-500 text-green-500');
    btn.removeClass('bg-yellow-600 hover:bg-yellow-700 bg-green-600 hover:bg-green-700');

    // Customize based on Action
    if (status === 'In Progress') {
        // ACCEPT MODE
        iconBg.addClass('bg-yellow-500/20 text-yellow-500');
        icon.text('pending_actions');
        title.text('Accept Request?');
        msg.text('This will mark the request as "In Progress". You are acknowledging that help is on the way.');
        btn.addClass('bg-yellow-600 hover:bg-yellow-700').text('Accept Request');
    } else {
        // COMPLETE MODE
        iconBg.addClass('bg-green-500/20 text-green-500');
        icon.text('check_circle');
        title.text('Mark as Completed?');
        msg.text('This will move the request to the archive. Ensure the resident has received assistance.');
        btn.addClass('bg-green-600 hover:bg-green-700').text('Complete Request');
    }

    modal.removeClass('hidden');
};

// ==========================================
// 2. DOCUMENT READY
// ==========================================
$(document).ready(function() {
    
    // Initial Load
    loadRequests();
    
    // Auto-refresh every 30 seconds
    setInterval(loadRequests, 30000);

    // --- Modal Button Handlers ---
    $('#cancel-status-btn').on('click', function() {
        $('#status-modal').addClass('hidden');
    });

    $('#confirm-status-btn').on('click', function() {
        performUpdate();
    });
});

// ==========================================
// 3. INTERNAL LOGIC
// ==========================================

function performUpdate() {
    var btn = $('#confirm-status-btn');
    var originalText = btn.text();
    btn.text('Updating...').prop('disabled', true);

    $.ajax({
        url: 'api/admin/update_request_status.php',
        type: 'POST',
        data: { id: targetRequestId, status: targetStatus },
        dataType: 'json',
        success: function(res) {
            if(res.success) {
                $('#status-modal').addClass('hidden');
                loadRequests(); // Refresh table
            } else {
                alert("Error: " + res.message);
            }
        },
        error: function() {
            alert("System connection error.");
        },
        complete: function() {
            btn.text(originalText).prop('disabled', false);
        }
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

            if (data.length === 0) {
                tbody.html('<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500 italic">No active requests.</td></tr>');
                return;
            }

            data.forEach(function(req) {
                // Status Badge Logic
                var statusBadge = '';
                if(req.status === 'Pending') statusBadge = '<span class="bg-red-500/10 text-red-400 px-2 py-1 rounded border border-red-500/20 text-[10px] uppercase font-bold tracking-wide">Pending</span>';
                else if(req.status === 'In Progress') statusBadge = '<span class="bg-yellow-500/10 text-yellow-400 px-2 py-1 rounded border border-yellow-500/20 text-[10px] uppercase font-bold tracking-wide">In Progress</span>';
                else statusBadge = '<span class="bg-green-500/10 text-green-400 px-2 py-1 rounded border border-green-500/20 text-[10px] uppercase font-bold tracking-wide">Completed</span>';

                // Action Buttons Logic
                var actions = '';
                if(req.status === 'Pending') {
                    actions = `<button onclick="window.openStatusModal(${req.id}, 'In Progress')" class="text-white bg-yellow-600 hover:bg-yellow-700 px-3 py-1.5 rounded text-xs font-bold shadow-lg transition-all flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">play_arrow</span> ACCEPT</button>`;
                } else if (req.status === 'In Progress') {
                    actions = `<button onclick="window.openStatusModal(${req.id}, 'Completed')" class="text-white bg-green-600 hover:bg-green-700 px-3 py-1.5 rounded text-xs font-bold shadow-lg transition-all flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">check</span> DONE</button>`;
                } else {
                    actions = `<span class="text-slate-600 text-xs italic flex items-center justify-end gap-1"><span class="material-symbols-outlined text-[16px]">archive</span> Archived</span>`;
                }

                // Location Logic
                var locationInfo = `<span class="text-slate-500 text-xs">No GPS</span>`;
                if (req.latitude && req.longitude) {
                    locationInfo = `
                        <a href="https://www.google.com/maps?q=${req.latitude},${req.longitude}" target="_blank" class="flex items-center gap-1 text-primary hover:text-white transition-colors group">
                            <span class="material-symbols-outlined text-[16px] group-hover:animate-bounce">location_on</span>
                            <span class="text-xs font-medium">View Map</span>
                        </a>
                    `;
                }

                var row = `
                    <tr class="hover:bg-[#222831] transition-colors border-b border-[#283039] last:border-0">
                        <td class="px-6 py-4 font-medium text-white">
                            ${req.first_name} ${req.last_name}
                            <div class="text-[10px] text-slate-500 mt-0.5">${new Date(req.created_at).toLocaleString()}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-slate-300 text-xs font-bold mb-1">${req.zone_purok || 'Unknown Zone'}</div>
                            ${locationInfo}
                        </td>
                        <td class="px-6 py-4 text-slate-300 text-sm font-medium">${req.request_type}</td>
                        <td class="px-6 py-4 text-slate-400 text-sm max-w-xs truncate" title="${req.description}">${req.description}</td>
                        <td class="px-6 py-4 text-center">${statusBadge}</td>
                        <td class="px-6 py-4 text-right flex justify-end">${actions}</td>
                    </tr>
                `;
                tbody.append(row);
            });
        }
    });
}