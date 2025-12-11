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

// Helper: Count as Active if 'is_deleted' is 0 OR NULL
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
    $sql = "SELECT COUNT(*) AS total 
            FROM evacuees e
            JOIN residents r ON e.resident_id = r.id
            WHERE e.time_checked_out IS NULL 
            AND ($active_check)";
    $result = $conn->query($sql);
    if ($result) $response["residents_evacuated"] = (int)$result->fetch_assoc()["total"];

    // --- 4. AFFECTED HOUSEHOLDS (FIXED) ---
    // We now count the DISTINCT Household ID by looking at the Residents.
    // A Household is affected if any member is:
    // A) Currently in 'evacuees' (Checked In)
    // B) OR has a request that is 'Pending' or 'In Progress'
    
    $sql = "SELECT COUNT(DISTINCT r.household_id) as total 
            FROM residents r
            LEFT JOIN evacuees e ON r.id = e.resident_id AND e.time_checked_out IS NULL
            LEFT JOIN assistance_requests ar ON r.id = ar.resident_id AND ar.status IN ('Pending', 'In Progress')
            WHERE ($active_check) 
            AND (e.id IS NOT NULL OR ar.id IS NOT NULL)";

    $result = $conn->query($sql);
    if ($result) $response["affected_households"] = (int)$result->fetch_assoc()["total"];

} catch (Exception $e) {
    // Fail silently with 0s
}

echo json_encode($response);
$conn->close();
?>