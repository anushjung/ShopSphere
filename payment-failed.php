<?php

require_once "includes/auth.php";

requireLogin();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Payment Failed | ShopSphere</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    background: #f4f6fb;
    color: #172033;
}

.failed-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px;
}

.failed-card {
    width: 100%;
    max-width: 520px;
    background: #ffffff;
    border-radius: 20px;
    padding: 40px;
    text-align: center;
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.12);
}

.logo {
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 30px;
}

.logo span {
    color: #ff6b35;
}

.failed-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 25px;
    border-radius: 50%;
    background: #fff0f0;
    color: #dc3545;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 42px;
    font-weight: bold;
}

h1 {
    margin-bottom: 12px;
}

.message {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 25px;
}

.demo-info {
    background: #f8f9fb;
    border-radius: 12px;
    padding: 18px;
    margin-bottom: 25px;
    text-align: left;
}

.demo-info strong {
    display: block;
    margin-bottom: 8px;
}

.demo-info p {
    color: #6b7280;
    font-size: 14px;
    line-height: 1.6;
}

.retry-btn {
    display: inline-block;
    background: #ff6b35;
    color: white;
    text-decoration: none;
    padding: 14px 25px;
    border-radius: 10px;
    font-weight: 700;
}

.retry-btn:hover {
    background: #e95725;
}

.back-btn {
    display: block;
    margin-top: 18px;
    color: #6b7280;
    text-decoration: none;
}

</style>

</head>

<body>

<div class="failed-container">

    <div class="failed-card">

        <div class="logo">
            <span>Shop</span>Sphere
        </div>

        <div class="failed-icon">
            ×
        </div>

        <h1>
            Payment Failed
        </h1>

        <p class="message">
            We could not process your demo payment.
            No order has been created and no money has been charged.
        </p>

        <div class="demo-info">

            <strong>
                🧪 Demo Payment
            </strong>

            <p>
                For a successful sandbox payment, use:
                <br><br>
                Card Number: <strong>4111 1111 1111 1111</strong>
                <br>
                CVV: <strong>123</strong>
                <br>
                Expiry: <strong>12/30</strong>
            </p>

        </div>

        <a href="checkout.php" class="retry-btn">
            Try Payment Again
        </a>

        <a href="cart.php" class="back-btn">
            ← Back to Cart
        </a>

    </div>

</div>

</body>

</html>