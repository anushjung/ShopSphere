<?php

require_once "includes/db.php";
require_once "includes/auth.php";

requireLogin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cart.php");
    exit;
}

$cart_id = (int) $_POST["cart_id"];
$quantity = (int) $_POST["quantity"];
$user_id = $_SESSION["user_id"];

if ($cart_id <= 0 || $quantity <= 0) {
    header("Location: cart.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT cart.cart_id, products.stock
     FROM cart
     INNER JOIN products
     ON cart.product_id = products.product_id
     WHERE cart.cart_id = ? AND cart.user_id = ?"
);

$stmt->bind_param("ii", $cart_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: cart.php");
    exit;
}

$item = $result->fetch_assoc();

if ($quantity > $item["stock"]) {
    $quantity = $item["stock"];
}

$update = $conn->prepare(
    "UPDATE cart
     SET quantity = ?
     WHERE cart_id = ? AND user_id = ?"
);

$update->bind_param("iii", $quantity, $cart_id, $user_id);
$update->execute();

header("Location: cart.php");
exit;

?>