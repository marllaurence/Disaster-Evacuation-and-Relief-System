<?php
// api/evacuation/get_centers.php
include_once '../config/db_connect.php';
header('Content-Type: application/json');

// Added 'latitude' and 'longitude' to the selection
$sql = "SELECT 
            ec.id, 
            ec.center_name, 
            ec.address, 
            ec.capacity, 
            ec.is_active, 
            ec.latitude, 
            ec.longitude,
            (SELECT COUNT(*) FROM evacuees e WHERE e.center_id = ec.id AND e.time_checked_out IS NULL) as current_occupancy
        FROM evacuation_centers ec
        ORDER BY ec.is_active DESC, ec.center_name ASC";

$result = $conn->query($sql);
$centers = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $centers[] = $row;
    }
}

echo json_encode($centers);
$conn->close();
?>