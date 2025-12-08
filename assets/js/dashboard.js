$(document).ready(function() {
    console.log("Command Center Dashboard Loaded - V2.0");

    // --- 1. DASHBOARD MAP LOGIC ---
    var map;

    function initDashboardMap() {
        // Center on Mati City
        const matiLat = 6.9567;
        const matiLng = 126.2174;

        map = L.map('dashboard-map', {
            center: [matiLat, matiLng],
            zoom: 13,
            minZoom: 11
        });

        // Dark Mode / Standard Tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // --- LAYER 1: EVACUATION CENTERS (Green = Active, Gray = Closed) ---
        $.ajax({
            url: 'api/evacuation/get_centers.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                data.forEach(c => {
                    if (c.latitude && c.longitude) {
                        var color = c.is_active == 1 ? '#22c55e' : '#64748b'; 
                        var statusText = c.is_active == 1 ? 'Active' : 'Closed';
                        
                        var popup = `
                            <div class="text-center">
                                <strong class="text-sm">${c.center_name}</strong><br>
                                <span class="text-xs ${c.is_active==1 ? 'text-green-600' : 'text-gray-500'} font-bold">${statusText}</span><br>
                                <span class="text-xs">Occupancy: ${c.current_occupancy}/${c.capacity}</span>
                            </div>`;
                        
                        L.circleMarker([c.latitude, c.longitude], {
                            radius: 8,
                            fillColor: color,
                            color: "#fff",
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.9
                        }).addTo(map).bindPopup(popup);
                    }
                });
            }
        });

        // --- LAYER 2: RESIDENTS (Blue Pins) ---
        $.ajax({
            url: 'api/resident/get_households.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                data.forEach(h => {
                    if (h.latitude && h.longitude) {
                        var popup = `
                            <div class="text-center">
                                <strong class="text-sm text-blue-600">${h.household_head_name}</strong><br>
                                <span class="text-xs text-gray-600">${h.zone_purok}</span><br>
                                <span class="text-xs">Members: ${h.member_count}</span>
                            </div>`;

                        L.circleMarker([h.latitude, h.longitude], {
                            radius: 4,
                            fillColor: '#3b82f6', // Blue
                            color: '#fff',
                            weight: 1,
                            opacity: 1,
                            fillOpacity: 0.6
                        }).addTo(map).bindPopup(popup);
                    }
                });
            }
        });

        // --- LAYER 3: EMERGENCY REQUESTS (Red/Yellow Pins) ---
        $.ajax({
            url: 'api/admin/get_all_requests.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                data.forEach(req => {
                    // Only show Pending or In Progress
                    if (req.latitude && req.longitude && req.status !== 'Completed') {
                        var color = req.status === 'Pending' ? '#ef4444' : '#eab308'; // Red or Yellow
                        var pulseClass = req.status === 'Pending' ? 'animate-pulse' : '';
                        
                        var popup = `
                            <div class="text-center">
                                <strong class="text-sm text-red-600">SOS: ${req.request_type}</strong><br>
                                <span class="text-xs font-bold">${req.first_name} ${req.last_name}</span><br>
                                <p class="text-xs italic mt-1">"${req.description}"</p>
                                <span class="text-[10px] text-gray-500">${new Date(req.created_at).toLocaleString()}</span>
                            </div>`;

                        L.circleMarker([req.latitude, req.longitude], {
                            radius: 10,
                            fillColor: color,
                            color: "#fff",
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 1,
                            className: pulseClass
                        }).addTo(map).bindPopup(popup);
                    }
                });
            }
        });

        // --- LAYER 4: AFFECTED FAMILIES (Active Evacuees - Red Pulse) ---
        $.ajax({
            url: 'api/admin/get_affected_map.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                data.forEach(h => {
                    if (h.latitude && h.longitude) {
                        var popup = `
                            <div class="text-center">
                                <strong class="text-red-500">Family Evacuated</strong><br>
                                <span class="text-sm font-bold">${h.household_head_name}</span>
                            </div>`;
                        
                        L.circleMarker([h.latitude, h.longitude], {
                            radius: 6, 
                            fillColor: '#ef4444', // Red
                            color: "#fff", 
                            weight: 2, 
                            opacity: 1, 
                            fillOpacity: 1, 
                            className: 'animate-pulse' // Pulse effect
                        }).addTo(map).bindPopup(popup);
                    }
                });
            }
        });
    }

    // Initialize Map
    initDashboardMap();


    // --- 2. LOAD LIVE STATS ---
    function loadStats() {
        $.ajax({
            url: 'api/resident/get_resident_stats.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#stats-total-households').text(data.total_households);
                $('#stats-total-residents').text(data.total_residents);
                $('#stats-affected-households').text(data.affected_households);
                $('#stats-residents-evacuated').text(data.residents_evacuated);
            }
        });
    }

    // --- 3. LOAD CENTERS TABLE ---
    function loadCentersTable() {
        $.ajax({
            url: 'api/evacuation/get_centers.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var tbody = $('#centers-table-body');
                tbody.empty();

                if (data.length === 0) {
                    tbody.html('<tr><td colspan="3" class="px-4 py-8 text-center text-gray-500 italic">No centers found.</td></tr>');
                    return;
                }
                
                data.forEach(c => {
                    var statusBadge = c.is_active == 1 
                        ? '<span class="text-green-400 font-bold text-xs uppercase tracking-wider">Active</span>' 
                        : '<span class="text-slate-500 font-bold text-xs uppercase tracking-wider">Closed</span>';
                    
                    var occColor = (c.current_occupancy >= c.capacity) ? 'text-red-400' : 'text-slate-300';
                    
                    tbody.append(`
                        <tr class="border-b border-border-dark hover:bg-[#222831] transition-colors">
                            <td class="px-4 py-3 text-white font-medium text-sm">${c.center_name}</td>
                            <td class="px-4 py-3">${statusBadge}</td>
                            <td class="px-4 py-3 ${occColor} text-sm text-right font-mono">${c.current_occupancy} / ${c.capacity}</td>
                        </tr>
                    `);
                });
            }
        });
    }

    // --- 4. LOAD INVENTORY TABLE ---
    function loadInventoryTable() {
        $.ajax({
            url: 'api/relief/get_items.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Handle data wrapper if present
                var items = data.items || data;
                var tbody = $('#inventory-table-body');
                tbody.empty();

                if (items.length === 0) {
                    tbody.html('<tr><td colspan="3" class="px-4 py-8 text-center text-slate-500 italic">No stock.</td></tr>');
                    return;
                }

                items.forEach(item => {
                    var stockClass = item.stock_quantity < 20 ? 'text-red-400 font-bold' : 'text-green-400 font-bold';
                    
                    // Match DB columns: item_name, stock_quantity, unit_of_measure
                    tbody.append(`
                        <tr class="border-b border-border-dark hover:bg-[#222831] transition-colors">
                            <td class="px-4 py-3 text-white font-medium text-sm">${item.item_name}</td>
                            <td class="px-4 py-3 ${stockClass} text-sm text-right font-mono">${item.stock_quantity}</td>
                            <td class="px-4 py-3 text-slate-400 text-xs text-right uppercase">${item.unit_of_measure}</td>
                        </tr>
                    `);
                });
            }
        });
    }

    // --- INITIAL LOAD & AUTO-REFRESH ---
    loadStats();
    loadCentersTable();
    loadInventoryTable();

    // Auto-refresh data every 30 seconds
    setInterval(function() {
        loadStats();
        loadCentersTable();
        loadInventoryTable();
        // Note: We don't auto-refresh the map markers to avoid flickering while dragging
    }, 30000);

});