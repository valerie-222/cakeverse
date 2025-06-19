<?php
session_start();

$order_details = $_SESSION['order_details'] ?? null;
unset($_SESSION['order_details']); // Clear session data after retrieval

if (!$order_details) {
    header('Location: index.html'); // Redirect if no order details
    exit();
}

$order_id = $order_details['order_id'];
$customer_name = $order_details['customer_name'];
$address = $order_details['address'];
$contact_number = $order_details['contact_number'];
$delivery_date = $order_details['delivery_date'];
$delivery_type = $order_details['delivery_type'];
$cake_design_name = $order_details['cake_design_name'];
$cake_design = $order_details['cake_design'];
$layers = $order_details['layers'];
$toppings = $order_details['toppings'];
$artist_assigned = $order_details['artist_assigned'];
$payment_method = $order_details['payment_method'];

$promotions = [
    "New Customer Discount: Get 10% off your next order with code WELCOME10!",
    "Birthday Special: Free mini cupcakes on your birthday month!",
    "Refer a Friend: Both get a 5% discount on their next purchase!"
];
// You can dynamically choose a promotion or display all, for now it displays all
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - Cakes Oasis</title>
    <link rel="stylesheet" href="order-form.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Styles specific to the receipt page */
        .receipt-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .receipt-header {
            color: var(--text-primary);
            font-family: 'Playfair Display', serif;
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        .order-summary {
            text-align: left;
            margin-top: 30px;
            border-top: 1px solid var(--lavender-pink);
            padding-top: 20px;
        }
        .order-summary p {
            margin-bottom: 10px;
            font-size: 1.1em;
            color: var(--text-secondary);
        }
        .order-summary strong {
            color: var(--text-primary);
        }
        .feedback-section, .promotions-section {
            background-color: var(--lavender-pink);
            padding: 30px;
            margin-top: 40px;
            border-radius: 10px;
            text-align: left;
        }
        .feedback-section h2, .promotions-section h2 {
            font-family: 'Playfair Display', serif;
            color: var(--text-primary);
            margin-bottom: 20px;
            text-align: center;
        }
        .feedback-section textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-height: 120px;
            font-family: 'Poppins', sans-serif;
            font-size: 1em;
            box-sizing: border-box;
        }
        .feedback-section button {
            display: block;
            margin: 20px auto 0;
            background-color: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .feedback-section button:hover {
            background-color: var(--button-hover);
        }
        .promotions-section ul {
            list-style: none;
            padding: 0;
        }
        .promotions-section ul li {
            background-color: var(--background-light);
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            font-size: 1.05em;
            color: var(--text-secondary);
        }
        .customize-button {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        .customize-button:hover {
            background-color: var(--button-hover);
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <h1 class="receipt-header">Order Confirmed!</h1>
        <p>Thank you for your order, <strong><?php echo $customer_name; ?></strong>!</p>
        <p>Your Order ID is: <strong><?php echo $order_id; ?></strong></p>

        <div class="order-summary">
            <h2>Order Summary</h2>
            <p><strong>Customer Name:</strong> <?php echo $customer_name; ?></p>
            <p><strong>Delivery Address:</strong> <?php echo $address; ?></p>
            <p><strong>Contact Number:</strong> <?php echo $contact_number; ?></p>
            <p><strong>Required Delivery Date:</strong> <?php echo $delivery_date; ?></p>
            <p><strong>Delivery Type:</strong> <?php echo $delivery_type; ?></p>
            <p><strong>Cake Design Name:</strong> <?php echo $cake_design_name; ?></p>
            <p><strong>Selected Cake Design:</strong> <?php echo $cake_design; ?></p>
            <p><strong>Layers:</strong> <?php echo $layers; ?></p>
            <p><strong>Toppings:</strong> <?php echo $toppings; ?></p>
            <p><strong>Artist Assigned:</strong> <?php echo $artist_assigned; ?></p>
            <p><strong>Payment Method:</strong> <?php echo $payment_method; ?></p>
        </div>

        <div class="feedback-section">
            <h2>Your Feedback Matters!</h2>
            <p>We'd love to hear about your experience. Please leave us a review:</p>
            <form action="process_feedback.php" method="POST">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                <textarea name="feedback_text" placeholder="Share your thoughts about your Cakes Oasis experience..." required></textarea>
                <button type="submit">Submit Feedback</button>
            </form>
        </div>

        <div class="promotions-section">
            <h2>Exclusive Promotions for You!</h2>
            <ul>
                <?php foreach ($promotions as $promo) : ?>
                    <li><?php echo $promo; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <p style="margin-top: 30px;"><a href="index.html" class="customize-button">Back to Home</a></p>
    </div>
</body>
</html>
