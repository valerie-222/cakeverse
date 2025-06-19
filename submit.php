<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "cakeverse_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}


$customer_name = $_POST['customerName'];
$address = $_POST['address'];
$design_name = $_POST['designName'];
$layers = isset($_POST['layers']) ? implode(", ", $_POST['layers']) : "";
$toppings = isset($_POST['toppings']) ? implode(", ", $_POST['toppings']) : "";
$artist = $_POST['artist'];
$delivery_date = $_POST['deliveryDate'];
$delivery_type = $_POST['deliveryType'];
$payment_method = $_POST['paymentMethod'];
$feedback = $_POST['feedback'];
$promotions = isset($_POST['promos']) ? implode(", ", $_POST['promos']) : "";

$sql = "INSERT INTO orders (order_id, customer_name, address, design_name, layers, toppings, artist, delivery_date, delivery_type, payment_method, feedback, promotions)
VALUES ('$order_id', '$customer_name', '$address', '$design_name', '$layers', '$toppings', '$artist', '$delivery_date', '$delivery_type', '$payment_method', '$feedback', '$promotions')";

if ($conn->query($sql) === TRUE) {
  // Get the auto-incremented ID
  $last_id = $conn->insert_id;

  // Format it like O0001, O0002, etc.
  $formatted_id = "O" . str_pad($last_id, 4, "0", STR_PAD_LEFT);

  // Update the inserted row with the formatted order_id
  $update_sql = "UPDATE orders SET order_id='$formatted_id' WHERE id=$last_id";
  $conn->query($update_sql);

  // Redirect to thank-you page
  header("Location: thankyou.html");
  exit;
} else {
  echo "Error: " . $conn->error;
}

$conn->close();
?>