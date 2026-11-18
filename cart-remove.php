<?php

require_once "includes/db.php";
require_once "includes/auth.php";

requireLogin();

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: cart.php");
    exit;
}

$cart_id = (int) $_GET["id"];
$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "DELETE FROM cart
     WHERE cart_id = ? AND user_id = ?"
);

$stmt->bind_param("ii", $cart_id, $user_id);
$stmt->execute();

header("Location: cart.php");
exit;

?>