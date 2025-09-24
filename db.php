<?php
// db.php - Database connection
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from your HTML file
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty
$dbname = "stockwave_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Initial check for a default admin user
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $username = 'admin';
        $role = 'Admin';
        $password_hash = hash('sha256', 'admin');
        $stmt = $conn->prepare("INSERT INTO users (username, role, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$username, $role, $password_hash]);
    }

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
    exit();
}
?>