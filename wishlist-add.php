<?php

require_once "includes/db.php";
require_once "includes/auth.php";

requireLogin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: products.php");
    exit;
}

$product_id = (int) $_POST["product_id"];
$user_id = $_SESSION["user_id"];

if ($product_id <= 0) {
    header("Location: products.php");
    exit;
}

$check = $conn->prepare(
    "SELECT wishlist_id
     FROM wishlist
     WHERE user_id = ? AND product_id = ?"
);

$check->bind_param("ii", $user_id, $product_id);
$check->execute();

$result = $check->get_result();

if ($result->num_rows === 0) {

    $stmt = $conn->prepare(
        "INSERT INTO wishlist (user_id, product_id)
         VALUES (?, ?)"
    );

    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
}

header("Location: wishlist.php");
exit;

?>