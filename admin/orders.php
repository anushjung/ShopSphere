<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    if ($action === "update_status") {

        $order_id = intval($_POST["order_id"]);
        $status = $_POST["status"];

        $allowed_statuses = [
            "pending",
            "confirmed",
            "processing",
            "shipped",
            "out_for_delivery",
            "delivered",
            "cancelled"
        ];

        if (in_array($status, $allowed_statuses)) {

            $stmt = $conn->prepare("
                UPDATE orders
                SET status = ?
                WHERE order_id = ?
            ");

            $stmt->bind_param(
                "si",
                $status,
                $order_id
            );

            if ($stmt->execute()) {
                $message = "Order status updated successfully.";
                $message_type = "success";
            } else {
                $message = "Unable to update order status.";
                $message_type = "error";
            }
        }
    }
}


$search = trim($_GET["search"] ?? "");
$status_filter = $_GET["status"] ?? "";


$query = "
    SELECT
        o.order_id,
        o.user_id,
        o.total_amount,
        o.status,
        o.shipping_address,
        o.payment_method,
        o.created_at,
        u.name AS customer_name,
        u.email AS customer_email,
        COUNT(oi.order_item_id) AS item_count
    FROM orders o
    INNER JOIN users u
        ON o.user_id = u.user_id
    LEFT JOIN order_items oi
        ON o.order_id = oi.order_id
    WHERE 1=1
";


$params = [];
$types = "";


if ($search !== "") {

    $query .= "
        AND (
            CAST(o.order_id AS CHAR) LIKE ?
            OR u.name LIKE ?
            OR u.email LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "sss";
}


if ($status_filter !== "") {

    $query .= " AND o.status = ? ";

    $params[] = $status_filter;
    $types .= "s";
}


$query .= "
    GROUP BY
        o.order_id,
        o.user_id,
        o.total_amount,
        o.status,
        o.shipping_address,
        o.payment_method,
        o.created_at,
        u.name,
        u.email
    ORDER BY o.created_at DESC
";


$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$orders = $stmt->get_result();


$status_counts = [];

$status_query = "
    SELECT status, COUNT(*) AS total
    FROM orders
    GROUP BY status
";

$status_result = $conn->query($status_query);

while ($row = $status_result->fetch_assoc()) {
    $status_counts[$row["status"]] = $row["total"];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Orders | ShopSphere Admin</title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>

<body class="admin-body">

<?php include "includes/sidebar.php"; ?>

<div class="admin-main">

<?php include "includes/topbar.php"; ?>


<main class="admin-content">


    <div class="admin-page-header">

        <div>

            <span class="admin-breadcrumb">
                Administration / Orders
            </span>

            <h1>Orders</h1>

            <p>
                Manage customer orders and update their delivery status.
            </p>

        </div>

    </div>


    <?php if ($message !== ""): ?>

        <div class="admin-alert <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <div class="order-status-summary">


        <div class="order-summary-item">

            <span class="order-summary-label">
                Pending
            </span>

            <strong>
                <?php echo $status_counts["pending"] ?? 0; ?>
            </strong>

        </div>


        <div class="order-summary-item">

            <span class="order-summary-label">
                Processing
            </span>

            <strong>
                <?php echo $status_counts["processing"] ?? 0; ?>
            </strong>

        </div>


        <div class="order-summary-item">

            <span class="order-summary-label">
                Shipped
            </span>

            <strong>
                <?php echo $status_counts["shipped"] ?? 0; ?>
            </strong>

        </div>


        <div class="order-summary-item">

            <span class="order-summary-label">
                Delivered
            </span>

            <strong>
                <?php echo $status_counts["delivered"] ?? 0; ?>
            </strong>

        </div>


        <div class="order-summary-item">

            <span class="order-summary-label">
                Cancelled
            </span>

            <strong>
                <?php echo $status_counts["cancelled"] ?? 0; ?>
            </strong>

        </div>


    </div>


    <section class="admin-panel">


        <div class="product-toolbar">


            <form
                method="GET"
                class="product-filter-form"
            >


                <div class="admin-filter-search">

                    <span>🔍</span>

                    <input
                        type="text"
                        name="search"
                        value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Search order ID, customer or email..."
                    >

                </div>


                <select
                    name="status"
                    class="admin-filter-select"
                >

                    <option value="">
                        All Status
                    </option>

                    <option
                        value="pending"
                        <?php echo $status_filter === "pending" ? "selected" : ""; ?>
                    >
                        Pending
                    </option>

                    <option
                        value="confirmed"
                        <?php echo $status_filter === "confirmed" ? "selected" : ""; ?>
                    >
                        Confirmed
                    </option>

                    <option
                        value="processing"
                        <?php echo $status_filter === "processing" ? "selected" : ""; ?>
                    >
                        Processing
                    </option>

                    <option
                        value="shipped"
                        <?php echo $status_filter === "shipped" ? "selected" : ""; ?>
                    >
                        Shipped
                    </option>

                    <option
                        value="out_for_delivery"
                        <?php echo $status_filter === "out_for_delivery" ? "selected" : ""; ?>
                    >
                        Out for Delivery
                    </option>

                    <option
                        value="delivered"
                        <?php echo $status_filter === "delivered" ? "selected" : ""; ?>
                    >
                        Delivered
                    </option>

                    <option
                        value="cancelled"
                        <?php echo $status_filter === "cancelled" ? "selected" : ""; ?>
                    >
                        Cancelled
                    </option>

                </select>


                <button
                    type="submit"
                    class="filter-button"
                >
                    Filter
                </button>


                <a
                    href="orders.php"
                    class="clear-filter"
                >
                    Clear
                </a>


            </form>


            <div class="product-count">

                <?php echo $orders->num_rows; ?> orders

            </div>


        </div>


        <div class="admin-table-wrapper">


            <table class="admin-table order-table">


                <thead>

                    <tr>

                        <th>Order</th>

                        <th>Customer</th>

                        <th>Items</th>

                        <th>Total</th>

                        <th>Payment</th>

                        <th>Status</th>

                        <th>Date</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>


                <?php if ($orders->num_rows > 0): ?>


                    <?php while ($order = $orders->fetch_assoc()): ?>


                        <tr>


                            <td>

                                <strong>
                                    #<?php echo $order["order_id"]; ?>
                                </strong>

                            </td>


                            <td>

                                <div class="order-customer">

                                    <strong>
                                        <?php echo htmlspecialchars($order["customer_name"]); ?>
                                    </strong>

                                    <span>
                                        <?php echo htmlspecialchars($order["customer_email"]); ?>
                                    </span>

                                </div>

                            </td>


                            <td>

                                <?php echo $order["item_count"]; ?>

                            </td>


                            <td>

                                <strong>
                                    Rs. <?php echo number_format($order["total_amount"], 2); ?>
                                </strong>

                            </td>


                            <td>

                                <?php

                                echo $order["payment_method"] === "cash_on_delivery"
                                    ? "Cash on Delivery"
                                    : "Online";

                                ?>

                            </td>


                            <td>

                                <?php

                                $status_class = str_replace(
                                    "_",
                                    "-",
                                    $order["status"]
                                );

                                ?>

                                <span
                                    class="admin-status status-<?php echo $status_class; ?>"
                                >
                                    <?php
                                    echo ucwords(
                                        str_replace(
                                            "_",
                                            " ",
                                            $order["status"]
                                        )
                                    );
                                    ?>
                                </span>

                            </td>


                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime($order["created_at"])
                                );
                                ?>

                                <small class="order-time">

                                    <?php
                                    echo date(
                                        "h:i A",
                                        strtotime($order["created_at"])
                                    );
                                    ?>

                                </small>

                            </td>


                            <td>

                                <div class="table-actions">


                                    <a
                                        href="order-details.php?id=<?php echo $order["order_id"]; ?>"
                                        class="edit-action"
                                    >
                                        View
                                    </a>


                                    <button
                                        type="button"
                                        class="status-action"
                                        onclick='openStatusModal(
                                            <?php echo (int)$order["order_id"]; ?>,
                                            <?php echo json_encode($order["status"]); ?>
                                        )'
                                    >
                                        Status
                                    </button>


                                </div>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="8"
                            class="empty-table"
                        >
                            No orders found.
                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>


        </div>


    </section>


