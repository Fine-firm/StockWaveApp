<?php
// api.php - Central API endpoint handler
require_once 'db.php';

// Corrected logic to handle the API endpoint
// This will work regardless of the subfolder structure
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Remove the script name from the URI to get only the path
$path = str_replace($script_name, '', $request_uri);
$path_parts = explode('/', trim($path, '/'));

$endpoint = isset($path_parts[0]) ? $path_parts[0] : '';
$id = isset($path_parts[1]) ? $path_parts[1] : null;
$method = $_SERVER['REQUEST_METHOD'];

// Handle OPTIONS preflight request
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

function handleUsers($conn, $method, $id, $input) {
    switch ($method) {
        case 'GET':
            $stmt = $conn->prepare("SELECT * FROM users");
            $stmt->execute();
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;
        case 'POST':
            $stmt = $conn->prepare("INSERT INTO users (username, role, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$input['username'], $input['role'], $input['password_hash']]);
            echo json_encode(['id' => $conn->lastInsertId()]);
            break;
        case 'PUT':
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$input['role'], $id]);
            echo json_encode(['message' => 'User updated']);
            break;
        case 'DELETE':
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['message' => 'User deleted']);
            break;
    }
}

function handleItems($conn, $method, $id, $input) {
    switch ($method) {
        case 'GET':
            $stmt = $conn->prepare("SELECT * FROM item_master");
            $stmt->execute();
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;
        case 'POST':
            $stmt = $conn->prepare("INSERT INTO item_master (item_code, item_name, barcode, expected) VALUES (?, ?, ?, ?)");
            $stmt->execute([$input['item_code'], $input['item_name'], $input['barcode'], $input['expected']]);
            echo json_encode(['id' => $conn->lastInsertId()]);
            break;
        case 'PUT':
            $stmt = $conn->prepare("UPDATE item_master SET item_name = ?, barcode = ?, expected = ? WHERE item_code = ?");
            $stmt->execute([$input['item_name'], $input['barcode'], $input['expected'], $id]);
            echo json_encode(['message' => 'Item updated']);
            break;
        case 'DELETE':
            $stmt = $conn->prepare("DELETE FROM item_master WHERE item_code = ?");
            $stmt->execute([$id]);
            echo json_encode(['message' => 'Item deleted']);
            break;
    }
}

function handleLocations($conn, $method, $id, $input) {
    switch ($method) {
        case 'GET':
            $stmt = $conn->prepare("SELECT * FROM locations");
            $stmt->execute();
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;
        case 'POST':
            $stmt = $conn->prepare("INSERT INTO locations (name) VALUES (?)");
            $stmt->execute([$input['name']]);
            echo json_encode(['id' => $conn->lastInsertId()]);
            break;
        case 'DELETE':
            $stmt = $conn->prepare("DELETE FROM locations WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['message' => 'Location deleted']);
            break;
    }
}

function handleAudit($conn, $method, $id, $input) {
    switch ($method) {
        case 'GET':
            $stmt = $conn->prepare("SELECT * FROM inventory_audit");
            $stmt->execute();
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;
        case 'POST':
            $stmt = $conn->prepare("INSERT INTO inventory_audit (user_name, location, category, item_code, barcode, item_name, actual_quantity) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$input['user_name'], $input['location'], $input['category'], $input['item_code'], $input['barcode'], $input['item_name'], $input['actual_quantity']]);
            echo json_encode(['id' => $conn->lastInsertId()]);
            break;
        case 'PUT':
            $stmt = $conn->prepare("UPDATE inventory_audit SET actual_quantity = ? WHERE id = ?");
            $stmt->execute([$input['actual_quantity'], $id]);
            echo json_encode(['message' => 'Audit record updated']);
            break;
        case 'DELETE':
            $stmt = $conn->prepare("DELETE FROM inventory_audit WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['message' => 'Audit record deleted']);
            break;
    }
}

switch ($endpoint) {
    case 'users':
        handleUsers($conn, $method, $id, $input);
        break;
    case 'items':
        handleItems($conn, $method, $id, $input);
        break;
    case 'locations':
        handleLocations($conn, $method, $id, $input);
        break;
    case 'audit':
        handleAudit($conn, $method, $id, $input);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}
?>