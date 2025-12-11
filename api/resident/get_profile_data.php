<?php
// api/resident/get_profile_data.php

ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../config/session.php';
    require_once __DIR__ . '/../config/db_connect.php';

    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Not logged in");
    }

    $user_id = $_SESSION['user_id'];

    // FIXED: Changed 'r.user_id' to 'r.id'
    $sql = "SELECT 
                r.first_name, r.last_name, r.birthdate, r.gender, 
                h.zone_purok, h.address_notes AS address, h.latitude, h.longitude 
            FROM residents r 
            LEFT JOIN households h ON r.household_id = h.id 
            WHERE r.id = ?"; 

    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("SQL Error: " . $conn->error);

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = $result->fetch_assoc();

    ob_end_clean();
    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

if (isset($conn)) $conn->close();
?>