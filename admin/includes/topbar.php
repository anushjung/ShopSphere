<?php
require_once __DIR__ . "/../../includes/auth.php";

$admin_name = $_SESSION['name'] ?? 'Administrator';
?>

<header class="admin-topbar">

    <div class="topbar-left">

        <button class="sidebar-toggle" id="sidebarToggle">
            ☰
        </button>

        <div class="admin-page-heading">
            <span class="admin-small-title">ShopSphere</span>
            <strong>Administration</strong>
        </div>

    </div>

    <div class="topbar-right">

        <div class="admin-search">

            <span>⌕</span>

            <input
                type="text"
                id="adminSearch"
                placeholder="Search..."
                autocomplete="off"
            >

        </div>

        <button class="notification-button" type="button">
            ♢
            <span class="notification-dot"></span>
        </button>

        <div class="admin-profile">

            <div class="admin-avatar">
                <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
            </div>

            <div class="admin-profile-info">
                <strong><?php echo htmlspecialchars($admin_name); ?></strong>
                <span>Administrator</span>
            </div>

            <span class="profile-arrow">⌄</span>

            <div class="admin-profile-menu">

                <a href="settings.php">⚙ Settings</a>

                <a href="../index.php">↗ View Store</a>

                <a href="../logout.php" class="profile-logout">⇥ Logout</a>

            </div>

        </div>

    </div>

</header>