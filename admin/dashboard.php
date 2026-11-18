<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$totalCustomers = 0;
$totalProducts = 0;
$totalOrders = 0;
$totalSales = 0;
$pendingOrders = 0;
$lowStock = 0;
$totalSellers = 0;
$totalDelivered = 0;

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE role = 'customer'
");

if ($result) {
    $totalCustomers = $result->fetch_assoc()['total'];
}

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM products
    WHERE status = 'active'
");

if ($result) {
    $totalProducts = $result->fetch_assoc()['total'];
}

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
");

if ($result) {
    $totalOrders = $result->fetch_assoc()['total'];
}

$result = $conn->query("
    SELECT COALESCE(SUM(total_amount), 0) AS total
    FROM orders
    WHERE status != 'cancelled'
");

if ($result) {
    $totalSales = $result->fetch_assoc()['total'];
}

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE status = 'pending'
");

if ($result) {
    $pendingOrders = $result->fetch_assoc()['total'];
}

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM products
    WHERE status = 'active'
    AND stock <= 5
");

if ($result) {
    $lowStock = $result->fetch_assoc()['total'];
}

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE role = 'seller'
");

if ($result) {
    $totalSellers = $result->fetch_assoc()['total'];
}

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE status = 'delivered'
");

if ($result) {
    $totalDelivered = $result->fetch_assoc()['total'];
}

$recentOrders = $conn->query("
    SELECT
        o.order_id,
        o.total_amount,
        o.status,
        o.payment_method,
        o.created_at,
        u.name AS customer_name
    FROM orders o
    INNER JOIN users u
        ON o.user_id = u.user_id
    ORDER BY o.created_at DESC
    LIMIT 8
");

$lowStockProducts = $conn->query("
    SELECT
        p.product_id,
        p.name,
        p.price,
        p.stock,
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c
        ON p.category_id = c.category_id
    WHERE p.status = 'active'
    AND p.stock <= 5
    ORDER BY p.stock ASC
    LIMIT 8
");

$recentCustomers = $conn->query("
    SELECT
        user_id,
        name,
        email,
        phone,
        created_at
    FROM users
    WHERE role = 'customer'
    ORDER BY created_at DESC
    LIMIT 6
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | ShopSphere</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

</head>

<body class="admin-body">

    <?php include "includes/sidebar.php"; ?>

    <div class="admin-main">

        <?php include "includes/topbar.php"; ?>

        <main class="admin-content">

            <div class="admin-page-header">

                <div>
                    <span class="admin-breadcrumb">Administration / Dashboard</span>

                    <h1>Dashboard</h1>

                    <p>
                        Overview of your ShopSphere store and business activity.
                    </p>
                </div>

                <div class="dashboard-date">
                    <?php echo date("d M Y"); ?>
                </div>

            </div>


            <section class="admin-stat-grid">

                <div class="admin-stat-card">

                    <div class="stat-card-icon customers-icon">
                        👥
                    </div>

                    <div class="stat-card-content">

                        <span>Total Customers</span>

                        <strong>
                            <?php echo number_format($totalCustomers); ?>
                        </strong>

                    </div>

                </div>


                <div class="admin-stat-card">

                    <div class="stat-card-icon products-icon">
                        📦
                    </div>

                    <div class="stat-card-content">

                        <span>Active Products</span>

                        <strong>
                            <?php echo number_format($totalProducts); ?>
                        </strong>

                    </div>

                </div>


                <div class="admin-stat-card">

                    <div class="stat-card-icon orders-icon">
                        🛒
                    </div>

                    <div class="stat-card-content">

                        <span>Total Orders</span>

                        <strong>
                            <?php echo number_format($totalOrders); ?>
                        </strong>

                    </div>

                </div>


                <div class="admin-stat-card">

                    <div class="stat-card-icon sales-icon">
                        💰
                    </div>

                    <div class="stat-card-content">

                        <span>Total Sales</span>

                        <strong>
                            Rs. <?php echo number_format($totalSales, 2); ?>
                        </strong>

                    </div>

                </div>

            </section>


            <section class="admin-mini-grid">

                <div class="admin-mini-card">

                    <span>Pending Orders</span>

                    <strong><?php echo number_format($pendingOrders); ?></strong>

                    <small>Orders waiting for processing</small>

                </div>


                <div class="admin-mini-card">

                    <span>Low Stock</span>

                    <strong><?php echo number_format($lowStock); ?></strong>

                    <small>Products requiring attention</small>

                </div>


                <div class="admin-mini-card">

                    <span>Sellers</span>

                    <strong><?php echo number_format($totalSellers); ?></strong>

                    <small>Registered sellers</small>

                </div>


                <div class="admin-mini-card">

                    <span>Delivered</span>

                    <strong><?php echo number_format($totalDelivered); ?></strong>

                    <small>Successfully delivered orders</small>

                </div>

            </section>


            <section class="admin-panel">

                <div class="admin-panel-header">

                    <div>

                        <h2>Recent Orders</h2>

                        <p>Latest orders placed in ShopSphere.</p>

                    </div>

                    <a href="orders.php" class="admin-view-all">
                        View All Orders →
                    </a>

                </div>


                <div class="admin-table-wrapper">

                    <table class="admin-table">

                        <thead>

                            <tr>

                                <th>Order ID</th>

                                <th>Customer</th>

                                <th>Amount</th>

                                <th>Payment</th>

                                <th>Status</th>

                                <th>Date</th>

                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>

                                <?php while ($order = $recentOrders->fetch_assoc()): ?>

                                    <tr>

                                        <td>
                                            <strong>
                                                #<?php echo $order['order_id']; ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($order['customer_name']); ?>
                                        </td>

                                        <td>
                                            Rs. <?php echo number_format($order['total_amount'], 2); ?>
                                        </td>

                                        <td>
                                            <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?>
                                        </td>

                                        <td>

                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php echo ucwords(str_replace('_', ' ', $order['status'])); ?>
                                            </span>

                                        </td>

                                        <td>
                                            <?php echo date("d M Y", strtotime($order['created_at'])); ?>
                                        </td>

                                        <td>

                                            <a
                                                href="orders.php?view=<?php echo $order['order_id']; ?>"
                                                class="table-action"
                                            >
                                                View
                                            </a>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="7" class="empty-table">
                                        No orders found.
                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>


            <div class="admin-two-column">


                <section class="admin-panel">

                    <div class="admin-panel-header">

                        <div>

                            <h2>Low Stock Products</h2>

                            <p>Products with five or fewer items remaining.</p>

                        </div>

                        <a href="products.php" class="admin-view-all">
                            Manage Products →
                        </a>

                    </div>


                    <div class="admin-table-wrapper">

                        <table class="admin-table">

                            <thead>

                                <tr>

                                    <th>Product</th>

                                    <th>Category</th>

                                    <th>Price</th>

                                    <th>Stock</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php if ($lowStockProducts && $lowStockProducts->num_rows > 0): ?>

                                    <?php while ($product = $lowStockProducts->fetch_assoc()): ?>

                                        <tr>

                                            <td>
                                                <strong>
                                                    <?php echo htmlspecialchars($product['name']); ?>
                                                </strong>
                                            </td>

                                            <td>
                                                <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                                            </td>

                                            <td>
                                                Rs. <?php echo number_format($product['price'], 2); ?>
                                            </td>

                                            <td>

                                                <?php if ($product['stock'] <= 2): ?>

                                                    <span class="stock-critical">
                                                        <?php echo $product['stock']; ?>
                                                    </span>

                                                <?php else: ?>

                                                    <span class="stock-low">
                                                        <?php echo $product['stock']; ?>
                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <tr>

                                        <td colspan="4" class="empty-table">
                                            All products have sufficient stock.
                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </section>


                <section class="admin-panel">

                    <div class="admin-panel-header">

                        <div>

                            <h2>Recent Customers</h2>

                            <p>Recently registered customers.</p>

                        </div>

                        <a href="users.php" class="admin-view-all">
                            View Users →
                        </a>

                    </div>


                    <div class="customer-list">

                        <?php if ($recentCustomers && $recentCustomers->num_rows > 0): ?>

                            <?php while ($customer = $recentCustomers->fetch_assoc()): ?>

                                <div class="customer-row">

                                    <div class="customer-avatar">

                                        <?php
                                        echo strtoupper(
                                            substr($customer['name'], 0, 1)
                                        );
                                        ?>

                                    </div>

                                    <div class="customer-info">

                                        <strong>
                                            <?php echo htmlspecialchars($customer['name']); ?>
                                        </strong>

                                        <span>
                                            <?php echo htmlspecialchars($customer['email']); ?>
                                        </span>

                                    </div>

                                    <div class="customer-date">

                                        <?php
                                        echo date(
                                            "d M",
                                            strtotime($customer['created_at'])
                                        );
                                        ?>

                                    </div>

                                </div>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <div class="empty-customer">
                                No customers found.
                            </div>

                        <?php endif; ?>

                    </div>

                </section>


            </div>


            <section class="quick-actions-panel">

                <div>

                    <h2>Quick Actions</h2>

                    <p>Frequently used administration functions.</p>

                </div>

                <div class="quick-actions">

                    <a href="products.php?action=add" class="quick-action">
                        <span>＋</span>
                        Add Product
                    </a>

                    <a href="categories.php?action=add" class="quick-action">
                        <span>＋</span>
                        Add Category
                    </a>

                    <a href="orders.php" class="quick-action">
                        <span>▤</span>
                        Manage Orders
                    </a>

                    <a href="users.php" class="quick-action">
                        <span>♙</span>
                        Manage Users
                    </a>

                </div>

            </section>

        </main>

        <?php include "includes/footer.php"; ?>

    </div>


    <script src="../assets/js/admin.js"></script>

</body>

</html>