<?php
// api/resident/get_my_household.php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['resident_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$current_resident_id = $_SESSION['resident_id'];

// 1. Find household ID
$stmt = $conn->prepare("SELECT household_id FROM residents WHERE id = ?");
$stmt->bind_param("i", $current_resident_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$household_id = $row['household_id'] ?? null;
$stmt->close();

if (!$household_id) {
    echo json_encode(['success' => false, 'message' => 'No household assigned.']);
    exit;
}

// 2. Get Household Details (ADDED latitude, longitude)
$hh_sql = "SELECT household_head_name, address_notes, zone_purok, latitude, longitude FROM households WHERE id = ?";
$stmt_hh = $conn->prepare($hh_sql);
$stmt_hh->bind_param("i", $household_id);
$stmt_hh->execute();
$hh_data = $stmt_hh->get_result()->fetch_assoc();
$stmt_hh->close();

// 3. Get Family Members
$mem_sql = "SELECT id, first_name, last_name, birthdate, gender, is_pwd, is_senior, remarks FROM residents WHERE household_id = ?";
$stmt_mem = $conn->prepare($mem_sql);
$stmt_mem->bind_param("i", $household_id);
$stmt_mem->execute();
$mem_result = $stmt_mem->get_result();

$members = array();
while($m = $mem_result->fetch_assoc()) {
    $members[] = $m;
}
$stmt_mem->close();

echo json_encode([
    'success' => true,
    'household' => $hh_data,
    'members' => $members
]);

$conn->close();
?>