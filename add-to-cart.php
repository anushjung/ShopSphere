<?php

require_once "includes/db.php";
require_once "includes/auth.php";

requireLogin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: products.php");
    exit;
}

$product_id = (int) $_POST["product_id"];
$quantity = (int) $_POST["quantity"];

if ($product_id <= 0 || $quantity <= 0) {
    header("Location: products.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT product_id, stock
     FROM products
     WHERE product_id = ? AND status = 'active'"
);

$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: products.php");
    exit;
}

$product = $result->fetch_assoc();

if ($quantity > $product["stock"]) {
    $quantity = $product["stock"];
}

$user_id = $_SESSION["user_id"];

$check = $conn->prepare(
    "SELECT cart_id, quantity
     FROM cart
     WHERE user_id = ? AND product_id = ?"
);

$check->bind_param("ii", $user_id, $product_id);
$check->execute();

$cart_result = $check->get_result();

if ($cart_result->num_rows > 0) {

    $cart_item = $cart_result->fetch_assoc();

    $new_quantity = $cart_item["quantity"] + $quantity;

    if ($new_quantity > $product["stock"]) {
        $new_quantity = $product["stock"];
    }

    $update = $conn->prepare(
        "UPDATE cart
         SET quantity = ?
         WHERE cart_id = ?"
    );

    $update->bind_param("ii", $new_quantity, $cart_item["cart_id"]);
    $update->execute();

} else {

    $insert = $conn->prepare(
        "INSERT INTO cart (user_id, product_id, quantity)
         VALUES (?, ?, ?)"
    );

    $insert->bind_param("iii", $user_id, $product_id, $quantity);
    $insert->execute();
}

header("Location: cart.php");
exit;

?>