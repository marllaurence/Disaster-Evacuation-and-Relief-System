<?php
// api/resident/get_active_centers.php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');

// 1. Allow Residents
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    echo json_encode([]);
    exit;
}

// 2. Get ONLY Active Centers with Coordinates
$sql = "SELECT id, center_name, address, capacity, latitude, longitude 
        FROM evacuation_centers 
        WHERE is_active = 1 AND latitude IS NOT NULL AND longitude IS NOT NULL";

$result = $conn->query($sql);
$centers = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Get current occupancy for popup info
        $count_sql = "SELECT COUNT(*) as count FROM evacuees WHERE center_id = " . $row['id'] . " AND time_checked_out IS NULL";
        $count_res = $conn->query($count_sql);
        $row['occupancy'] = ($count_res) ? $count_res->fetch_assoc()['count'] : 0;
        
        $centers[] = $row;
    }
}

echo json_encode($centers);
$conn->close();
?>