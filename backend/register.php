<?php
// CORS and Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

// Show errors (for dev)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 🔒 Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Only POST requests are allowed"
    ]);
    exit;
}

// 🧱 Get and decode JSON input
$data = json_decode(file_get_contents("php://input"));

// 🔍 Validate required fields
if (
    !isset($data->username) || empty(trim($data->username)) ||
    !isset($data->email) || empty(trim($data->email)) ||
    !isset($data->password) || empty(trim($data->password))
) {
    echo json_encode([
        "status" => "error",
        "message" => "All fields are required"
    ]);
    exit;
}

// 🧼 Sanitize & hash
$username = trim($data->username);
$email = trim($data->email);
$password = password_hash(trim($data->password), PASSWORD_BCRYPT);

// 💾 DB connection
$conn = new mysqli("localhost", "root", "", "react_auth");

if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Connection failed: " . $conn->connect_error
    ]);
    exit;
}

// ✅ Use prepared statements to avoid SQL injection
$sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $email, $password);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "User registered"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>