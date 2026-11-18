<?php

require_once "includes/db.php";
require_once "includes/auth.php";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;

$categories = $conn->query("
    SELECT category_id, name
    FROM categories
    ORDER BY name ASC
");

$sql = "
    SELECT
        p.product_id,
        p.name,
        p.description,
        p.price,
        p.stock,
        p.image,
        c.name AS category_name
    FROM products p
    INNER JOIN categories c ON p.category_id = c.category_id
    WHERE p.status = 'active'
";

if ($search !== '') {
    $safeSearch = $conn->real_escape_string($search);
    $sql .= "
        AND (
            p.name LIKE '%$safeSearch%'
            OR p.description LIKE '%$safeSearch%'
            OR c.name LIKE '%$safeSearch%'
        )
    ";
}

if ($category > 0) {
    $sql .= " AND p.category_id = $category";
}

$sql .= " ORDER BY p.created_at DESC";

$products = $conn->query($sql);

$cartCount = 0;

if (isLoggedIn()) {
    $userId = intval($_SESSION['user_id']);

    $cartResult = $conn->query("
        SELECT COALESCE(SUM(quantity), 0) AS total
        FROM cart
        WHERE user_id = $userId
    ");

    if ($cartResult) {
        $cartCount = intval($cartResult->fetch_assoc()['total']);
    }
}

$wishlistCount = 0;

if (isLoggedIn()) {
    $wishlistResult = $conn->query("
        SELECT COUNT(*) AS total
        FROM wishlist
        WHERE user_id = $userId
    ");

    if ($wishlistResult) {
        $wishlistCount = intval($wishlistResult->fetch_assoc()['total']);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products | ShopSphere</title>
    <link rel="stylesheet" href="assets/css/products.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
</head>

<body>
<?php include "includes/navbar.php"; ?>
<main>

    <section class="products-hero">
        <div class="hero-content">
            <span class="eyebrow">SHOPSPHERE COLLECTION</span>

            <h1>
                Discover Something
                <span>You'll Love.</span>
            </h1>

            <p>
                Explore our collection of carefully selected products,
                from everyday essentials to the latest technology.
            </p>
        </div>
    </section>

    <section class="products-section">

        <div class="products-top">

            <div>
                <span class="section-label">OUR STORE</span>

                <h2>
                    <?php if ($search !== ''): ?>
                        Search Results
                    <?php elseif ($category > 0): ?>
                        Category Products
                    <?php else: ?>
                        All Products
                    <?php endif; ?>
                </h2>

                <p>
                    <?php
                    if ($products) {
                        echo $products->num_rows . " products available";
                    }
                    ?>
                </p>
            </div>

            <form method="GET" action="products.php" class="sort-form">

                <?php if ($search !== ''): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <?php endif; ?>

                <select name="category" onchange="this.form.submit()">
                    <option value="0">All Categories</option>

                    <?php if ($categories): ?>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option
                                value="<?php echo $cat['category_id']; ?>"
                                <?php echo $category == $cat['category_id'] ? 'selected' : ''; ?>
                            >
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>

                </select>

            </form>

        </div>

        <div class="products-layout">

            <aside class="category-sidebar">

                <div class="sidebar-heading">
                    <span>☰</span>
                    <h3>Categories</h3>
                </div>

                <a
                    href="products.php<?php echo $search !== '' ? '?search=' . urlencode($search) : ''; ?>"
                    class="<?php echo $category == 0 ? 'selected' : ''; ?>"
                >
                    <span>All Products</span>
                    <b>→</b>
                </a>

                <?php

                $sidebarCategories = $conn->query("
                    SELECT category_id, name
                    FROM categories
                    ORDER BY name ASC
                ");

                if ($sidebarCategories):
                    while ($cat = $sidebarCategories->fetch_assoc()):
                ?>

                    <a
                        href="products.php?category=<?php echo $cat['category_id']; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>"
                        class="<?php echo $category == $cat['category_id'] ? 'selected' : ''; ?>"
                    >
                        <span><?php echo htmlspecialchars($cat['name']); ?></span>
                        <b>→</b>
                    </a>

                <?php
                    endwhile;
                endif;
                ?>

                <div class="sidebar-promo">
                    <span>✨</span>
                    <h4>Shop Smarter</h4>
                    <p>Find everything you need in one sphere.</p>
                    <a href="products.php">Explore All →</a>
                </div>

            </aside>

            <div class="products-area">

                <?php if ($products && $products->num_rows > 0): ?>

                    <div class="product-grid">

                        <?php while ($product = $products->fetch_assoc()): ?>

                            <article class="product-card">

                                <div class="product-image">

                                    <a href="product-details.php?id=<?php echo $product['product_id']; ?>">

                                        <?php if (!empty($product['image'])): ?>

                                            <img
                                                src="assets/images/<?php echo htmlspecialchars($product['image']); ?>"
                                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                            >

                                        <?php else: ?>

                                            <div class="no-image">
                                                <span>📦</span>
                                            </div>

                                        <?php endif; ?>

                                    </a>

                                    <span class="product-category">
                                        <?php echo htmlspecialchars($product['category_name']); ?>
                                    </span>

                                    <?php if ($product['stock'] <= 5 && $product['stock'] > 0): ?>

                                        <span class="stock-badge">
                                            Only <?php echo $product['stock']; ?> left
                                        </span>

                                    <?php elseif ($product['stock'] == 0): ?>

                                        <span class="stock-badge sold-out">
                                            Sold Out
                                        </span>

                                    <?php endif; ?>

                                    <form method="POST" action="wishlist-add.php" class="wishlist-form">

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="<?php echo $product['product_id']; ?>"
                                        >

                                        <button type="submit" class="wishlist-button">
                                            ♡
                                        </button>

                                    </form>

                                </div>

                                <div class="product-info">

                                    <a
                                        href="product-details.php?id=<?php echo $product['product_id']; ?>"
                                        class="product-name"
                                    >
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>

                                    <p class="product-description">
                                        <?php
                                        $description = trim($product['description'] ?? '');

                                        echo htmlspecialchars(
                                            strlen($description) > 75
                                                ? substr($description, 0, 75) . '...'
                                                : $description
                                        );
                                        ?>
                                    </p>

                                    <div class="product-bottom">

                                        <div class="price">
                                            <span>Rs.</span>
                                            <?php echo number_format($product['price'], 2); ?>
                                        </div>

                                        <?php if ($product['stock'] > 0): ?>

                                            <form method="POST" action="add-to-cart.php">

                                                <input
                                                    type="hidden"
                                                    name="product_id"
                                                    value="<?php echo $product['product_id']; ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="quantity"
                                                    value="1"
                                                >

                                                <button type="submit" class="add-cart">
                                                    🛒
                                                    <span>Add</span>
                                                </button>

                                            </form>

                                        <?php else: ?>

                                            <button class="add-cart disabled" disabled>
                                                Sold Out
                                            </button>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </article>

                        <?php endwhile; ?>

                    </div>

                <?php else: ?>

                    <div class="empty-products">

                        <div class="empty-icon">🔍</div>

                        <h3>No Products Found</h3>

                        <p>
                            We couldn't find any products matching your search.
                        </p>

                        <a href="products.php" class="reset-button">
                            View All Products
                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </section>

</main>

<footer class="site-footer">

    <div class="footer-main">

        <div class="footer-brand">
            <a href="index.php" class="logo">
                <span class="logo-mark">S</span>
                <span>Shop<span>Sphere</span></span>
            </a>

            <p>
                Your World of Shopping, One Sphere.
            </p>
        </div>

        <div class="footer-column">
            <h4>Shop</h4>
            <a href="products.php">All Products</a>
            <a href="products.php">Categories</a>
            <a href="wishlist.php">Wishlist</a>
            <a href="cart.php">Cart</a>
        </div>

        <div class="footer-column">
            <h4>Account</h4>
            <a href="login.php">Login</a>
            <a href="register.php">Create Account</a>
            <a href="orders.php">My Orders</a>
        </div>

        <div class="footer-column">
            <h4>ShopSphere</h4>
            <a href="index.php">About Us</a>
            <a href="index.php">Contact</a>
            <a href="index.php">Privacy Policy</a>
        </div>

    </div>

    <div class="footer-bottom">
        <p>© <?php echo date('Y'); ?> ShopSphere. All rights reserved.</p>
        <p>Designed for a better shopping experience.</p>
    </div>

</footer>

</body>
</html>