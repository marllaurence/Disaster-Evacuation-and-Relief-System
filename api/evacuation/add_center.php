<?php
// api/evacuation/add_center.php

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../config/db_connect.php';

$response = array('success' => false, 'message' => 'An unknown error occurred.');

// 1. Get data from the POST request
$center_name = $_POST['center_name'] ?? '';
$address = $_POST['address'] ?? '';
$capacity = $_POST['capacity'] ?? 0;
$is_active = isset($_POST['is_active']) ? 1 : 0; 

// --- NEW: Get Coordinates ---
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;

// Handle empty strings (convert " " to NULL so database accepts it)
if ($latitude === '') $latitude = null;
if ($longitude === '') $longitude = null;

// 2. Validation
if (empty($center_name)) {
    $response['message'] = 'Center Name is required.';
} elseif ($capacity <= 0) {
    $response['message'] = 'Capacity must be a positive number.';
} else {
    
    // 3. Prepare the query (Updated with Lat/Long)
    $stmt = $conn->prepare(
        "INSERT INTO evacuation_centers (center_name, address, capacity, is_active, latitude, longitude) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    // 4. Bind the variables
    // "ssiidd" -> String, String, Int, Int, Double, Double
    $stmt->bind_param("ssiidd", 
        $center_name, 
        $address, 
        $capacity,
        $is_active,
        $latitude,
        $longitude
    );

    // 5. Execute
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Evacuation center added successfully!';
    } else {
        // Check for a duplicate name error
        if ($conn->errno == 1062) { 
            $response['message'] = 'Error: An evacuation center with this name already exists.';
        } else {
            $response['message'] = 'Database error: ' . $stmt->error;
        }
    }

    $stmt->close();
}

$conn->close();

echo json_encode($response);
?>