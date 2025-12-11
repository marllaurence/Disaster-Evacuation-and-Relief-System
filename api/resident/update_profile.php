<?php
// api/resident/update_profile.php

// 1. Prevent HTML errors from breaking the JSON response
ob_start();

include_once '../config/session.php';
include_once '../config/db_connect.php';

// 2. Hide errors from display, handle them manually
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Unauthorized: Please login again.");
    }

    $user_id = $_SESSION['user_id'];
    
    // Safety Check: Ensure resident_id exists
    if (!isset($_SESSION['resident_id'])) {
        // Try to recover it
        $stmt = $conn->prepare("SELECT id FROM residents WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $_SESSION['resident_id'] = $row['id'];
        } else {
            throw new Exception("Resident profile not found.");
        }
    }
    $resident_id = $_SESSION['resident_id'];

    // Get Data
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    // New Address Data
    $zone = trim($_POST['zone_purok'] ?? '');
    $address = trim($_POST['address_notes'] ?? '');
    $lat = $_POST['latitude'] ?? null;
    $lng = $_POST['longitude'] ?? null;
    if ($lat === '') $lat = null;
    if ($lng === '') $lng = null;

    if (empty($first_name) || empty($last_name) || empty($email)) {
        throw new Exception("Name and Email are required.");
    }

    // 1. Update Resident Name
    $stmt = $conn->prepare("UPDATE residents SET first_name = ?, last_name = ? WHERE id = ?");
    $stmt->bind_param("ssi", $first_name, $last_name, $resident_id);
    if (!$stmt->execute()) throw new Exception("Failed to update name.");
    $stmt->close();

    // 2. Update User Email
    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
    $stmt->bind_param("si", $email, $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Email already taken.");
    }
    $stmt->close();

    // 3. Update Household Address & Map
    // CRITICAL FIX: Check if query succeeded before fetching
    $hh_query = $conn->query("SELECT household_id FROM residents WHERE id = $resident_id");
    if (!$hh_query) throw new Exception("Database Error: " . $conn->error);
    
    $hh_row = $hh_query->fetch_assoc();
    $hh_id = $hh_row['household_id'] ?? null;

    if ($hh_id) {
        $stmt = $conn->prepare("UPDATE households SET zone_purok=?, address_notes=?, latitude=?, longitude=? WHERE id=?");
        $stmt->bind_param("ssddi", $zone, $address, $lat, $lng, $hh_id);
        $stmt->execute();
        $stmt->close();
    }

    // 4. Update Password (Optional)
    if (!empty($password)) {
        if ($password !== $confirm) {
            throw new Exception("Passwords do not match.");
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->bind_param("si", $hash, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    $_SESSION['full_name'] = $first_name . ' ' . $last_name;

    // Success: Clear buffer and send JSON
    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);

} catch (Exception $e) {
    // Error: Clear buffer and send JSON error
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>