</main>


<?php include "includes/footer.php"; ?>

</div>


<div
    class="product-modal"
    id="statusModal"
>


    <div
        class="product-modal-overlay"
        onclick="closeStatusModal()"
    ></div>


    <div class="product-modal-box">


        <div class="product-modal-header">

            <div>

                <span class="admin-breadcrumb">
                    Order Management
                </span>

                <h2>
                    Update Order Status
                </h2>

            </div>


            <button
                type="button"
                onclick="closeStatusModal()"
                class="modal-close"
            >
                ×
            </button>

        </div>


        <form
            method="POST"
            class="product-form"
        >


            <input
                type="hidden"
                name="action"
                value="update_status"
            >


            <input
                type="hidden"
                name="order_id"
                id="statusOrderId"
            >


            <div class="form-group form-full">

                <label>
                    Order Status
                </label>


                <select
                    name="status"
                    id="statusSelect"
                    required
                >

                    <option value="pending">
                        Pending
                    </option>

                    <option value="confirmed">
                        Confirmed
                    </option>

                    <option value="processing">
                        Processing
                    </option>

                    <option value="shipped">
                        Shipped
                    </option>

                    <option value="out_for_delivery">
                        Out for Delivery
                    </option>

                    <option value="delivered">
                        Delivered
                    </option>

                    <option value="cancelled">
                        Cancelled
                    </option>

                </select>

            </div>


            <div class="product-form-actions">

                <button
                    type="button"
                    class="modal-cancel"
                    onclick="closeStatusModal()"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="admin-primary-button"
                >
                    Update Status
                </button>

            </div>


        </form>


    </div>

</div>


<script src="../assets/js/admin.js"></script>


<script>

function openStatusModal(orderId, status) {

    document.getElementById("statusOrderId").value = orderId;

    document.getElementById("statusSelect").value = status;

    document
        .getElementById("statusModal")
        .classList.add("show");
}


function closeStatusModal() {

    document
        .getElementById("statusModal")
        .classList.remove("show");

}

</script>


</body>

</html>