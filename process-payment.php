<?php

require_once "includes/db.php";
require_once "includes/auth.php";

requireLogin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: checkout.php");
    exit;
}

if (!isset($_SESSION["payment_data"])) {
    header("Location: checkout.php");
    exit;
}

$payment_data = $_SESSION["payment_data"];

$card_name = trim($_POST["card_name"] ?? "");
$card_number = preg_replace('/\s+/', '', $_POST["card_number"] ?? "");
$expiry = trim($_POST["expiry"] ?? "");
$cvv = trim($_POST["cvv"] ?? "");

if ($card_name === "" || $card_number === "" || $expiry === "" || $cvv === "") {
    header("Location: payment.php");
    exit;
}

$payment_success = false;

if ($card_number === "4111111111111111" && $expiry !== "" && $cvv === "123") {
    $payment_success = true;
}

if (!$payment_success) {

    unset($_SESSION["payment_data"]);

    header("Location: payment-failed.php");
    exit;
}

$_SESSION["payment_data"]["transaction_id"] =
    "DEMO-" . strtoupper(bin2hex(random_bytes(5)));

$_SESSION["payment_data"]["payment_status"] = "paid";

header("Location: place-order.php?payment=success");
exit;

?>