<?php
// api/admin/get_all_requests.php

header('Content-Type: application/json');
include_once '../config/db_connect.php';

// We join 'assistance_requests' with 'residents' and 'households'
// to get the Name and Location of the person asking for help.
$sql = "SELECT 
            ar.id, 
            ar.request_type, 
            ar.description, 
            ar.status, 
            ar.created_at, 
            ar.image_proof, 
            res.first_name, 
            res.last_name, 
            h.address_notes AS zone_purok, 
            h.latitude, 
            h.longitude
        FROM assistance_requests ar
        JOIN residents res ON ar.resident_id = res.id
        LEFT JOIN households h ON ar.household_id = h.id
        ORDER BY ar.created_at DESC";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$conn->close();
?>