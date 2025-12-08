<?php
// api/admin/get_affected_map.php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([]);
    exit;
}

// FETCH: Coordinates of households that have ACTIVE EVACUEES
$sql = "SELECT DISTINCT 
            h.household_head_name, 
            h.latitude, 
            h.longitude,
            'Evacuated' as status
        FROM evacuees e
        JOIN residents r ON e.resident_id = r.id
        JOIN households h ON r.household_id = h.id
        WHERE e.time_checked_out IS NULL  -- Only currently evacuated
        AND h.latitude IS NOT NULL        -- Only if they have a map pin
        AND h.longitude IS NOT NULL";

$result = $conn->query($sql);
$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);
$conn->close();
?>