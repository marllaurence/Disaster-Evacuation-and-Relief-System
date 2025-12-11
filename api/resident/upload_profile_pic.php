<?php
// api/resident/upload_profile_pic.php
ob_start(); // Prevent HTML garbage
include_once '../config/session.php';
include_once '../config/db_connect.php';

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

$response = array('success' => false, 'message' => 'Unknown error.');

try {
    if (!isset($_SESSION['user_id'])) throw new Exception("Please log in again.");

    $user_id = $_SESSION['user_id'];

    if (!isset($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] != 0) {
        throw new Exception("No file received.");
    }

    $file = $_FILES['profile_pic'];
    
    // Absolute paths
    $root_dir = dirname(__DIR__, 2); 
    $upload_dir = $root_dir . '/uploads/';

    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) throw new Exception("Failed to create uploads folder.");
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
    $destination = $upload_dir . $new_filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception("Failed to move file.");
    }

    $db_path = 'uploads/' . $new_filename;

    $stmt = $conn->prepare("UPDATE users SET profile_picture_url = ? WHERE id = ?");
    $stmt->bind_param("si", $db_path, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['profile_picture_url'] = $db_path;
        $response['success'] = true;
        $response['message'] = 'Upload successful!';
        $response['new_path'] = $db_path;
    } else {
        throw new Exception("Database update failed.");
    }
    $stmt->close();

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

ob_end_clean();
echo json_encode($response);
$conn->close();
?>