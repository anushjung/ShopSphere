<?php

require_once "includes/db.php";
require_once "includes/auth.php";

requireLogin();

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: orders.php");
    exit;
}

$order_id = (int) $_GET["id"];
$user_id = $_SESSION["user_id"];

$order_stmt = $conn->prepare(
    "SELECT order_id,
            total_amount,
            status,
            shipping_address,
            payment_method,
            created_at
     FROM orders
     WHERE order_id = ? AND user_id = ?"
);

$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();

$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    header("Location: orders.php");
    exit;
}

$order = $order_result->fetch_assoc();

$item_stmt = $conn->prepare(
    "SELECT order_items.quantity,
            order_items.price,
            products.name,
            products.image
     FROM order_items
     INNER JOIN products
     ON order_items.product_id = products.product_id
     WHERE order_items.order_id = ?"
);

$item_stmt->bind_param("i", $order_id);
$item_stmt->execute();

$items_result = $item_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Order Details - ShopSphere</title>

<link rel="stylesheet" href="assets/css/order-details.css">

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


<main class="order-details-page">

    <section class="details-hero">

        <div>

            <span class="eyebrow">
                SHOPSPHERE ORDER
            </span>

            <h1>
                Order #<?php echo $order["order_id"]; ?>
            </h1>

            <p>
                Placed on
                <?php echo date("M d, Y", strtotime($order["created_at"])); ?>
            </p>

        </div>

        <div class="hero-status <?php echo htmlspecialchars($order["status"]); ?>">

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

    </section>


    <section class="tracking-card">

        <div class="tracking-heading">

            <div>

                <span>DELIVERY TRACKING</span>

                <h2>
                    Order Progress
                </h2>

            </div>

            <div class="tracking-truck">
                🚚
            </div>

        </div>


        <div class="order-progress">

            <div class="progress-line"></div>

            <div class="<?php echo in_array($order["status"], ["pending", "confirmed", "processing", "shipped", "out_for_delivery", "delivered"]) ? "active" : ""; ?>">

                <span>✓</span>

                <p>Order Placed</p>

            </div>


            <div class="<?php echo in_array($order["status"], ["confirmed", "processing", "shipped", "out_for_delivery", "delivered"]) ? "active" : ""; ?>">

                <span>2</span>

                <p>Processing</p>

            </div>


            <div class="<?php echo in_array($order["status"], ["shipped", "out_for_delivery", "delivered"]) ? "active" : ""; ?>">

                <span>3</span>

                <p>Shipped</p>

            </div>


            <div class="<?php echo in_array($order["status"], ["out_for_delivery", "delivered"]) ? "active" : ""; ?>">

                <span>4</span>

                <p>Out for Delivery</p>

            </div>


            <div class="<?php echo $order["status"] === "delivered" ? "active" : ""; ?>">

                <span>5</span>

                <p>Delivered</p>

            </div>

        </div>

    </section>


    <div class="order-details-layout">


        <div class="left-column">

            <section class="order-products">

                <div class="section-title">

                    <div>

                        <span>ORDER CONTENTS</span>

                        <h2>Ordered Products</h2>

                    </div>

                    <div class="product-count">
                        <?php echo $items_result->num_rows; ?>
                        <?php echo $items_result->num_rows === 1 ? "Item" : "Items"; ?>
                    </div>

                </div>


                <div class="products-list">

                    <?php while ($item = $items_result->fetch_assoc()): ?>

                        <div class="order-product">


                            <div class="order-product-image">

                                <?php if (!empty($item["image"])): ?>

                                    <img
                                        src="assets/images/<?php echo htmlspecialchars($item["image"]); ?>"
                                        alt="<?php echo htmlspecialchars($item["name"]); ?>"
                                    >

                                <?php else: ?>

                                    <span>🛍️</span>

                                <?php endif; ?>

                            </div>


                            <div class="order-product-info">

                                <span class="product-label">
                                    SHOPSPHERE PRODUCT
                                </span>

                                <h3>
                                    <?php echo htmlspecialchars($item["name"]); ?>
                                </h3>

                                <p>
                                    Quantity:
                                    <strong>
                                        <?php echo $item["quantity"]; ?>
                                    </strong>
                                </p>

                            </div>


                            <div class="order-product-price">

                                <span>
                                    Rs.
                                    <?php echo number_format($item["price"], 2); ?>
                                    ×
                                    <?php echo $item["quantity"]; ?>
                                </span>

                                <strong>
                                    Rs.
                                    <?php
                                    echo number_format(
                                        $item["price"] * $item["quantity"],
                                        2
                                    );
                                    ?>
                                </strong>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>

            </section>


            <section class="order-address-card">

                <div class="address-heading">

                    <div class="address-icon">
                        📍
                    </div>

                    <div>

                        <span>SHIPPING INFORMATION</span>

                        <h2>
                            Delivery Address
                        </h2>

                    </div>

                </div>


                <div class="address-content">

                    <?php
                    echo nl2br(
                        htmlspecialchars(
                            $order["shipping_address"]
                        )
                    );
                    ?>

                </div>

            </section>

        </div>


        <aside class="right-column">


            <section class="order-summary-card">

                <div class="summary-heading">

                    <span>PAYMENT SUMMARY</span>

                    <h2>
                        Order Summary
                    </h2>

                </div>


                <div class="summary-row">

                    <span>Subtotal</span>

                    <strong>
                        Rs.
                        <?php
                        echo number_format(
                            $order["total_amount"],
                            2
                        );
                        ?>
                    </strong>

                </div>


                <div class="summary-row">

                    <span>Delivery</span>

                    <strong class="free">
                        Free
                    </strong>

                </div>


                <div class="summary-divider"></div>


                <div class="summary-total">

                    <span>Total</span>

                    <strong>
                        Rs.
                        <?php
                        echo number_format(
                            $order["total_amount"],
                            2
                        );
                        ?>
                    </strong>

                </div>


                <div class="payment-method">

                    <span class="payment-icon">
                        <?php
                        echo $order["payment_method"] === "cash_on_delivery"
                            ? "💵"
                            : "💳";
                        ?>
                    </span>

                    <div>

                        <small>
                            PAYMENT METHOD
                        </small>

                        <strong>
                            <?php
                            echo $order["payment_method"] === "cash_on_delivery"
                                ? "Cash on Delivery"
                                : "Online Payment";
                            ?>
                        </strong>

                    </div>

                </div>

            </section>


            <section class="help-card">

                <div class="help-icon">
                    💬
                </div>

                <div>

                    <h3>
                        Need help?
                    </h3>

                    <p>
                        If you have any questions about
                        this order, our support team is here
                        to help.
                    </p>

                </div>

            </section>

        </aside>

    </div>


    <a href="orders.php" class="back-orders">
        ← Back to My Orders
    </a>

</main>


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