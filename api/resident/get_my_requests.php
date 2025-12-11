<?php
// api/resident/get_my_requests.php

// 1. HEADERS & CONFIG
header('Content-Type: application/json');
// Disable error printing to avoid breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

include_once '../config/session.php';
include_once '../config/db_connect.php';

// 2. CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']); 
    exit;
}

$user_id = $_SESSION['user_id'];

// 3. GET RESIDENT ID (THE FIX)
// We query the 'users' table because that is where the link exists
$stmt = $conn->prepare("SELECT resident_id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User account not found']);
    exit;
}

$row = $result->fetch_assoc();
$resident_id = $row['resident_id'];

// If the column is NULL in the database
if (empty($resident_id)) {
    // Return empty list (Success=True, Data=Empty) so page doesn't show "Error"
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

// 4. FETCH REQUESTS
$sql = "SELECT id, request_type, description, status, image_proof, created_at 
        FROM assistance_requests 
        WHERE resident_id = ? 
        ORDER BY created_at DESC";

$stmt_req = $conn->prepare($sql);
$stmt_req->bind_param("i", $resident_id);
$stmt_req->execute();
$result_req = $stmt_req->get_result();

$requests = [];
while ($row = $result_req->fetch_assoc()) {
    $requests[] = $row;
}

// 5. RETURN DATA
echo json_encode(['success' => true, 'data' => $requests]);

$stmt->close();
$stmt_req->close();
$conn->close();
?>