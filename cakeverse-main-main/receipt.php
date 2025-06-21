<?php
require_once 'connect.php';

// Handle feedback form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['feedback_text'], $_POST['promotion'])) {
    $order_id = $_POST['order_id'];
    $feedback = $_POST['feedback_text'];
    $promotion = $_POST['promotion'];

    // Update the orders table with feedback and promotion
    $stmt = $conn->prepare("UPDATE orders SET feedback = ?, promotion = ? WHERE order_id = ?");
    $stmt->bind_param("sss", $feedback, $promotion, $order_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    // Show thank you popup and redirect to home
    echo "<script>alert('THANK YOU FOR COMPLETING THIS ORDER');window.location.href='index.html';</script>";
    exit();
}

$order_id = $_GET['order_id'] ?? '';

$stmt = $conn->prepare("
    SELECT 
        o.order_id,
        o.payment_method,
        c.customer_name,
        c.address,
        c.contact_number,
        d.delivery_date,
        d.delivery_type,
        k.cake_design,
        k.layers,
        k.toppings,
        a.artist_assigned AS artist_assigned
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.customer_id
    LEFT JOIN delivery d ON o.customer_id = d.customer_id
    LEFT JOIN cake k ON o.cake_id = k.cake_id
    LEFT JOIN cake_artists ca ON o.cake_id = ca.cake_id
    LEFT JOIN artist a ON ca.artist_id = a.artist_id
    WHERE o.order_id = ?
    ORDER BY d.delivery_date DESC
    LIMIT 1
");
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $order = $result->fetch_assoc();
} else {
    echo "Order not found.";
    exit();
}

$stmt->close();
$conn->close();

$promotions = [
    "New Customer Discount: Get 10% off your next order with code WELCOME10!",
    "Birthday Special: Free mini cupcakes on your birthday month!",
    "Refer a Friend: Both get a 5% discount on your next purchase!"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Receipt — Cakes Oasis</title>
    <style>
        :root {
            --pomp-and-power: #7D71A7;
            --mauve: #CAADFF;
            --lavender-pink: #FFC2E2;
            --carnation-pink: #FFADC7;
            --cornsilk: #FCF6D9;
            --primary-color: var(--carnation-pink);
            --secondary-color: var(--lavender-pink);
            --text-primary: var(--pomp-and-power);
            --text-secondary: #555;
            --background-light: #fff;
            --button-hover: #E095B5;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--lavender-pink);
            margin: 0;
            padding: 20px;
        }

        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: var(--background-light);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        h1, h2 {
            font-family: 'Playfair Display', serif;
            color: var(--text-primary);
        }

        p, label, span {
            color: var(--text-secondary);
        }

        .order-summary p {
            margin: 10px 0;
        }

        .feedback-section, .promotions-section {
            margin-top: 30px;
            background-color: var(--secondary-color);
            padding: 20px;
            border-radius: 12px;
        }

        .feedback-section textarea {
        width: 100%;
        padding: 15px;
        border-radius: 8px;
        border: 2px solid var(--carnation-pink);
        font-size: 1em;
        resize: none; /* 🔧 This stops dragging */
        box-sizing: border-box;
        min-height: 150px;
        background-color: #fff;
        font-family: 'Poppins', sans-serif;
        line-height: 1.5;
    }


        .feedback-section button {
            margin-top: 15px;
            background-color: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 24px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }

        .feedback-section button:hover {
            background-color: var(--button-hover);
        }

        .promo-radio-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 12px;
        }

        .promo-radio {
            background: #fff7fb;
            padding: 10px 16px;
            border-radius: 16px;
            border: 2px solid var(--mauve);
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .promo-radio:hover {
            background: #ffe3f2;
            border-color: var(--primary-color);
        }

        .back-button {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            background-color: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border-radius: 24px;
            font-weight: bold;
            transition: background 0.3s;
        }

        .back-button:hover {
            background-color: var(--button-hover);
        }
    </style>
</head>
<body>
<div class="receipt-container">
    <h1>Order Confirmed!</h1>
    <p>Thank you for your order, <strong><?= htmlspecialchars($order['customer_name']) ?></strong>!</p>
    <p>Your Order ID is: <strong><?= $order['order_id'] ?></strong></p>

    <div class="order-summary">
        <h2>Order Summary</h2>
        <p><strong>Customer Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
        <p><strong>Contact Number:</strong> <?= htmlspecialchars($order['contact_number']) ?></p>
        <p><strong>Delivery Date:</strong> <?= htmlspecialchars($order['delivery_date']) ?></p>
        <p><strong>Delivery Type:</strong> <?= htmlspecialchars($order['delivery_type']) ?></p>
        <p><strong>Cake Design:</strong> <?= htmlspecialchars($order['cake_design']) ?></p>
        <p><strong>Layers:</strong> <?= htmlspecialchars($order['layers']) ?></p>
        <p><strong>Toppings:</strong> <?= htmlspecialchars($order['toppings']) ?></p>
        <p><strong>Artist Assigned:</strong> <?= htmlspecialchars($order['artist_assigned']) ?></p>
        <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
    </div>

        <form id="order-feedback-form" method="POST">
            <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['order_id']) ?>">

            <div class="feedback-section">
                <h2>Your Feedback Matters!</h2>
                <textarea name="feedback_text" placeholder="Tell us about your cake or delivery experience..." required></textarea>
            </div>

            <div class="promotions-section">
                <h2>Exclusive Promotions</h2>
                <label><strong>Choose a promotion:</strong></label>
                <div class="promo-radio-group">
                    <label class="promo-radio">
                        <input type="radio" name="promotion" value="Valentine" checked>
                        <span>💖 Valentine: 2-for-1 Heart Cakes!</span>
                    </label>
                    <label class="promo-radio">
                        <input type="radio" name="promotion" value="Birthday">
                        <span>🎂 Birthday: Free mini cupcakes with any order!</span>
                    </label>
                    <label class="promo-radio">
                        <input type="radio" name="promotion" value="Anniversary">
                        <span>💍 Anniversary: 15% off on all custom cakes!</span>
                    </label>
                    <label class="promo-radio">
                        <input type="radio" name="promotion" value="Matcha Lover">
                        <span>🍵 Matcha Lover: Free matcha latte with every Matcha cake!</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="back-button" id="final-submit-btn">Submit</button>
        </form>
    </div>

</body>
</html>