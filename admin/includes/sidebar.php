<?php
$current_admin_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="admin-sidebar" id="adminSidebar">

    <div class="admin-brand">
        <a href="dashboard.php">
            <img src="../assets/images/shopsphere-logo.png" alt="ShopSphere">
        </a>
    </div>

    <div class="admin-menu-label">MAIN MENU</div>

    <nav class="admin-menu">

        <a href="dashboard.php" class="<?php echo $current_admin_page === 'dashboard.php' ? 'active' : ''; ?>">
            <span class="menu-icon">▦</span>
            <span>Dashboard</span>
        </a>

        <a href="products.php" class="<?php echo $current_admin_page === 'products.php' ? 'active' : ''; ?>">
            <span class="menu-icon">▣</span>
            <span>Products</span>
        </a>

        <a href="categories.php" class="<?php echo $current_admin_page === 'categories.php' ? 'active' : ''; ?>">
            <span class="menu-icon">◈</span>
            <span>Categories</span>
        </a>

        <a href="orders.php" class="<?php echo $current_admin_page === 'orders.php' ? 'active' : ''; ?>">
            <span class="menu-icon">▤</span>
            <span>Orders</span>
        </a>

        <a href="users.php" class="<?php echo $current_admin_page === 'users.php' ? 'active' : ''; ?>">
            <span class="menu-icon">♙</span>
            <span>Users</span>
        </a>

        <a href="reviews.php" class="<?php echo $current_admin_page === 'reviews.php' ? 'active' : ''; ?>">
            <span class="menu-icon">★</span>
            <span>Reviews</span>
        </a>

        <a href="payments.php" class="<?php echo $current_admin_page === 'payments.php' ? 'active' : ''; ?>">
            <span class="menu-icon">◉</span>
            <span>Payments</span>
        </a>

        <a href="reports.php" class="<?php echo $current_admin_page === 'reports.php' ? 'active' : ''; ?>">
            <span class="menu-icon">◒</span>
            <span>Reports</span>
        </a>

    </nav>

    <div class="admin-menu-label">SYSTEM</div>

    <nav class="admin-menu">

        <a href="settings.php" class="<?php echo $current_admin_page === 'settings.php' ? 'active' : ''; ?>">
            <span class="menu-icon">⚙</span>
            <span>Settings</span>
        </a>

        <a href="../index.php">
            <span class="menu-icon">↗</span>
            <span>View Store</span>
        </a>

        <a href="../logout.php" class="logout-link">
            <span class="menu-icon">⇥</span>
            <span>Logout</span>
        </a>

    </nav>

</aside>