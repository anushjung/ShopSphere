
<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$search = trim($_GET["search"] ?? "");
$status = $_GET["status"] ?? "";

$sql = "
    SELECT
        p.payment_id,
        p.order_id,
        p.amount,
        p.payment_method,
        p.payment_status,
        p.transaction_id,
        p.paid_at,
        o.user_id,
        u.name AS customer_name,
        u.email AS customer_email
    FROM payments p
    INNER JOIN orders o ON p.order_id = o.order_id
    INNER JOIN users u ON o.user_id = u.user_id
    WHERE 1 = 1
";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (
        u.name LIKE ?
        OR u.email LIKE ?
        OR p.transaction_id LIKE ?
        OR CAST(p.order_id AS CHAR) LIKE ?
    )";

    $keyword = "%" . $search . "%";

    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;

    $types .= "ssss";
}

if (in_array($status, ["pending", "paid", "failed"], true)) {
    $sql .= " AND p.payment_status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY p.payment_id DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$payments = $stmt->get_result();

$total_payments = $conn->query("
    SELECT COUNT(*) AS total FROM payments
")->fetch_assoc()["total"];

$successful_payments = $conn->query("
    SELECT COUNT(*) AS total FROM payments WHERE payment_status = 'paid'
")->fetch_assoc()["total"];

$pending_payments = $conn->query("
    SELECT COUNT(*) AS total FROM payments WHERE payment_status = 'pending'
")->fetch_assoc()["total"];

$failed_payments = $conn->query("
    SELECT COUNT(*) AS total FROM payments WHERE payment_status = 'failed'
")->fetch_assoc()["total"];

$total_revenue = $conn->query("
    SELECT COALESCE(SUM(amount), 0) AS total
    FROM payments
    WHERE payment_status = 'paid'
")->fetch_assoc()["total"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Payments Management | ShopSphere Admin</title>

    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/payments.css">

</head>

<body class="admin-body">

    <?php include "includes/sidebar.php"; ?>

    <div class="admin-main">

        <?php include "includes/topbar.php"; ?>

        <main class="payments-content">

            <div class="payments-heading">
                <div>
                    <span class="payments-breadcrumb">Admin / Payments</span>
                    <h1>Payments Management</h1>
                    <p>Monitor customer transactions and payment records.</p>
                </div>
            </div>

            <section class="payments-stats">

                <div class="payment-stat-card">
                    <span>Total Payments</span>
                    <h2><?php echo (int)$total_payments; ?></h2>
                </div>

                <div class="payment-stat-card">
                    <span>Successful Payments</span>
                    <h2><?php echo (int)$successful_payments; ?></h2>
                </div>

                <div class="payment-stat-card">
                    <span>Pending Payments</span>
                    <h2><?php echo (int)$pending_payments; ?></h2>
                </div>

                <div class="payment-stat-card">
                    <span>Failed Payments</span>
                    <h2><?php echo (int)$failed_payments; ?></h2>
                </div>

                <div class="payment-stat-card payment-revenue-card">
                    <span>Total Revenue</span>
                    <h2>Rs. <?php echo number_format((float)$total_revenue, 2); ?></h2>
                </div>

            </section>

            <section class="payments-table-section">

                <div class="payments-toolbar">

                    <h2>Payment Records</h2>

                    <form method="GET" class="payments-filters">

                        <input
                            type="text"
                            name="search"
                            placeholder="Search customer, order or transaction..."
                            value="<?php echo htmlspecialchars($search); ?>"
                        >

                        <select name="status">

                            <option value="">All Status</option>

                            <option value="paid"
                                <?php echo $status === "paid" ? "selected" : ""; ?>>
                                Paid
                            </option>

                            <option value="pending"
                                <?php echo $status === "pending" ? "selected" : ""; ?>>
                                Pending
                            </option>

                            <option value="failed"
                                <?php echo $status === "failed" ? "selected" : ""; ?>>
                                Failed
                            </option>

                        </select>

                        <button type="submit">Filter</button>

                    </form>

                </div>

                <div class="payments-table-wrapper">

                    <table class="payments-table">

                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Transaction ID</th>
                                <th>Status</th>
                                <th>Payment Date</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($payments->num_rows > 0): ?>

                                <?php while ($payment = $payments->fetch_assoc()): ?>

                                    <tr>

                                        <td>#<?php echo (int)$payment["payment_id"]; ?></td>

                                        <td>#<?php echo (int)$payment["order_id"]; ?></td>

                                        <td>
                                            <div class="payment-customer">
                                                <strong>
                                                    <?php echo htmlspecialchars($payment["customer_name"]); ?>
                                                </strong>
                                                <small>
                                                    <?php echo htmlspecialchars($payment["customer_email"]); ?>
                                                </small>
                                            </div>
                                        </td>

                                        <td class="payment-amount">
                                            Rs. <?php echo number_format((float)$payment["amount"], 2); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars(ucwords(str_replace("_", " ", $payment["payment_method"]))); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($payment["transaction_id"] ?: "N/A"); ?>
                                        </td>

                                        <td>
                                            <span class="payment-status payment-status-<?php echo htmlspecialchars($payment["payment_status"]); ?>">
                                                <?php echo htmlspecialchars(ucfirst($payment["payment_status"])); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?php
                                            echo $payment["paid_at"]
                                                ? date("d M Y, h:i A", strtotime($payment["paid_at"]))
                                                : "Not paid";
                                            ?>
                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="8" class="payments-empty">
                                        No payment records found.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>

        </main>

    </div>

    <?php include "includes/footer.php"; ?>

</body>
</html>
