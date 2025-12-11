<?php
// api/resident/debug.php
// Updated to check 'users' table for resident_id
ini_set('display_errors', 1); 
error_reporting(E_ALL);
session_start();

include_once '../config/db_connect.php';

echo "<h1>🔍 Debugging Requests (Fixed Schema)</h1>";

// 1. CHECK SESSION
if (!isset($_SESSION['user_id'])) {
    die("<h3 style='color:red'>❌ Not logged in.</h3>");
}
$user_id = $_SESSION['user_id'];
echo "<p><strong>User ID:</strong> $user_id</p>";

// 2. CHECK USERS TABLE
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user_data = $result->fetch_assoc();

echo "<p><strong>Checking 'users' table...</strong></p>";
if ($user_data['resident_id']) {
    $resident_id = $user_data['resident_id'];
    echo "<p style='color:green'>✅ Found linked Resident ID: <strong>$resident_id</strong></p>";
} else {
    die("<h3 style='color:red'>❌ User found, but 'resident_id' column is NULL.</h3>");
}

// 3. CHECK REQUESTS
$req_res = $conn->query("SELECT * FROM assistance_requests WHERE resident_id = $resident_id");
$count = $req_res->num_rows;

echo "<hr>";
if ($count > 0) {
    echo "<h3 style='color:green'>✅ Found $count Requests!</h3>";
    while($row = $req_res->fetch_assoc()) {
        echo "<li>Request ID: " . $row['id'] . " - " . $row['request_type'] . "</li>";
    }
} else {
    echo "<h3 style='color:orange'>⚠️ 0 Requests found for Resident #$resident_id</h3>";
}
?>