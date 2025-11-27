<?php
// api/admin/update_request_status.php
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$request_id = $_POST['id'] ?? 0;
$new_status = $_POST['status'] ?? '';

if (empty($request_id) || empty($new_status)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$stmt = $conn->prepare("UPDATE assistance_requests SET status = ? WHERE id = ?");
$stmt->bind_param("si", $new_status, $request_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
?>