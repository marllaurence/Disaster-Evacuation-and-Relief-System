<?php
// api/relief/get_distributions.php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([]);
    exit;
}

// Query: Get Distribution Log + Household Location + Item Name
$sql = "SELECT 
            rd.id,
            h.household_head_name,
            h.latitude,
            h.longitude,
            ri.name as item_name,
            rd.quantity,
            rd.distribution_date
        FROM relief_distribution rd
        JOIN households h ON rd.household_id = h.id
        JOIN relief_items ri ON rd.item_id = ri.id
        ORDER BY rd.distribution_date DESC";

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