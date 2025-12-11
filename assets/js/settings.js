$(document).ready(function() {
    
    // ==========================================
    // 1. PROFILE PICTURE LOGIC
    // ==========================================
    $('#settings-pic-container').on('click', function() {
        $('#settings-file-input').click();
    });

    $('#settings-file-input').on('change', function() {
        if (this.files && this.files[0]) {
            var formData = new FormData();
            formData.append('profile_pic', this.files[0]);

            // Visual feedback (fade out slightly)
            $('#settings-pic-container').css('opacity', '0.5');

            $.ajax({
                url: 'api/resident/upload_profile_pic.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(res) {
                    $('#settings-pic-container').css('opacity', '1');
                    if (res.success) {
                        // Update both main image and sidebar image
                        var newSrc = res.new_path + '?t=' + new Date().getTime();
                        $('#settings-profile-img').attr('src', newSrc);
                        $('#sidebar-profile-img').attr('src', newSrc);
                        alert("Profile picture updated!");
                    } else {
                        alert("Error: " + res.message);
                    }
                },
                error: function(xhr) {
                    $('#settings-pic-container').css('opacity', '1');
                    console.log(xhr.responseText);
                    alert("System Error: Check console");
                }
            });
        }
    });

    // ==========================================
    // 2. MAP LOGIC
    // ==========================================
    const defaultLat = 6.9567; 
    const defaultLng = 126.2174;
    
    // Get existing values or use default
    const currentLat = parseFloat($('#latitude').val()) || defaultLat;
    const currentLng = parseFloat($('#longitude').val()) || defaultLng;

    var map = L.map('settings-map').setView([currentLat, currentLng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    
    var marker;
    // If we have saved coordinates, show the marker immediately
    if ($('#latitude').val() && $('#longitude').val()) {
        marker = L.marker([currentLat, currentLng]).addTo(map);
    }

    // Click map to move/add marker
    map.on('click', function(e) {
        if (marker) marker.setLatLng(e.latlng);
        else marker = L.marker(e.latlng).addTo(map);
        
        $('#latitude').val(e.latlng.lat.toFixed(8));
        $('#longitude').val(e.latlng.lng.toFixed(8));
    });

    // ==========================================
    // 3. FORM SUBMIT LOGIC
    // ==========================================
    $('#update-profile-form').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#save-btn');
        var msg = $('#settings-message');
        
        btn.text('Saving...').prop('disabled', true);
        msg.text('');

        $.ajax({
            url: 'api/resident/update_profile.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    msg.text(res.message).removeClass('text-red-500').addClass('text-green-500');
                    // Optional: Reload page after 1 second to refresh name in sidebar
                    // setTimeout(() => location.reload(), 1000);
                } else {
                    msg.text("Error: " + res.message).removeClass('text-green-500').addClass('text-red-500');
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                msg.text("System Error: Check console").addClass('text-red-500');
            },
            complete: function() {
                btn.text('Save Changes').prop('disabled', false);
            }
        });
    });

});