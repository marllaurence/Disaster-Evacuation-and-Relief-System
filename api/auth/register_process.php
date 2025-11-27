<?php
// api/auth/register_process.php

// 1. DISABLE ERROR PRINTING
ini_set('display_errors', 0); 
error_reporting(E_ALL); 

// 2. CORRECT PATHS (One dot, not two)
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');

$response = array('success' => false, 'message' => 'An unknown error occurred.');

try {
    // 3. GET DATA
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $zone_purok = trim($_POST['zone_purok'] ?? ''); 
    $address = trim($_POST['address'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    $birthdate = $_POST['birthdate'] ?? null;
    $gender = $_POST['gender'] ?? 'Other';
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;

    // Handle empty inputs
    if ($latitude === '' || $latitude === 'undefined') $latitude = null;
    if ($longitude === '' || $longitude === 'undefined') $longitude = null;
    if (empty($birthdate)) $birthdate = null;

    // 4. VALIDATION
    if (empty($first_name) || empty($last_name) || empty($zone_purok) || empty($address) || empty($email) || empty($password)) {
        throw new Exception('All fields are required.');
    }

    if ($password !== $password_confirm) {
        throw new Exception('Passwords do not match.');
    }

    // Check existing email
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    if (!$stmt) throw new Exception("DB Error: " . $conn->error);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        throw new Exception('That email is already registered.');
    }
    $stmt->close();

    // 5. TRANSACTION
    $conn->begin_transaction();

    // A. Create Household
    $full_name = $first_name . ' ' . $last_name;
    $stmt_hh = $conn->prepare("INSERT INTO households (household_head_name, zone_purok, address_notes, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt_hh) throw new Exception("Household Error: " . $conn->error);

    // FIX: "sssdd" (String, String, String, Double, Double)
    $stmt_hh->bind_param("sssdd", $full_name, $zone_purok, $address, $latitude, $longitude);
    
    if (!$stmt_hh->execute()) {
        throw new Exception("Household Execute Error: " . $stmt_hh->error);
    }
    $household_id = $conn->insert_id;
    $stmt_hh->close();

    // B. Create Resident
    $stmt_res = $conn->prepare("INSERT INTO residents (household_id, first_name, last_name, birthdate, gender) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt_res) throw new Exception("Resident Error: " . $conn->error);

    $stmt_res->bind_param("issss", $household_id, $first_name, $last_name, $birthdate, $gender);
    
    if (!$stmt_res->execute()) {
        throw new Exception("Resident Execute Error: " . $stmt_res->error);
    }
    $resident_id = $conn->insert_id;
    $stmt_res->close();

    // C. Create User
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'resident';
    $stmt_user = $conn->prepare("INSERT INTO users (username, password_hash, full_name, role, resident_id) VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt_user) throw new Exception("User Account Error: " . $conn->error);

    $stmt_user->bind_param("ssssi", $email, $password_hash, $full_name, $role, $resident_id);
    
    if (!$stmt_user->execute()) {
        throw new Exception("User Execute Error: " . $stmt_user->error);
    }
    $user_id = $conn->insert_id;
    $stmt_user->close();

    // D. Commit
    $conn->commit();
    
    // Auto Login
    $_SESSION['user_id'] = $user_id;
    $_SESSION['full_name'] = $full_name;
    $_SESSION['role'] = $role;
    $_SESSION['resident_id'] = $resident_id;

    $response['success'] = true;
    $response['message'] = 'Registration successful!';

} catch (Exception $e) {
    try { $conn->rollback(); } catch (Exception $x) {}
    $response['message'] = $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>