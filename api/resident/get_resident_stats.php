<?php
// api/resident/get_resident_stats.php

// 1. Silence errors to prevent JSON crashes
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';

$response = [
    "total_households" => 0,
    "total_residents" => 0,
    "affected_households" => 0,
    "residents_evacuated" => 0
];

if ($conn->connect_error) {
    echo json_encode($response);
    exit;
}

// Helper: Count as Active if 'is_deleted' is 0 OR NULL (Empty)
// This fixes the "0 Stats" issue if you haven't updated old records yet.
$active_check = "(is_deleted = 0 OR is_deleted IS NULL)";

try {
    // --- 1. TOTAL HOUSEHOLDS ---
    $sql = "SELECT COUNT(*) AS total FROM households WHERE $active_check";
    $result = $conn->query($sql);
    if ($result) $response["total_households"] = (int)$result->fetch_assoc()["total"];

    // --- 2. TOTAL RESIDENTS ---
    $sql = "SELECT COUNT(*) AS total FROM residents WHERE $active_check";
    $result = $conn->query($sql);
    if ($result) $response["total_residents"] = (int)$result->fetch_assoc()["total"];

    // --- 3. RESIDENTS EVACUATED ---
    // Must be active resident AND currently checked in (no check-out time)
    $sql = "SELECT COUNT(*) AS total 
            FROM evacuees e
            JOIN residents r ON e.resident_id = r.id
            WHERE e.time_checked_out IS NULL 
            AND ($active_check)";
    $result = $conn->query($sql);
    if ($result) $response["residents_evacuated"] = (int)$result->fetch_assoc()["total"];

    // --- 4. AFFECTED HOUSEHOLDS ---
    // Households that are Active AND (Evacuated OR Requesting Help)
    $sql = "SELECT COUNT(DISTINCT h.id) as total 
            FROM households h
            LEFT JOIN evacuees e ON h.id = e.household_id AND e.time_checked_out IS NULL
            LEFT JOIN assistance_requests ar ON h.id = ar.household_id AND ar.status = 'Pending'
            WHERE ($active_check) 
            AND (e.id IS NOT NULL OR ar.id IS NOT NULL)";

    $result = $conn->query($sql);
    if ($result) $response["affected_households"] = (int)$result->fetch_assoc()["total"];

} catch (Exception $e) {
    // If table columns are missing, it might return 0, but won't crash the page
}

echo json_encode($response);
$conn->close();
?>