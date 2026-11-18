<?php

require_once "includes/db.php";
require_once "includes/auth.php";

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId <= 0) {
    header("Location: products.php");
    exit;
}

$productQuery = $conn->query("
    SELECT
        p.product_id,
        p.name,
        p.description,
        p.price,
        p.stock,
        p.image,
        p.category_id,
        c.name AS category_name
    FROM products p
    INNER JOIN categories c ON p.category_id = c.category_id
    WHERE p.product_id = $productId
    AND p.status = 'active'
    LIMIT 1
");

if (!$productQuery || $productQuery->num_rows === 0) {
    header("Location: products.php");
    exit;
}

$product = $productQuery->fetch_assoc();

$averageRating = 0;
$totalReviews = 0;

$ratingQuery = $conn->query("
    SELECT
        COALESCE(AVG(rating), 0) AS average_rating,
        COUNT(*) AS total_reviews
    FROM reviews
    WHERE product_id = $productId
");

if ($ratingQuery) {
    $ratingData = $ratingQuery->fetch_assoc();
    $averageRating = round(floatval($ratingData['average_rating']), 1);
    $totalReviews = intval($ratingData['total_reviews']);
}

$reviews = $conn->query("
    SELECT
        r.rating,
        r.review,
        r.created_at,
        u.name
    FROM reviews r
    INNER JOIN users u ON r.user_id = u.user_id
    WHERE r.product_id = $productId
    ORDER BY r.created_at DESC
");

$relatedProducts = $conn->query("
    SELECT
        p.product_id,
        p.name,
        p.price,
        p.stock,
        p.image,
        c.name AS category_name
    FROM products p
    INNER JOIN categories c ON p.category_id = c.category_id
    WHERE p.category_id = {$product['category_id']}
    AND p.product_id != $productId
    AND p.status = 'active'
    ORDER BY p.created_at DESC
    LIMIT 4
");

$cartCount = 0;
$wishlistCount = 0;

if (isLoggedIn()) {

    $userId = intval($_SESSION['user_id']);

    $cartQuery = $conn->query("
        SELECT COALESCE(SUM(quantity), 0) AS total
        FROM cart
        WHERE user_id = $userId
    ");

    if ($cartQuery) {
        $cartCount = intval($cartQuery->fetch_assoc()['total']);
    }

    $wishlistQuery = $conn->query("
        SELECT COUNT(*) AS total
        FROM wishlist
        WHERE user_id = $userId
    ");

    if ($wishlistQuery) {
        $wishlistCount = intval($wishlistQuery->fetch_assoc()['total']);
    }
}

$viewUserId = isLoggedIn() ? intval($_SESSION['user_id']) : "NULL";

$conn->query("
    INSERT INTO product_views (user_id, product_id)
    VALUES ($viewUserId, $productId)
");

$description = trim($product['description'] ?? '');

if ($description === '') {
    $description = "Discover quality and value with this carefully selected ShopSphere product.";
}

$seoDescription = mb_substr(
    preg_replace('/\s+/', ' ', strip_tags($description)),
    0,
    155
);

$seoTitle = $product['name'] . " | ShopSphere";

$canonicalUrl = "http://localhost/ShopSphere/product-details.php?id=" . $product['product_id'];

?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo htmlspecialchars($seoTitle); ?>
    </title>

    <meta
        name="description"
        content="<?php echo htmlspecialchars($seoDescription); ?>"
    >

    <meta
        name="keywords"
        content="<?php echo htmlspecialchars($product['name']); ?>, <?php echo htmlspecialchars($product['category_name']); ?>, ShopSphere, online shopping"
    >

    <meta name="robots" content="index, follow">

    <link
        rel="canonical"
        href="<?php echo htmlspecialchars($canonicalUrl); ?>"
    >

    <meta
        property="og:title"
        content="<?php echo htmlspecialchars($seoTitle); ?>"
    >

    <meta
        property="og:description"
        content="<?php echo htmlspecialchars($seoDescription); ?>"
    >

    <meta
        property="og:type"
        content="product"
    >

    <meta
        property="og:url"
        content="<?php echo htmlspecialchars($canonicalUrl); ?>"
    >

    <?php if (!empty($product['image'])): ?>

        <meta
            property="og:image"
            content="http://localhost/ShopSphere/assets/images/<?php echo htmlspecialchars($product['image']); ?>"
        >

    <?php endif; ?>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Product",
        "name": <?php echo json_encode($product['name']); ?>,
        "description": <?php echo json_encode($description); ?>,
        "category": <?php echo json_encode($product['category_name']); ?>,
        "offers": {
            "@type": "Offer",
            "price": <?php echo json_encode(number_format($product['price'], 2, '.', '')); ?>,
            "priceCurrency": "NPR",
            "availability": "<?php echo $product['stock'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'; ?>",
            "url": <?php echo json_encode($canonicalUrl); ?>
        }
    }
    </script>

    <link rel="stylesheet" href="assets/css/product-details.css">

</head>

<body>

<header class="site-header">

    <div class="navbar">

        <a href="index.php" class="logo">
            <span class="logo-mark">S</span>
            <span>Shop<span>Sphere</span></span>
        </a>

        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="products.php">Products</a>
            <a href="products.php">Categories</a>
        </nav>

        <div class="nav-actions">

            <form action="products.php" method="GET" class="nav-search">
                <input type="text" name="search" placeholder="Search products...">
                <button type="submit">⌕</button>
            </form>

            <a href="wishlist.php" class="nav-icon">
                ♡
                <?php if ($wishlistCount > 0): ?>
                    <span><?php echo $wishlistCount; ?></span>
                <?php endif; ?>
            </a>

            <a href="cart.php" class="nav-icon">
                🛒
                <?php if ($cartCount > 0): ?>
                    <span><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>

            <?php if (isLoggedIn()): ?>

                <a href="#" class="account-link">
                    <span class="account-avatar">
                        <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                    </span>

                    <span class="account-name">
                        <?php echo htmlspecialchars($_SESSION['name']); ?>
                    </span>
                </a>

                <a href="logout.php" class="logout-link">Logout</a>

            <?php else: ?>

                <a href="login.php" class="login-link">Login</a>
                <a href="register.php" class="signup-link">Sign Up</a>

            <?php endif; ?>

        </div>

    </div>

</header>

<main>

    <section class="breadcrumb-section">

        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>›</span>
            <a href="products.php">Products</a>
            <span>›</span>
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </div>

    </section>

    <section class="product-details-section">

        <div class="product-details-container">

            <div class="product-gallery">

                <div class="main-product-image">

                    <?php if (!empty($product['image'])): ?>

                        <img
                            src="assets/images/<?php echo htmlspecialchars($product['image']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                        >

                    <?php else: ?>

                        <div class="image-placeholder">
                            <span>📦</span>
                        </div>

                    <?php endif; ?>

                    <div class="image-badge">
                        ✨ ShopSphere Pick
                    </div>

                </div>

            </div>

            <div class="product-information">

                <div class="product-category">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </div>

                <h1>
                    <?php echo htmlspecialchars($product['name']); ?>
                </h1>

                <div class="rating-row">

                    <div class="stars">

                        <?php
                        $fullStars = floor($averageRating);

                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= $fullStars ? "★" : "☆";
                        }
                        ?>

                    </div>

                    <strong><?php echo number_format($averageRating, 1); ?></strong>

                    <span>
                        <?php echo $totalReviews; ?>
                        <?php echo $totalReviews == 1 ? 'review' : 'reviews'; ?>
                    </span>

                </div>

                <div class="price-box">

                    <span class="price-label">Price</span>

                    <div class="product-price">
                        <small>Rs.</small>
                        <?php echo number_format($product['price'], 2); ?>
                    </div>

                    <span class="price-note">
                        Inclusive of applicable taxes
                    </span>

                </div>

                <div class="product-description">
                    <h3>Product Description</h3>

                    <p>
                        <?php echo nl2br(htmlspecialchars($description)); ?>
                    </p>
                </div>

                <div class="availability">

                    <?php if ($product['stock'] > 0): ?>

                        <span class="available-dot"></span>

                        <strong>In Stock</strong>

                        <span>
                            <?php echo $product['stock']; ?> units available
                        </span>

                    <?php else: ?>

                        <span class="unavailable-dot"></span>

                        <strong>Out of Stock</strong>

                    <?php endif; ?>

                </div>

                <?php if ($product['stock'] > 0): ?>

                    <div class="purchase-area">

                        <div class="quantity-wrapper">

                            <label>Quantity</label>

                            <div class="quantity-control">

                                <button type="button" onclick="decreaseQuantity()">−</button>

                                <input
                                    type="number"
                                    id="quantity"
                                    value="1"
                                    min="1"
                                    max="<?php echo $product['stock']; ?>"
                                >

                                <button type="button" onclick="increaseQuantity()">+</button>

                            </div>

                        </div>

                        <div class="purchase-buttons">

                            <form method="POST" action="add-to-cart.php" id="cart-form">

                                <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?php echo $product['product_id']; ?>"
                                >

                                <input
                                    type="hidden"
                                    name="quantity"
                                    id="cart-quantity"
                                    value="1"
                                >

                                <button type="submit" class="add-cart-button">
                                    🛒 Add to Cart
                                </button>

                            </form>

                            <form method="POST" action="wishlist-add.php">

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

                    </div>

                <?php else: ?>

                    <button class="out-stock-button" disabled>
                        Currently Unavailable
                    </button>

                <?php endif; ?>

                <div class="product-features">

                    <div class="feature-item">
                        <div class="feature-icon">🚚</div>
                        <div>
                            <strong>Fast Delivery</strong>
                            <span>Reliable delivery service</span>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">🔒</div>
                        <div>
                            <strong>Secure Shopping</strong>
                            <span>Your information is protected</span>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">↩</div>
                        <div>
                            <strong>Easy Returns</strong>
                            <span>Simple return process</span>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">✓</div>
                        <div>
                            <strong>Quality Products</strong>
                            <span>Selected for ShopSphere</span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </section>

    <section class="reviews-section">

        <div class="section-container">

            <div class="section-heading">

                <div>
                    <span class="section-label">CUSTOMER FEEDBACK</span>

                    <h2>Reviews & Ratings</h2>

                    <p>
                        See what customers think about this product.
                    </p>
                </div>

                <div class="rating-summary">

                    <strong><?php echo number_format($averageRating, 1); ?></strong>

                    <div>
                        <div class="summary-stars">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= floor($averageRating) ? "★" : "☆";
                            }
                            ?>
                        </div>

                        <span>
                            Based on <?php echo $totalReviews; ?> reviews
                        </span>
                    </div>

                </div>

            </div>

            <?php if ($reviews && $reviews->num_rows > 0): ?>

                <div class="reviews-grid">

                    <?php while ($review = $reviews->fetch_assoc()): ?>

                        <div class="review-card">

                            <div class="review-header">

                                <div class="review-user">

                                    <div class="review-avatar">
                                        <?php echo strtoupper(substr($review['name'], 0, 1)); ?>
                                    </div>

                                    <div>
                                        <strong>
                                            <?php echo htmlspecialchars($review['name']); ?>
                                        </strong>

                                        <span>
                                            <?php echo date("M d, Y", strtotime($review['created_at'])); ?>
                                        </span>
                                    </div>

                                </div>

                                <div class="review-stars">

                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= intval($review['rating']) ? "★" : "☆";
                                    }
                                    ?>

                                </div>

                            </div>

                            <?php if (!empty($review['review'])): ?>

                                <p>
                                    <?php echo nl2br(htmlspecialchars($review['review'])); ?>
                                </p>

                            <?php else: ?>

                                <p class="no-review-text">
                                    Customer left a rating without a written review.
                                </p>

                            <?php endif; ?>

                        </div>

                    <?php endwhile; ?>

                </div>

            <?php else: ?>

                <div class="no-reviews">

                    <div>⭐</div>

                    <h3>No reviews yet</h3>

                    <p>
                        Be the first to share your experience with this product.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </section>

    <?php if ($relatedProducts && $relatedProducts->num_rows > 0): ?>

        <section class="related-section">

            <div class="section-container">

                <div class="section-heading related-heading">

                    <div>
                        <span class="section-label">YOU MAY ALSO LIKE</span>
                        <h2>Related Products</h2>
                    </div>

                    <a href="products.php?category=<?php echo $product['category_id']; ?>">
                        View All →
                    </a>

                </div>

                <div class="related-grid">

                    <?php while ($related = $relatedProducts->fetch_assoc()): ?>

                        <article class="related-card">

                            <div class="related-image">

                                <a href="product-details.php?id=<?php echo $related['product_id']; ?>">

                                    <?php if (!empty($related['image'])): ?>

                                        <img
                                            src="assets/images/<?php echo htmlspecialchars($related['image']); ?>"
                                            alt="<?php echo htmlspecialchars($related['name']); ?>"
                                        >

                                    <?php else: ?>

                                        <div class="image-placeholder">
                                            📦
                                        </div>

                                    <?php endif; ?>

                                </a>

                                <span>
                                    <?php echo htmlspecialchars($related['category_name']); ?>
                                </span>

                            </div>

                            <div class="related-info">

                                <a
                                    href="product-details.php?id=<?php echo $related['product_id']; ?>"
                                    class="related-name"
                                >
                                    <?php echo htmlspecialchars($related['name']); ?>
                                </a>

                                <div class="related-bottom">

                                    <strong>
                                        <small>Rs.</small>
                                        <?php echo number_format($related['price'], 2); ?>
                                    </strong>

                                    <a
                                        href="product-details.php?id=<?php echo $related['product_id']; ?>"
                                        class="view-button"
                                    >
                                        View →
                                    </a>

                                </div>

                            </div>

                        </article>

                    <?php endwhile; ?>

                </div>

            </div>

        </section>

    <?php endif; ?>

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

        <p>
            © <?php echo date('Y'); ?> ShopSphere. All rights reserved.
        </p>

        <p>
            Designed for a better shopping experience.
        </p>

    </div>

</footer>

<script>

function increaseQuantity() {

    const quantity = document.getElementById("quantity");
    const cartQuantity = document.getElementById("cart-quantity");

    const max = parseInt(quantity.max);
    const current = parseInt(quantity.value);

    if (current < max) {
        quantity.value = current + 1;
        cartQuantity.value = quantity.value;
    }
}

function decreaseQuantity() {

    const quantity = document.getElementById("quantity");
    const cartQuantity = document.getElementById("cart-quantity");

    const current = parseInt(quantity.value);

    if (current > 1) {
        quantity.value = current - 1;
        cartQuantity.value = quantity.value;
    }
}

document.getElementById("quantity").addEventListener("change", function () {

    const max = parseInt(this.max);
    let value = parseInt(this.value);

    if (isNaN(value) || value < 1) {
        value = 1;
    }

    if (value > max) {
        value = max;
    }

    this.value = value;
    document.getElementById("cart-quantity").value = value;

});

</script>

</body>
</html>