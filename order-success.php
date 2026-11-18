<?php

require_once "includes/db.php";
require_once "includes/auth.php";

requireLogin();

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$order_id = (int) $_GET["id"];
$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT order_id,
            total_amount,
            status,
            shipping_address,
            payment_method,
            created_at
     FROM orders
     WHERE order_id = ? AND user_id = ?"
);

$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$order = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Order Confirmed - ShopSphere</title>

<link rel="stylesheet" href="assets/css/order-success.css">

</head>

<body>

<header class="navbar">

    <a href="index.php" class="logo">
        🛍️ Shop<span>Sphere</span>
    </a>

    <nav>
        <a href="index.php">Home</a>
        <a href="products.php">Products</a>
        <a href="orders.php">My Orders</a>
    </nav>

    <div class="nav-actions">
        <a href="wishlist.php" class="nav-icon">♡</a>
        <a href="cart.php" class="nav-icon">🛒</a>

        <a href="logout.php" class="logout-btn">
            Logout
        </a>
    </div>

</header>


<main class="success-page">

    <div class="success-container">

        <div class="success-card">

            <div class="success-circle">

                <div class="checkmark">
                    ✓
                </div>

            </div>

            <p class="eyebrow">
                SHOPSPHERE ORDER
            </p>

            <h1>
                Order Confirmed!
            </h1>

            <p class="success-message">
                Thank you for shopping with ShopSphere.
                Your order has been successfully placed and is now being processed.
            </p>


            <div class="order-number">

                <span>Order Number</span>

                <strong>
                    #<?php echo $order["order_id"]; ?>
                </strong>

            </div>


            <div class="order-info">

                <div class="info-box">

                    <span class="info-icon">📦</span>

                    <div>
                        <small>Status</small>

                        <strong class="status">
                            <?php echo ucfirst($order["status"]); ?>
                        </strong>
                    </div>

                </div>


                <div class="info-box">

                    <span class="info-icon">💳</span>

                    <div>
                        <small>Payment</small>

                        <strong>
                            <?php
                            echo $order["payment_method"] === "cash_on_delivery"
                                ? "Cash on Delivery"
                                : "Online Payment";
                            ?>
                        </strong>
                    </div>

                </div>


                <div class="info-box">

                    <span class="info-icon">💰</span>

                    <div>
                        <small>Total Amount</small>

                        <strong class="amount">
                            Rs. <?php echo number_format($order["total_amount"], 2); ?>
                        </strong>
                    </div>

                </div>


                <div class="info-box">

                    <span class="info-icon">🕒</span>

                    <div>
                        <small>Order Date</small>

                        <strong>
                            <?php
                            echo date("d M Y", strtotime($order["created_at"]));
                            ?>
                        </strong>
                    </div>

                </div>

            </div>


            <div class="delivery-box">

                <div class="delivery-header">

                    <div class="delivery-icon">
                        📍
                    </div>

                    <div>
                        <h3>Delivery Address</h3>
                        <p>Your order will be delivered here</p>
                    </div>

                </div>

                <div class="address-content">
                    <?php echo nl2br(htmlspecialchars($order["shipping_address"])); ?>
                </div>

            </div>


            <div class="next-step">

                <div class="step-icon">
                    🚚
                </div>

                <div>
                    <strong>
                        What happens next?
                    </strong>

                    <p>
                        We'll prepare your order and keep you updated
                        as it moves toward delivery.
                    </p>
                </div>

            </div>


            <div class="success-buttons">

                <a href="orders.php" class="primary-btn">
                    View My Orders
                    <span>→</span>
                </a>

                <a href="products.php" class="secondary-btn">
                    Continue Shopping
                </a>

            </div>


            <a href="index.php" class="home-link">
                ← Back to ShopSphere Home
            </a>

        </div>

    </div>

</main>


<section class="trust-section">

    <div class="trust-item">

        <div>🔒</div>

        <div>
            <strong>Secure Shopping</strong>
            <span>Your information is protected</span>
        </div>

    </div>


    <div class="trust-item">

        <div>🚚</div>

        <div>
            <strong>Reliable Delivery</strong>
            <span>Your order is on its way</span>
        </div>

    </div>


    <div class="trust-item">

        <div>💬</div>

        <div>
            <strong>Customer Support</strong>
            <span>We're here whenever you need us</span>
        </div>

    </div>

</section>


<footer>

    <div class="footer-content">

        <div>

            <div class="footer-logo">
                🛍️ Shop<span>Sphere</span>
            </div>

            <p>
                Your World of Shopping, One Sphere.
            </p>

        </div>


        <div class="footer-links">

            <a href="index.php">Home</a>
            <a href="products.php">Products</a>
            <a href="orders.php">My Orders</a>

        </div>

    </div>


    <div class="copyright">

        © 2026 ShopSphere. All rights reserved.

    </div>

</footer>

</body>
</html>