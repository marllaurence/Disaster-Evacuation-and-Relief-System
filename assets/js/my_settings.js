$(document).ready(function() {
    
    // 1. Initialize Map
    const defaultLat = 6.9567; 
    const defaultLng = 126.2174;
    let map = L.map('settings-map').setView([defaultLat, defaultLng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    let marker;

    function setMarker(lat, lng) {
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng]).addTo(map);
        $('#latitude').val(lat);
        $('#longitude').val(lng);
        map.setView([lat, lng], 13);
    }

    map.on('click', function(e) {
        setMarker(e.latlng.lat.toFixed(8), e.latlng.lng.toFixed(8));
    });

    // 2. Load Data
    // ... inside $(document).ready ...

    // 2. Load Data
    $.ajax({
        url: 'api/resident/get_profile_data.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // If data exists, fill it. If null (new user), leave blank.
                if (response.data) {
                    var d = response.data;
                    $('#first_name').val(d.first_name || '');
                    $('#last_name').val(d.last_name || '');
                    $('#birthdate').val(d.birthdate || '');
                    $('#gender').val(d.gender || 'Male');
                    $('#zone_purok').val(d.zone_purok || '');
                    $('#address').val(d.address || '');

                    if (d.latitude && d.longitude) {
                        setMarker(d.latitude, d.longitude);
                    }
                }
            } else {
                // The PHP file ran, but returned success: false (e.g., Not Logged In)
                alert("Error: " + response.message);
            }
        },
        error: function(xhr, status, error) {
            // This runs if the file is NOT FOUND (404) or CRASHED (500)
            console.error("AJAX Error:", error);
            console.log("Server Response:", xhr.responseText);
            
            // This alert will now show you the raw error (like "404 Not Found" or HTML error text)
            alert("Connection Failed. \nStatus: " + status + "\nError: " + error + "\n\nCheck Console (F12) for details.");
        }
    });

    // ... rest of code ...

    // 3. Save Data
    $('#settings-form').on('submit', function(e) {
        e.preventDefault();
        $('#save-btn').text('Saving...').prop('disabled', true);

        $.ajax({
            url: 'api/resident/update_profile.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                alert(res.message);
                if(res.success) location.reload();
            },
            complete: function() {
                $('#save-btn').text('Save Changes').prop('disabled', false);
            }
        });
    });
});