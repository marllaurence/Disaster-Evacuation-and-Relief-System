$(document).ready(function() {

    // ==========================================
    // 1. LOAD REQUESTS FUNCTION (UPDATED)
    // ==========================================
    function loadRequests() {
        const noCacheUrl = 'api/resident/get_my_requests.php?v=' + new Date().getTime();

        $.ajax({
            url: noCacheUrl,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var container = $('#requests-container');
                container.empty();

                if (response.success && response.data && response.data.length > 0) {
                    
                    response.data.forEach(function(req) {
                        
                        // --- Status Logic ---
                        let statusColor, statusIcon, statusText;
                        let rejectionHtml = ''; // Variable for the rejection message

                        if (req.status === 'Pending') {
                            statusColor = 'border-l-yellow-500 text-yellow-500';
                            statusIcon = 'pending';
                            statusText = 'Pending Approval';
                        } else if (req.status === 'In Progress') {
                            statusColor = 'border-l-blue-500 text-blue-400';
                            statusIcon = 'hourglass_top';
                            statusText = 'Assistance On The Way';
                        } else if (req.status === 'Completed' || req.status === 'Approved') {
                            statusColor = 'border-l-green-500 text-green-400';
                            statusIcon = 'check_circle';
                            statusText = 'Resolved';
                        } else if (req.status === 'Rejected') {
                            // --- REJECTED LOGIC ---
                            statusColor = 'border-l-red-500 text-red-400';
                            statusIcon = 'cancel';
                            statusText = 'Request Rejected';
                            
                            // Create the Red Box for the reason
                            let reasonText = req.rejection_reason || 'No specific reason provided.';
                            rejectionHtml = `
                                <div class="mt-3 p-3 bg-red-500/10 border border-red-500/20 rounded-lg">
                                    <p class="text-xs font-bold text-red-400 uppercase flex items-center gap-1">
                                        <span class="material-symbols-outlined !text-[16px]">report</span> 
                                        Why was this rejected?
                                    </p>
                                    <p class="text-sm text-slate-300 mt-1 italic">"${reasonText}"</p>
                                </div>
                            `;
                        } else {
                            statusColor = 'border-l-slate-500 text-slate-400';
                            statusIcon = 'archive';
                            statusText = 'Archived';
                        }

                        // --- Image Logic (Same as before) ---
                        let imageHtml = '';
                        if (req.image_proof && req.image_proof.trim() !== "") {
                            let finalPath = req.image_proof; 
                            imageHtml = `
                                <div class="mt-3 pt-3 border-t border-[#283039]">
                                    <p class="text-xs text-slate-500 mb-2 font-bold uppercase">Attached Proof</p>
                                    <a href="${finalPath}" target="_blank" class="block w-full h-32 rounded-lg overflow-hidden border border-[#314d68] relative group">
                                        <img src="${finalPath}" class="w-full h-full object-cover transition-transform group-hover:scale-105" onerror="this.parentElement.innerHTML='<div class=\'h-full w-full flex items-center justify-center bg-red-900/20 text-red-400 text-xs\'>File not found</div>';">
                                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                            <span class="text-white text-xs font-bold flex items-center gap-1">View Full</span>
                                        </div>
                                    </a>
                                </div>
                            `;
                        }

                        // --- Build Card ---
                        var html = `
                            <div class="bg-[#1a222c] rounded-lg border border-[#283039] border-l-4 ${statusColor} p-5 shadow-lg relative overflow-hidden group transition-all hover:bg-[#202935] mb-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="text-white font-bold text-lg">${req.request_type}</h3>
                                        <p class="text-xs text-slate-500 font-mono">${req.created_at}</p>
                                    </div>
                                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#111418] border border-[#314d68]">
                                        <span class="material-symbols-outlined text-[18px] ${statusColor.split(' ')[1]}">${statusIcon}</span>
                                        <span class="text-xs font-bold ${statusColor.split(' ')[1]}">${req.status}</span>
                                    </div>
                                </div>
                                <p class="text-slate-300 text-sm leading-relaxed border-t border-[#283039] pt-3 mt-1">
                                    "${req.description}"
                                </p>

                                ${rejectionHtml} ${imageHtml} 
                                
                                <div class="mt-3 flex items-center gap-2 text-xs font-medium ${statusColor.split(' ')[1]} opacity-80">
                                    <span class="material-symbols-outlined text-[16px]">info</span>
                                    <span>${statusText}</span>
                                </div>
                            </div>
                        `;
                        container.append(html);
                    });

                } else {
                    container.html('<div class="col-span-full text-center py-20 text-slate-500">No requests found.</div>');
                }
            },
            error: function() {
                $('#requests-container').html('<div class="col-span-full text-center py-20 text-red-400">Error loading requests.</div>');
            }
        });
    }

    // Call load immediately when page opens
    loadRequests();

    // ==========================================
    // 2. MODAL CONTROLS
    // ==========================================
    
    // Open Request Modal
    $('#sidebar-request-btn').on('click', function() {
        $('#request-modal').removeClass('hidden').addClass('flex');
    });
    
    // Close Request Modal
    $('#close-request-btn, #cancel-request-btn').on('click', function() {
        $('#request-modal').addClass('hidden').removeClass('flex');
    });

    // Close Success Modal
    $('#close-success-btn').on('click', function() {
        $('#success-modal').addClass('hidden').removeClass('flex');
    });

    // ==========================================
    // 3. SUBMIT FORM
    // ==========================================
    $('#request-form').on('submit', function(e) {
        e.preventDefault();
        
        // UX: Change button text to indicate loading
        var btn = $(this).find('button[type="submit"]');
        var originalText = btn.text();
        btn.prop('disabled', true).text('Uploading...');

        // Create FormData (Required for file uploads)
        var formData = new FormData(this);

        $.ajax({
            url: 'api/resident/submit_request.php',
            type: 'POST',
            data: formData,
            contentType: false, // Required for files
            processData: false, // Required for files
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    // 1. Hide Input Modal
                    $('#request-modal').addClass('hidden').removeClass('flex');
                    
                    // 2. Show Success Modal
                    $('#success-modal').removeClass('hidden').addClass('flex');
                    
                    // 3. Reset Form
                    $('#request-form')[0].reset();
                    
                    // 4. Reload the list to show new item
                    loadRequests(); 
                } else {
                    alert("Error: " + res.message);
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("System Error. Please check console (F12) for details.");
            },
            complete: function() {
                // Restore button state
                btn.prop('disabled', false).text(originalText);
            }
        });
    }); 

});