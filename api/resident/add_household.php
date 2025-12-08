<?php
// api/resident/add_household.php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');

// 1. Check Admin Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// 2. Get Data
$head_name = trim($_POST['household_head_name'] ?? '');
$zone = $_POST['zone_purok'] ?? '';
$address_notes = $_POST['address_notes'] ?? '';

// Coordinates
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;
$birthdate = $_POST['birthdate'] ?? null;
$gender = $_POST['gender'] ?? 'Not Specified';

// Sanitize Inputs
if ($latitude === '') $latitude = null;
if ($longitude === '') $longitude = null;
if (empty($birthdate)) $birthdate = null;

// Validation
if (empty($head_name)) {
    echo json_encode(['success' => false, 'message' => 'Household Head Name is required.']);
    exit;
}

// 3. Start Transaction
$conn->begin_transaction();

try {
    // A. Create Household
    // FIX: Removed 'member_count' from this query
    $stmt1 = $conn->prepare("INSERT INTO households (household_head_name, zone_purok, address_notes, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
    $stmt1->bind_param("sssdd", $head_name, $zone, $address_notes, $latitude, $longitude);
    
    if (!$stmt1->execute()) {
        throw new Exception("Error creating household: " . $stmt1->error);
    }
    
    $new_household_id = $conn->insert_id;
    $stmt1->close();

    // B. Create Resident (Head of Family)
    // "Smart" Name Splitting
    $name_parts = explode(' ', $head_name, 2);
    $first_name = $name_parts[0];
    $last_name = $name_parts[1] ?? '(No Last Name)';

    // Insert into Residents table
    $stmt2 = $conn->prepare("INSERT INTO residents (household_id, first_name, last_name, birthdate, gender) VALUES (?, ?, ?, ?, ?)");
    $stmt2->bind_param("issss", $new_household_id, $first_name, $last_name, $birthdate, $gender);
    
    if (!$stmt2->execute()) {
        throw new Exception("Error creating resident: " . $stmt2->error);
    }
    $stmt2->close();

    // C. Commit
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Household and Resident created successfully!']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>