<?php

require_once "includes/db.php";
require_once "includes/auth.php";

requireLogin();

$user_id = $_SESSION["user_id"];

$sql = "SELECT cart.quantity,
               products.product_id,
               products.name,
               products.price,
               products.stock,
               products.image
        FROM cart
        INNER JOIN products
        ON cart.product_id = products.product_id
        WHERE cart.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$total = 0;
$items = [];

while ($item = $result->fetch_assoc()) {

    $subtotal = $item["price"] * $item["quantity"];

    $total += $subtotal;

    $items[] = $item;
}

if (count($items) === 0) {
    header("Location: cart.php");
    exit;
}

$user_stmt = $conn->prepare(
    "SELECT name, email, phone, address
     FROM users
     WHERE user_id = ?"
);

$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();

$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Checkout | ShopSphere</title>

<link rel="stylesheet" href="assets/css/checkout.css">

</head>

<body>

<nav class="navbar">

    <div class="nav-container">

        <a href="index.php" class="logo">
            <span>Shop</span>Sphere
        </a>

        <div class="nav-links">

            <a href="index.php">Home</a>
            <a href="products.php">Products</a>
            <a href="cart.php">Cart</a>

        </div>

        <div class="nav-right">

            <a href="wishlist.php" class="nav-icon">
                ♡
            </a>

            <a href="cart.php" class="nav-icon">
                🛒
            </a>

            <div class="user-menu">

                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION["name"], 0, 1)); ?>
                </div>

                <div class="user-info">

                    <strong>
                        <?php echo htmlspecialchars($_SESSION["name"]); ?>
                    </strong>

                    <small>
                        Secure Checkout
                    </small>

                </div>

            </div>

        </div>

    </div>

</nav>


<section class="checkout-hero">

    <div class="hero-content">

        <span class="hero-label">
            SHOPSPHERE CHECKOUT
        </span>

        <h1>
            Almost <span>There.</span>
        </h1>

        <p>
            Complete your details and place your order securely.
        </p>

    </div>

    <div class="hero-symbol">
        ✓
    </div>

</section>


<main class="checkout-container">

    <div class="checkout-progress">

        <div class="progress-step completed">
            <span>✓</span>
            <div>
                <strong>Cart</strong>
                <small>Items selected</small>
            </div>
        </div>

        <div class="progress-line"></div>

        <div class="progress-step active">
            <span>2</span>
            <div>
                <strong>Checkout</strong>
                <small>Your information</small>
            </div>
        </div>

        <div class="progress-line"></div>

        <div class="progress-step">
            <span>3</span>
            <div>
                <strong>Complete</strong>
                <small>Order placed</small>
            </div>
        </div>

    </div>


