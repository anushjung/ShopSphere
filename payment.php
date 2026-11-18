<?php

require_once "includes/db.php";
require_once "includes/auth.php";

requireLogin();

$user_id = $_SESSION["user_id"];

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$address = trim($_POST["address"] ?? "");
$payment_method = $_POST["payment_method"] ?? "cash_on_delivery";

if ($name === "" || $email === "" || $phone === "" || $address === "") {
    header("Location: checkout.php");
    exit;
}

if ($payment_method === "cash_on_delivery") {
    $_POST["name"] = $name;
    $_POST["email"] = $email;
    $_POST["phone"] = $phone;
    $_POST["address"] = $address;
    $_POST["payment_method"] = "cash_on_delivery";

    require "place-order.php";
    exit;
}

if ($payment_method !== "online") {
    header("Location: checkout.php");
    exit;
}

$sql = "SELECT cart.quantity,
               products.product_id,
               products.name,
               products.price,
               products.stock
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

    if ($item["quantity"] > $item["stock"]) {
        echo "One or more products do not have enough stock.";
        exit;
    }

    $total += $item["price"] * $item["quantity"];

    $items[] = $item;
}

if (count($items) === 0) {
    header("Location: cart.php");
    exit;
}

$_SESSION["payment_data"] = [
    "name" => $name,
    "email" => $email,
    "phone" => $phone,
    "address" => $address,
    "payment_method" => "online",
    "total" => $total
];

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Demo Payment | ShopSphere</title>

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

.payment-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px;
}

.payment-card {
    width: 100%;
    max-width: 520px;
    background: white;
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 15px 45px rgba(0,0,0,0.12);
}

.logo {
    text-align: center;
    font-size: 28px;
    font-weight: 800;
    color: #172033;
    margin-bottom: 10px;
}

.logo span {
    color: #ff6b35;
}

.demo-badge {
    display: block;
    width: fit-content;
    margin: 0 auto 25px;
    background: #fff3ed;
    color: #ff6b35;
    padding: 7px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}

h1 {
    text-align: center;
    margin-bottom: 8px;
}

.subtitle {
    text-align: center;
    color: #777;
    margin-bottom: 25px;
}

.amount-box {
    background: #f7f8fc;
    border-radius: 14px;
    padding: 20px;
    text-align: center;
    margin-bottom: 25px;
}

.amount-box span {
    display: block;
    color: #777;
    font-size: 14px;
    margin-bottom: 7px;
}

.amount-box strong {
    font-size: 30px;
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 7px;
    font-weight: 600;
}

.form-group input {
    width: 100%;
    padding: 13px;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 15px;
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.demo-note {
    background: #fff8e8;
    border: 1px solid #f2d58a;
    padding: 13px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 13px;
    color: #705c20;
}

.pay-btn {
    width: 100%;
    border: none;
    padding: 15px;
    border-radius: 11px;
    background: #ff6b35;
    color: white;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
}

.pay-btn:hover {
    background: #e95725;
}

.back-btn {
    display: block;
    text-align: center;
    margin-top: 18px;
    color: #666;
    text-decoration: none;
}

</style>

</head>

<body>

<div class="payment-container">

    <div class="payment-card">

        <div class="logo">
            <span>Shop</span>Sphere
        </div>

        <span class="demo-badge">
            DEMO / SANDBOX PAYMENT
        </span>

        <h1>
            Secure Payment
        </h1>

        <p class="subtitle">
            This is a simulated payment gateway for Lab 11.
        </p>

        <div class="amount-box">

            <span>
                Amount to Pay
            </span>

            <strong>
                Rs. <?php echo number_format($total, 2); ?>
            </strong>

        </div>

        <div class="demo-note">
            This is a dummy payment system. No real money will be charged.
        </div>

        <form method="POST" action="process-payment.php">

            <div class="form-group">

                <label>
                    Cardholder Name
                </label>

                <input
                    type="text"
                    name="card_name"
                    placeholder="Anush Jung Thapa"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Card Number
                </label>

                <input
                    type="text"
                    name="card_number"
                    placeholder="4111 1111 1111 1111"
                    maxlength="19"
                    required
                >

            </div>

            <div class="row">

                <div class="form-group">

                    <label>
                        Expiry
                    </label>

                    <input
                        type="text"
                        name="expiry"
                        placeholder="12/30"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        CVV
                    </label>

                    <input
                        type="text"
                        name="cvv"
                        placeholder="123"
                        maxlength="3"
                        required
                    >

                </div>

            </div>

            <button
                type="submit"
                class="pay-btn"
            >
                Pay Rs. <?php echo number_format($total, 2); ?>
            </button>

        </form>

        <a href="checkout.php" class="back-btn">
            ← Back to Checkout
        </a>

    </div>

</div>

</body>

</html>