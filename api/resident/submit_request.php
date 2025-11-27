<?php
// api/resident/submit_request.php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An error occurred.'];

// 1. Check Login
if (!isset($_SESSION['resident_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit;
}

$resident_id = $_SESSION['resident_id'];

// 2. Get Data
$request_type = $_POST['request_type'] ?? '';
$description = $_POST['description'] ?? '';

if (empty($request_type)) {
    echo json_encode(['success' => false, 'message' => 'Please select a request type.']);
    exit;
}

// 3. Get Household ID
$stmt = $conn->prepare("SELECT household_id FROM residents WHERE id = ?");
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$household_id = $res['household_id'] ?? 0;
$stmt->close();

if ($household_id == 0) {
    echo json_encode(['success' => false, 'message' => 'No household profile found.']);
    exit;
}

// 4. Insert Request
$sql = "INSERT INTO assistance_requests (resident_id, household_id, request_type, description, status) VALUES (?, ?, ?, ?, 'Pending')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $resident_id, $household_id, $request_type, $description);

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Request submitted successfully! Help is on the way.';
} else {
    $response['message'] = 'Database error: ' . $conn->error;
}

$stmt->close();
$conn->close();
echo json_encode($response);
?>