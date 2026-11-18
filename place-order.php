<?php

require_once "includes/db.php";
require_once "includes/auth.php";
require_once "includes/mailer.php";

requireLogin();

$user_id = $_SESSION["user_id"];

$is_online_payment = isset($_GET["payment"]) && $_GET["payment"] === "success";

if ($is_online_payment) {

    if (!isset($_SESSION["payment_data"])) {
        header("Location: checkout.php");
        exit;
    }

    $payment_data = $_SESSION["payment_data"];

    if (
        !isset($payment_data["payment_status"]) ||
        $payment_data["payment_status"] !== "paid"
    ) {
        header("Location: checkout.php");
        exit;
    }

    $name = trim($payment_data["name"]);
    $email = trim($payment_data["email"]);
    $phone = trim($payment_data["phone"]);
    $address = trim($payment_data["address"]);
    $payment_method = "online";
    $transaction_id = $payment_data["transaction_id"] ?? "";

} else {

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Location: checkout.php");
        exit;
    }

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $payment_method = $_POST["payment_method"] ?? "cash_on_delivery";

    $transaction_id = "";
}

if ($name === "" || $email === "" || $phone === "" || $address === "") {
    header("Location: checkout.php");
    exit;
}

if (!in_array($payment_method, ["cash_on_delivery", "online"])) {
    $payment_method = "cash_on_delivery";
}

$cart_stmt = $conn->prepare(
    "SELECT cart.product_id,
            cart.quantity,
            products.name,
            products.price,
            products.stock
     FROM cart
     INNER JOIN products
     ON cart.product_id = products.product_id
     WHERE cart.user_id = ?"
);

$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();

$cart_result = $cart_stmt->get_result();

if ($cart_result->num_rows === 0) {
    unset($_SESSION["payment_data"]);
    header("Location: cart.php");
    exit;
}

$items = [];
$total = 0;

while ($item = $cart_result->fetch_assoc()) {

    if ($item["quantity"] > $item["stock"]) {
        unset($_SESSION["payment_data"]);
        header("Location: cart.php");
        exit;
    }

    $subtotal = $item["price"] * $item["quantity"];

    $total += $subtotal;

    $items[] = $item;
}

$conn->begin_transaction();

