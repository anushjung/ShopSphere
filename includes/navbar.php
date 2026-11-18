
<?php
require_once __DIR__ . "/auth.php";

$current_page = basename($_SERVER['PHP_SELF']);
$accountName = $_SESSION["name"] ?? "Customer";
$accountEmail = $_SESSION["email"] ?? "";
$accountInitial = strtoupper(substr($accountName, 0, 1));

?>

<header class="shop-navbar" id="shopNavbar">

    <div class="navbar-container">

        <a href="index.php" class="shop-logo">
            <img src="assets/images/shopsphere-logo.png" alt="ShopSphere">
        </a>

        <div class="navbar-search">

            <form action="products.php" method="GET" id="navbarSearchForm">

                <span class="search-icon">🔍</span>

                <input
                    type="text"
                    name="search"
                    id="navbarSearchInput"
                    placeholder="Search products, brands & categories..."
                    autocomplete="off"
                >

                <button type="button" class="search-clear" id="searchClear">
                    ×
                </button>

            </form>

            <div class="search-suggestions" id="searchSuggestions"></div>

        </div>

        <nav class="main-nav">

            <a href="index.php"
               class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                <span>Home</span>
            </a>

            <a href="products.php"
               class="<?php echo $current_page === 'products.php' ? 'active' : ''; ?>">
                <span>Products</span>
            </a>

            <a href="index.php#categories">
                <span>Categories</span>
            </a>

            <a href="help-support.php"
               class="<?php echo $current_page === 'help-support.php' ? 'active' : ''; ?>">
                <span>Help & Support</span>
            </a>

        </nav>

        <div class="navbar-actions">

            <a href="wishlist.php"
               class="nav-action wishlist-action <?php echo $current_page === 'wishlist.php' ? 'active-action' : ''; ?>"
               title="Wishlist">
                <span class="action-icon">♡</span>
                <span class="action-label">Wishlist</span>
            </a>

            <a href="cart.php"
               class="nav-action cart-action <?php echo $current_page === 'cart.php' ? 'active-action' : ''; ?>"
               title="Shopping Cart">
                <span class="action-icon">🛒</span>
                <span class="action-label">Cart</span>
            </a>

            <?php if (isLoggedIn()): ?>

                <div class="account-wrapper">

                    <button
                        type="button"
                        class="account-button"
                        id="accountButton"
                        aria-expanded="false"
                        aria-controls="accountDropdown"
                    >

                        <span class="account-avatar">
                            <?php echo $accountInitial; ?>
                        </span>

                        <span class="account-name">
                            <?php echo htmlspecialchars($accountName); ?>
                        </span>

                        <span class="account-arrow">▾</span>

                    </button>

                    <div class="account-dropdown" id="accountDropdown">

                        <div class="dropdown-user">

                            <div class="dropdown-avatar">
                                <?php echo $accountInitial; ?>
                            </div>

                            <div>
                                <strong>
                                    <?php echo htmlspecialchars($accountName); ?>
                                </strong>

                                <small>
                                    <?php echo htmlspecialchars($accountEmail); ?>
                                </small>
                            </div>

                        </div>

                        <div class="dropdown-divider"></div>

                        <a href="orders.php">
                            <span>📦</span>
                            My Orders
                        </a>

                        <a href="wishlist.php">
                            <span>♡</span>
                            Wishlist
                        </a>

                        <a href="help-support.php">
                            <span>💬</span>
                            Help & Support
                        </a>

                        <div class="dropdown-divider"></div>

                        <a href="logout.php" class="logout-link">
                            <span>↪</span>
                            Logout
                        </a>

                    </div>

                </div>

            <?php else: ?>

                <div class="guest-actions">

                    <a href="login.php" class="login-link">
                        Login
                    </a>

                    <a href="register.php" class="signup-button">
                        Sign Up
                    </a>

                </div>

            <?php endif; ?>

            <button
                type="button"
                class="mobile-menu-button"
                id="mobileMenuButton"
                aria-expanded="false"
                aria-controls="mobileMenu"
                aria-label="Toggle navigation menu"
            >
                <span></span>
                <span></span>
                <span></span>
            </button>

        </div>

    </div>

    <div class="mobile-search">

        <form action="products.php" method="GET">

            <span>🔍</span>

            <input
                type="text"
                name="search"
                placeholder="Search products..."
            >

            <button type="submit">
                Search
            </button>

        </form>

    </div>

    <div class="mobile-menu" id="mobileMenu">

        <a href="index.php"
           class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
            🏠 Home
        </a>

        <a href="products.php"
           class="<?php echo $current_page === 'products.php' ? 'active' : ''; ?>">
            🛍️ Products
        </a>

        <a href="index.php#categories">
            📂 Categories
        </a>

        <a href="wishlist.php"
           class="<?php echo $current_page === 'wishlist.php' ? 'active' : ''; ?>">
            ♡ Wishlist
        </a>

        <a href="cart.php"
           class="<?php echo $current_page === 'cart.php' ? 'active' : ''; ?>">
            🛒 Cart
        </a>

        <a href="help-support.php"
           class="<?php echo $current_page === 'help-support.php' ? 'active' : ''; ?>">
            💬 Help & Support
        </a>

        <?php if (isLoggedIn()): ?>

            <div class="mobile-user">

                <span class="account-avatar">
                    <?php echo $accountInitial; ?>
                </span>

                <div>

                    <strong>
                        <?php echo htmlspecialchars($accountName); ?>
                    </strong>

                    <small>
                        My ShopSphere Account
                    </small>

                </div>

            </div>

            <a href="orders.php">
                📦 My Orders
            </a>

            <a href="logout.php" class="mobile-logout">
                ↪ Logout
            </a>

        <?php else: ?>

            <div class="mobile-auth">

                <a href="login.php">
                    Login
                </a>

                <a href="register.php">
                    Sign Up
                </a>

            </div>

        <?php endif; ?>

    </div>

</header>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const page = window.location.pathname;

    fetch("track-visitor.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "page=" + encodeURIComponent(page),
        keepalive: true
    }).catch(function () {});

});
</script>

<script src="assets/js/navbar.js" defer></script>
