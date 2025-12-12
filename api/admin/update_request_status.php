<?php
// api/admin/update_request_status.php

header('Content-Type: application/json');
include_once '../config/db_connect.php';

// 1. Get Input
$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;
$reason = $_POST['reason'] ?? null; // Get the rejection reason

if (!$id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing ID or Status']);
    exit;
}

// 2. Prepare Query
// We use dynamic SQL to only update rejection_reason if it's provided
if ($status === 'Rejected' && !empty($reason)) {
    $sql = "UPDATE assistance_requests SET status = ?, rejection_reason = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $status, $reason, $id);
} else {
    $sql = "UPDATE assistance_requests SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $id);
}

// 3. Execute
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$conn->close();
?>