try {

    $order_stmt = $conn->prepare(
        "INSERT INTO orders
        (user_id, total_amount, status, shipping_address, payment_method)
        VALUES (?, ?, 'pending', ?, ?)"
    );

    $order_stmt->bind_param(
        "idss",
        $user_id,
        $total,
        $address,
        $payment_method
    );

    $order_stmt->execute();

    $order_id = $conn->insert_id;

    $item_stmt = $conn->prepare(
        "INSERT INTO order_items
        (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)"
    );

    $stock_stmt = $conn->prepare(
        "UPDATE products
         SET stock = stock - ?
         WHERE product_id = ?
         AND stock >= ?"
    );

    foreach ($items as $item) {

        $product_id = $item["product_id"];
        $quantity = $item["quantity"];
        $price = $item["price"];

        $item_stmt->bind_param(
            "iiid",
            $order_id,
            $product_id,
            $quantity,
            $price
        );

        $item_stmt->execute();

        $stock_stmt->bind_param(
            "iii",
            $quantity,
            $product_id,
            $quantity
        );

        $stock_stmt->execute();

        if ($stock_stmt->affected_rows !== 1) {
            throw new Exception("Insufficient stock.");
        }
    }

    if ($payment_method === "online") {
        $payment_status = "paid";
    } else {
        $payment_status = "pending";
    }

    $payment_stmt = $conn->prepare(
        "INSERT INTO payments
        (order_id, amount, payment_method, payment_status, transaction_id, paid_at)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    if ($payment_method === "online") {
        $paid_at = date("Y-m-d H:i:s");
    } else {
        $paid_at = null;
        $transaction_id = null;
    }

    $payment_stmt->bind_param(
        "idssss",
        $order_id,
        $total,
        $payment_method,
        $payment_status,
        $transaction_id,
        $paid_at
    );

    $payment_stmt->execute();

    $clear_stmt = $conn->prepare(
        "DELETE FROM cart
         WHERE user_id = ?"
    );

    $clear_stmt->bind_param("i", $user_id);
    $clear_stmt->execute();

    $conn->commit();

    unset($_SESSION["payment_data"]);

    $payment_text = $payment_method === "cash_on_delivery"
        ? "Cash on Delivery"
        : "Online Payment";

    $items_html = "";

    foreach ($items as $item) {

        $item_name = htmlspecialchars($item["name"]);
        $quantity = (int) $item["quantity"];
        $price = number_format($item["price"], 2);
        $subtotal = number_format(
            $item["price"] * $item["quantity"],
            2
        );

        $items_html .= "
        <tr>
            <td style='padding:12px;border-bottom:1px solid #e5e7eb;'>
                {$item_name}
            </td>
            <td style='padding:12px;text-align:center;border-bottom:1px solid #e5e7eb;'>
                {$quantity}
            </td>
            <td style='padding:12px;text-align:right;border-bottom:1px solid #e5e7eb;'>
                NPR {$price}
            </td>
            <td style='padding:12px;text-align:right;border-bottom:1px solid #e5e7eb;'>
                NPR {$subtotal}
            </td>
        </tr>";
    }

    $transaction_html = "";

    if ($payment_method === "online") {
        $transaction_html = "
            <p style='margin:5px 0;'>
                <strong>Transaction ID:</strong> " .
                htmlspecialchars($transaction_id) .
            "</p>";
    }

    $subject = "ShopSphere Order Confirmation - Order #{$order_id}";

    $body = "
    <div style='font-family:Arial,sans-serif;max-width:700px;margin:auto;background:#f5f7fb;padding:30px;'>

        <div style='background:#111827;padding:25px;text-align:center;'>
            <h1 style='color:#ffffff;margin:0;'>ShopSphere</h1>

            <p style='color:#f97316;margin:8px 0 0;'>
                Your World of Shopping, One Sphere.
            </p>
        </div>

        <div style='background:#ffffff;padding:30px;'>

            <h2 style='color:#111827;'>
                Order Confirmed! 🎉
            </h2>

            <p style='color:#4b5563;'>
                Hi " . htmlspecialchars($name) . ",
            </p>

            <p style='color:#4b5563;'>
                Thank you for shopping with ShopSphere.
                Your order has been successfully placed.
            </p>

            <div style='background:#f9fafb;padding:18px;margin:20px 0;border-radius:8px;'>

                <p style='margin:5px 0;'>
                    <strong>Order ID:</strong> #{$order_id}
                </p>

                <p style='margin:5px 0;'>
                    <strong>Status:</strong> Pending
                </p>

                <p style='margin:5px 0;'>
                    <strong>Payment:</strong> {$payment_text}
                </p>

                {$transaction_html}

                <p style='margin:5px 0;'>
                    <strong>Delivery Address:</strong>
                    " . htmlspecialchars($address) . "
                </p>

            </div>

            <h3 style='color:#111827;'>
                Order Summary
            </h3>

            <table style='width:100%;border-collapse:collapse;font-size:14px;'>

                <thead>

                    <tr style='background:#f3f4f6;'>

                        <th style='padding:12px;text-align:left;'>
                            Product
                        </th>

                        <th style='padding:12px;text-align:center;'>
                            Qty
                        </th>

                        <th style='padding:12px;text-align:right;'>
                            Price
                        </th>

                        <th style='padding:12px;text-align:right;'>
                            Subtotal
                        </th>

                    </tr>

                </thead>

                <tbody>
                    {$items_html}
                </tbody>

            </table>

            <div style='text-align:right;margin-top:20px;'>

                <strong style='font-size:18px;color:#111827;'>
                    Total: NPR " . number_format($total, 2) . "
                </strong>

            </div>

            <div style='text-align:center;margin:30px 0;'>

                <a href='http://localhost/ShopSphere/order-details.php?id={$order_id}'
                   style='background:#f97316;color:#ffffff;text-decoration:none;padding:12px 25px;border-radius:8px;display:inline-block;'>
                    View Your Order
                </a>

            </div>

            <p style='color:#6b7280;font-size:13px;'>
                We will keep you updated as your order moves through the delivery process.
            </p>

        </div>

        <div style='text-align:center;padding:20px;'>

            <p style='color:#9ca3af;font-size:12px;'>
                © " . date("Y") . " ShopSphere. All rights reserved.
            </p>

        </div>

    </div>
    ";

    sendEmail(
        $email,
        $name,
        $subject,
        $body
    );

    header("Location: order-success.php?id=" . $order_id);
    exit;

} catch (Exception $e) {

    $conn->rollback();

    unset($_SESSION["payment_data"]);

    header("Location: checkout.php");
    exit;
}

?>