<?php
// api/resident/submit_request.php

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

include_once '../config/session.php';
include_once '../config/db_connect.php';

$response = array("success" => false, "message" => "Unknown error");

try {
    if (!isset($_SESSION['user_id'])) throw new Exception("Unauthorized.");
    $user_id = $_SESSION['user_id'];

    // 1. GET RESIDENT ID
    $q = $conn->prepare("SELECT resident_id FROM users WHERE id = ?");
    $q->bind_param("i", $user_id);
    $q->execute();
    $res = $q->get_result();
    if ($res->num_rows === 0) throw new Exception("User account not found.");
    
    $user_row = $res->fetch_assoc();
    $resident_id = $user_row['resident_id'];

    if(empty($resident_id)) throw new Exception("No Resident Profile linked.");

    // 2. GET HOUSEHOLD ID (RESTORED THIS PART)
    $hh_q = $conn->query("SELECT household_id FROM residents WHERE id = $resident_id");
    $hh_row = $hh_q->fetch_assoc();
    $household_id = $hh_row['household_id'] ?? 0; // If null, defaults to 0

    // 3. GET INPUTS
    $type = $_POST['request_type'] ?? '';
    $desc = $_POST['description'] ?? '';

    // 4. HANDLE FILE UPLOAD (Keep this, it works!)
    $db_path = null;

    if (isset($_FILES['request_photo']) && $_FILES['request_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['request_photo'];
        $root = dirname(__DIR__, 2);
        $upload_dir = $root . '/uploads/requests/';
        
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_name = 'req_' . $resident_id . '_' . time() . '.' . $ext;
        
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_name)) {
            $db_path = 'uploads/requests/' . $new_name;
        }
    }

    // 5. INSERT INTO DB (Using the REAL household_id now)
    $sql = "INSERT INTO assistance_requests (resident_id, household_id, request_type, description, image_proof, status) VALUES (?, ?, ?, ?, ?, 'Pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $resident_id, $household_id, $type, $desc, $db_path);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Request submitted successfully!';
    } else {
        throw new Exception("DB Error: " . $stmt->error);
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>