<form method="POST" action="payment.php">
        <div class="checkout-layout">


            <section class="checkout-form">

                <div class="section-heading">

                    <div class="heading-icon">
                        📦
                    </div>

                    <div>
                        <span class="section-label">
                            DELIVERY DETAILS
                        </span>

                        <h2>
                            Shipping Information
                        </h2>
                    </div>

                </div>


                <div class="form-grid">

                    <div class="form-group full">

                        <label>
                            Full Name
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="<?php echo htmlspecialchars($user["name"]); ?>"
                            placeholder="Enter your full name"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Email Address
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="<?php echo htmlspecialchars($user["email"]); ?>"
                            placeholder="you@example.com"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Phone Number
                        </label>

                        <input
                            type="text"
                            name="phone"
                            value="<?php echo htmlspecialchars($user["phone"] ?? ""); ?>"
                            placeholder="98XXXXXXXX"
                            required
                        >

                    </div>


                    <div class="form-group full">

                        <label>
                            Shipping Address
                        </label>

                        <textarea
                            name="address"
                            placeholder="Enter your complete delivery address"
                            required
                        ><?php echo htmlspecialchars($user["address"] ?? ""); ?></textarea>

                    </div>

                </div>


                <div class="payment-section">

                    <div class="section-heading">

                        <div class="heading-icon">
                            💳
                        </div>

                        <div>
                            <span class="section-label">
                                PAYMENT
                            </span>

                            <h2>
                                Choose Payment Method
                            </h2>
                        </div>

                    </div>


                    <div class="payment-options">

                        <label class="payment-option selected">

                            <input
                                type="radio"
                                name="payment_method"
                                value="cash_on_delivery"
                                checked
                            >

                            <div class="payment-icon">
                                💵
                            </div>

                            <div class="payment-content">

                                <strong>
                                    Cash on Delivery
                                </strong>

                                <span>
                                    Pay when your order arrives
                                </span>

                            </div>

                            <div class="payment-check">
                                ✓
                            </div>

                        </label>


                        <label class="payment-option">

                            <input
                                type="radio"
                                name="payment_method"
                                value="online"
                            >

                            <div class="payment-icon">
                                💳
                            </div>

                            <div class="payment-content">

                                <strong>
                                    Online Payment
                                </strong>

                                <span>
                                    Pay securely online
                                </span>

                            </div>

                            <div class="payment-check">
                                ✓
                            </div>

                        </label>

                    </div>

                </div>


                <div class="secure-note">

                    <span>🔒</span>

                    <div>
                        <strong>
                            Secure Checkout
                        </strong>

                        <p>
                            Your personal information is protected and handled securely.
                        </p>
                    </div>

                </div>


                <button
                    type="submit"
                    class="place-order-btn"
                >
                    <span>Place Order</span>
                    <strong>→</strong>
                </button>

            </section>


            <aside class="checkout-summary">

                <div class="summary-top">

                    <div>

                        <span class="section-label">
                            YOUR ORDER
                        </span>

                        <h2>
                            Order Summary
                        </h2>

                    </div>

                    <span class="item-count">
                        <?php echo count($items); ?>
                        <?php echo count($items) == 1 ? "Item" : "Items"; ?>
                    </span>

                </div>


                <div class="summary-items">

                    <?php foreach ($items as $item): ?>

                        <div class="checkout-item">

                            <div class="item-image">

                                <?php if (!empty($item["image"])): ?>

                                    <img
                                        src="assets/images/<?php echo htmlspecialchars($item["image"]); ?>"
                                        alt="<?php echo htmlspecialchars($item["name"]); ?>"
                                    >

                                <?php else: ?>

                                    <span>
                                        🛍️
                                    </span>

                                <?php endif; ?>

                            </div>


                            <div class="item-info">

                                <strong>
                                    <?php echo htmlspecialchars($item["name"]); ?>
                                </strong>

                                <span>
                                    Qty: <?php echo $item["quantity"]; ?>
                                </span>

                            </div>


                            <strong class="item-price">

                                Rs.
                                <?php
                                echo number_format(
                                    $item["price"] * $item["quantity"],
                                    2
                                );
                                ?>

                            </strong>

                        </div>

                    <?php endforeach; ?>

                </div>


                <div class="summary-calculation">

                    <div class="summary-row">

                        <span>
                            Subtotal
                        </span>

                        <strong>
                            Rs. <?php echo number_format($total, 2); ?>
                        </strong>

                    </div>


                    <div class="summary-row">

                        <span>
                            Delivery
                        </span>

                        <strong class="free">
                            FREE
                        </strong>

                    </div>

                </div>


                <div class="summary-total">

                    <div>

                        <span>
                            Total
                        </span>

                        <small>
                            Inclusive of all charges
                        </small>

                    </div>

                    <strong>
                        Rs. <?php echo number_format($total, 2); ?>
                    </strong>

                </div>


                <a href="cart.php" class="back-cart">
                    ← Back to Cart
                </a>

            </aside>

        </div>

    </form>


    <section class="checkout-benefits">

        <div class="benefit">

            <span>🔒</span>

            <div>
                <strong>Secure</strong>
                <small>Protected checkout</small>
            </div>

        </div>


        <div class="benefit">

            <span>🚚</span>

            <div>
                <strong>Fast Delivery</strong>
                <small>Delivered to your door</small>
            </div>

        </div>


        <div class="benefit">

            <span>💵</span>

            <div>
                <strong>Easy Payment</strong>
                <small>Multiple payment options</small>
            </div>

        </div>


        <div class="benefit">

            <span>💬</span>

            <div>
                <strong>Support</strong>
                <small>We're here to help</small>
            </div>

        </div>

    </section>

</main>


<footer>

    <div class="footer-content">

        <div>

            <a href="index.php" class="footer-logo">
                <span>Shop</span>Sphere
            </a>

            <p>
                Your World of Shopping, One Sphere.
            </p>

        </div>


        <div class="footer-links">

            <a href="index.php">
                Home
            </a>

            <a href="products.php">
                Products
            </a>

            <a href="wishlist.php">
                Wishlist
            </a>

            <a href="cart.php">
                Cart
            </a>

        </div>

    </div>


    <div class="footer-bottom">

        © <?php echo date("Y"); ?>
        ShopSphere. All rights reserved.

    </div>

</footer>

<script>

const paymentOptions = document.querySelectorAll(".payment-option");
const paymentRadios = document.querySelectorAll('input[name="payment_method"]');

paymentRadios.forEach(function (radio) {

    radio.addEventListener("change", function () {

        paymentOptions.forEach(function (option) {
            option.classList.remove("selected");
        });

        this.closest(".payment-option").classList.add("selected");

    });

});

</script>

</body>
</html>