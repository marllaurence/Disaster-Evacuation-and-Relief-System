<?php
// api/admin/get_all_requests.php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');

// Security: Admin Only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Query: Get Requests + Resident Name + Household Location
$sql = "SELECT 
            ar.id,
            ar.request_type,
            ar.description,
            ar.status,
            ar.created_at,
            r.first_name,
            r.last_name,
            h.household_head_name,
            h.zone_purok,
            h.latitude,
            h.longitude
        FROM assistance_requests ar
        JOIN residents r ON ar.resident_id = r.id
        JOIN households h ON ar.household_id = h.id
        ORDER BY 
            CASE WHEN ar.status = 'Pending' THEN 1 ELSE 2 END, -- Show Pending first
            ar.created_at DESC";

$result = $conn->query($sql);

$requests = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}

echo json_encode($requests);
$conn->close();
?>