<?php

require_once "includes/db.php";
require_once "includes/auth.php";

requireLogin();

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT order_id,
            total_amount,
            status,
            shipping_address,
            payment_method,
            created_at
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Orders - ShopSphere</title>

<link rel="stylesheet" href="assets/css/orders.css">

</head>

<body>

<header class="navbar">

    <a href="index.php" class="logo">
        🛍️ Shop<span>Sphere</span>
    </a>

    <nav>
        <a href="index.php">Home</a>
        <a href="products.php">Products</a>
        <a href="orders.php" class="active">My Orders</a>
    </nav>

    <div class="nav-actions">

        <a href="wishlist.php" class="nav-icon">♡</a>

        <a href="cart.php" class="nav-icon">🛒</a>

        <a href="logout.php" class="logout-btn">
            Logout
        </a>

    </div>

</header>


<main class="orders-page">

    <section class="orders-hero">

        <div class="hero-content">

            <span class="eyebrow">
                SHOPSPHERE ACCOUNT
            </span>

            <h1>
                My Orders <span>📦</span>
            </h1>

            <p>
                Keep track of your purchases, delivery status,
                payment information, and order details.
            </p>

        </div>

        <div class="hero-decoration">

            <div class="decoration-circle">
                📦
            </div>

        </div>

    </section>


    <section class="orders-section">

        <div class="section-heading">

            <div>
                <span>ORDER HISTORY</span>
                <h2>Your Recent Orders</h2>
            </div>

            <a href="products.php" class="shop-more">
                Continue Shopping →
            </a>

        </div>


        <?php if ($result->num_rows > 0): ?>

            <div class="orders-list">

                <?php while ($order = $result->fetch_assoc()): ?>

                    <article class="order-card">

                        <div class="order-card-header">

                            <div class="order-id-area">

                                <div class="package-icon">
                                    📦
                                </div>

                                <div>

                                    <span class="label">
                                        ORDER NUMBER
                                    </span>

                                    <h3>
                                        #<?php echo $order["order_id"]; ?>
                                    </h3>

                                </div>

                            </div>


                            <div class="order-status <?php echo htmlspecialchars($order["status"]); ?>">

                                <span class="status-dot"></span>

                                <?php
                                echo ucwords(
                                    str_replace(
                                        "_",
                                        " ",
                                        $order["status"]
                                    )
                                );
                                ?>

                            </div>

                        </div>


                        <div class="order-main">

                            <div class="order-info-grid">

                                <div class="info-item">

                                    <span class="info-label">
                                        ORDER DATE
                                    </span>

                                    <strong>
                                        <?php
                                        echo date(
                                            "M d, Y",
                                            strtotime($order["created_at"])
                                        );
                                        ?>
                                    </strong>

                                </div>


                                <div class="info-item">

                                    <span class="info-label">
                                        PAYMENT
                                    </span>

                                    <strong>
                                        <?php
                                        echo $order["payment_method"] === "cash_on_delivery"
                                            ? "Cash on Delivery"
                                            : "Online Payment";
                                        ?>
                                    </strong>

                                </div>


                                <div class="info-item">

                                    <span class="info-label">
                                        TOTAL AMOUNT
                                    </span>

                                    <strong class="total">
                                        Rs.
                                        <?php
                                        echo number_format(
                                            $order["total_amount"],
                                            2
                                        );
                                        ?>
                                    </strong>

                                </div>

                            </div>


                            <div class="address-area">

                                <div class="address-title">

                                    <span class="location-icon">
                                        📍
                                    </span>

                                    <div>
                                        <span class="info-label">
                                            DELIVERY ADDRESS
                                        </span>

                                        <strong>
                                            Shipping Destination
                                        </strong>
                                    </div>

                                </div>

                                <p>
                                    <?php
                                    echo nl2br(
                                        htmlspecialchars(
                                            $order["shipping_address"]
                                        )
                                    );
                                    ?>
                                </p>

                            </div>

                        </div>


                        <div class="order-card-footer">

                            <div class="delivery-note">

                                <span>🚚</span>

                                <p>
                                    Track your order and view complete details
                                </p>

                            </div>

                            <a
                                href="order-details.php?id=<?php echo $order["order_id"]; ?>"
                                class="order-view"
                            >
                                View Order Details
                                <span>→</span>
                            </a>

                        </div>

                    </article>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div class="empty-orders">

                <div class="empty-icon">
                    📦
                </div>

                <span class="eyebrow">
                    SHOPSPHERE ORDERS
                </span>

                <h2>
                    No orders yet
                </h2>

                <p>
                    Looks like you haven't placed an order yet.
                    Discover something you love and start shopping.
                </p>

                <a href="products.php" class="start-shopping">
                    Start Shopping
                    <span>→</span>
                </a>

            </div>

        <?php endif; ?>

    </section>

</main>


<section class="trust-section">

    <div class="trust-item">

        <div class="trust-icon">
            🔒
        </div>

        <div>
            <strong>Secure Orders</strong>
            <span>Your order information is protected</span>
        </div>

    </div>


    <div class="trust-item">

        <div class="trust-icon">
            🚚
        </div>

        <div>
            <strong>Order Tracking</strong>
            <span>Stay updated throughout delivery</span>
        </div>

    </div>


    <div class="trust-item">

        <div class="trust-icon">
            💬
        </div>

        <div>
            <strong>Customer Support</strong>
            <span>We're here when you need us</span>
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
            <a href="wishlist.php">Wishlist</a>
            <a href="cart.php">Cart</a>

        </div>

    </div>


    <div class="copyright">
        © 2026 ShopSphere. All rights reserved.
    </div>

</footer>

</body>
</html>