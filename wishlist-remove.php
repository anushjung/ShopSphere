<?php

require_once "includes/db.php";
require_once "includes/auth.php";

requireLogin();

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: wishlist.php");
    exit;
}

$wishlist_id = (int) $_GET["id"];
$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "DELETE FROM wishlist
     WHERE wishlist_id = ? AND user_id = ?"
);

$stmt->bind_param("ii", $wishlist_id, $user_id);
$stmt->execute();

header("Location: wishlist.php");
exit;

?>