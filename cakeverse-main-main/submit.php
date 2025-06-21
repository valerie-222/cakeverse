<?php
require_once 'connect.php';
header('Content-Type: application/json');

// Enable error reporting (for development only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sanitize input function
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Collect and sanitize POST data
$order_id = 'O' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
$customer_name = sanitize_input($_POST['customer_name'] ?? '');
$address = sanitize_input($_POST['address'] ?? '');
$phone_number_raw = sanitize_input($_POST['phone_number'] ?? '');
$contact_number = '+63' . $phone_number_raw;
$delivery_date = sanitize_input($_POST['delivery_date'] ?? '');
$delivery_type = sanitize_input($_POST['delivery_type'] ?? '');
$cake_design = sanitize_input($_POST['cake_design'] ?? '');
$payment_method = sanitize_input($_POST['payment_method'] ?? '');
$layers = isset($_POST['layers']) ? array_map('sanitize_input', $_POST['layers']) : [];
$toppings = isset($_POST['toppings']) ? array_map('sanitize_input', $_POST['toppings']) : [];

$layers_text = implode(', ', $layers);
$toppings_text = implode(', ', $toppings);

// STEP 1: Insert or find customer
$stmt = $conn->prepare("SELECT customer_id FROM customers WHERE customer_name = ? AND address = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error preparing customer lookup: ' . $conn->error]);
    exit();
}
$stmt->bind_param("ss", $customer_name, $address);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $customer_id = $result->fetch_assoc()['customer_id'];
} else {
    $customer_id = 'CN' . str_pad(rand(8, 999), 3, '0', STR_PAD_LEFT);
    $stmt = $conn->prepare("INSERT INTO customers (customer_id, customer_name, address, contact_number) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error preparing customer insert: ' . $conn->error]);
        exit();
    }
    $stmt->bind_param("ssss", $customer_id, $customer_name, $address, $contact_number);
    $stmt->execute();
}

// STEP 2: Find or insert cake
$stmt = $conn->prepare("SELECT cake_id FROM cake WHERE cake_design = ? AND layers = ? AND toppings = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error preparing cake lookup: ' . $conn->error]);
    exit();
}
$stmt->bind_param("sss", $cake_design, $layers_text, $toppings_text);
$stmt->execute();
$cake_result = $stmt->get_result();
if ($cake_result && $cake_result->num_rows > 0) {
    $cake_id = $cake_result->fetch_assoc()['cake_id'];
} else {
    // Insert new cake
    $cake_id = 'C' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    $stmt = $conn->prepare("INSERT INTO cake (cake_id, cake_design, layers, toppings) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error preparing cake insert: ' . $conn->error]);
        exit();
    }
    $stmt->bind_param("ssss", $cake_id, $cake_design, $layers_text, $toppings_text);
    $stmt->execute();

    // Randomly assign an artist to this cake
    $artist_result = $conn->query("SELECT artist_id FROM artist ORDER BY RAND() LIMIT 1");
    if ($artist_result && $artist_result->num_rows > 0) {
        $artist_id = $artist_result->fetch_assoc()['artist_id'];
        $stmt = $conn->prepare("INSERT INTO cake_artists (cake_id, artist_id) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("ss", $cake_id, $artist_id);
            $stmt->execute();
        }
    }
}

// STEP 3: Get artist_id from cake_artists table (optional, not inserted into orders)
$artist_id = null;
$stmt = $conn->prepare("SELECT artist_id FROM cake_artists WHERE cake_id = ?");
if ($stmt) {
    $stmt->bind_param("s", $cake_id);
    $stmt->execute();
    $artist_result = $stmt->get_result();
    $artist_id = $artist_result && $artist_result->num_rows > 0 ? $artist_result->fetch_assoc()['artist_id'] : null;
}

// STEP 4: Create delivery record
$delivery_id = 'D' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
$stmt = $conn->prepare("INSERT INTO delivery (delivery_id, customer_id, delivery_date, delivery_type) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error preparing delivery insert: ' . $conn->error]);
    exit();
}
$stmt->bind_param("ssss", $delivery_id, $customer_id, $delivery_date, $delivery_type);
$stmt->execute();

// STEP 5: Insert into orders table
$stmt = $conn->prepare("INSERT INTO orders (order_id, customer_id, cake_id, artist_id, payment_method) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error preparing order insert: ' . $conn->error]);
    exit();
}
$stmt->bind_param("sssss", $order_id, $customer_id, $cake_id, $artist_id, $payment_method);
$success = $stmt->execute();

$stmt->close();
$conn->close();

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Order successfully processed!' : 'Database error: unable to store order.',
    'orderId' => $order_id
]);
exit();