<?php

require_once "includes/db.php";
require_once "includes/auth.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$categories = $conn->query("
    SELECT category_id, name
    FROM categories
    ORDER BY name ASC
");

$featuredProducts = $conn->query("
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
    ORDER BY p.created_at DESC
    LIMIT 4
");

$trendingProducts = $conn->query("
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
    ORDER BY p.product_id DESC
    LIMIT 4 OFFSET 4
");

$totalProductsResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM products
    WHERE status = 'active'
");

$totalProducts = 0;

if ($totalProductsResult) {
    $totalProducts = $totalProductsResult->fetch_assoc()['total'];
}

$categoryIcons = [
    'Electronics' => '💻',
    'Fashion' => '👕',
    'Footwear' => '👟',
    'Home & Living' => '🏠',
    'Gaming' => '🎮',
    'Accessories' => '🎧'
];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>ShopSphere | Your World of Shopping</title>

    <link rel="stylesheet" href="assets/css/navbar.css">
<link rel="stylesheet" href="assets/css/home.css">

</head>

<body>
<?php include "includes/navbar.php"; ?>

<main>


<section class="hero-section">

    <div class="hero-background-circle circle-one"></div>
    <div class="hero-background-circle circle-two"></div>


    <div class="hero-content">

        <div class="hero-badge">
            <span>✦</span>
            EVERYTHING IN ONE SPHERE
        </div>

        <h1>
            Your World of
            <span>Shopping.</span>
        </h1>

        <p>
            Discover products you'll love across electronics,
            fashion, gaming, footwear, home essentials and more.
        </p>


        <div class="hero-buttons">

            <a href="products.php" class="primary-button">
                Shop Now
                <span>→</span>
            </a>

            <a href="#categories" class="secondary-button">
                Explore Categories
            </a>

        </div>


        <div class="hero-stats">

            <div class="hero-stat">

                <strong>
                    <?php echo $totalProducts; ?>+
                </strong>

                <span>
                    Products
                </span>

            </div>


            <div class="hero-stat-line"></div>


            <div class="hero-stat">

                <strong>
                    6+
                </strong>

                <span>
                    Categories
                </span>

            </div>


            <div class="hero-stat-line"></div>


            <div class="hero-stat">

                <strong>
                    24/7
                </strong>

                <span>
                    Shopping
                </span>

            </div>

        </div>

    </div>


    <div class="hero-showcase">

        <div class="showcase-glow"></div>

        <div class="showcase-ring ring-one"></div>
        <div class="showcase-ring ring-two"></div>


        <div class="showcase-card">

            <div class="showcase-image">

                <img
                    src="assets/images/gaming-laptop.jpg"
                    alt="Gaming Laptop"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                >

                <div class="showcase-placeholder">
                    💻
                </div>

            </div>


            <div class="showcase-details">

                <span>
                    FEATURED PRODUCT
                </span>

                <h3>
                    Gaming Laptop
                </h3>

                <div class="showcase-bottom">

                    <strong>
                        Rs. 125,000.00
                    </strong>

                    <a href="product-details.php?id=1">
                        View →
                    </a>

                </div>

            </div>

        </div>


        <div class="floating-card rating-card">

            <div class="floating-card-icon">
                ⭐
            </div>

            <div>

                <strong>
                    4.8/5
                </strong>

                <span>
                    Top Rated
                </span>

            </div>

        </div>


        <div class="floating-card delivery-card">

            <div class="floating-card-icon">
                🚚
            </div>

            <div>

                <strong>
                    Easy Delivery
                </strong>

                <span>
                    Track your order
                </span>

            </div>

        </div>

    </div>

</section>



<section class="trust-section">

    <div class="trust-item">

        <div class="trust-icon">
            🚚
        </div>

        <div>
            <strong>
                Easy Delivery
            </strong>

            <span>
                Convenient order tracking
            </span>
        </div>

    </div>


    <div class="trust-item">

        <div class="trust-icon">
            🔒
        </div>

        <div>
            <strong>
                Secure Shopping
            </strong>

            <span>
                Protected customer data
            </span>
        </div>

    </div>


    <div class="trust-item">

        <div class="trust-icon">
            💳
        </div>

        <div>
            <strong>
                Flexible Payment
            </strong>

            <span>
                Multiple payment options
            </span>
        </div>

    </div>


    <div class="trust-item">

        <div class="trust-icon">
            ⭐
        </div>

        <div>
            <strong>
                Quality Products
            </strong>

            <span>
                Products worth discovering
            </span>
        </div>

    </div>

</section>



<section class="content-section" id="categories">

    <div class="section-heading-row">

        <div>

            <span class="section-label">
                SHOP BY CATEGORY
            </span>

            <h2>
                Explore Our Categories
            </h2>

            <p>
                Find what you're looking for faster.
            </p>

        </div>

        <a href="products.php" class="view-all-link">
            View All →
        </a>

    </div>


    <div class="category-grid">

        <?php if ($categories && $categories->num_rows > 0): ?>

            <?php while ($category = $categories->fetch_assoc()): ?>

                <a
                    href="products.php?category=<?php echo $category['category_id']; ?>"
                    class="category-card"
                >

                    <div class="category-icon">

                        <?php
                        echo $categoryIcons[$category['name']] ?? '🛍️';
                        ?>

                    </div>


                    <div class="category-info">

                        <h3>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </h3>

                        <span>
                            Explore
                            <b>→</b>
                        </span>

                    </div>

                </a>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="empty-message">
                No categories available.
            </div>

        <?php endif; ?>

    </div>

</section>



<section class="content-section products-section">

    <div class="section-heading-row">

        <div>

            <span class="section-label">
                FEATURED COLLECTION
            </span>

            <h2>
                Popular Right Now
            </h2>

            <p>
                Discover products selected from our collection.
            </p>

        </div>

        <a href="products.php" class="view-all-link">
            Shop All →
        </a>

    </div>


    <div class="product-grid">

        <?php if ($featuredProducts && $featuredProducts->num_rows > 0): ?>

            <?php while ($product = $featuredProducts->fetch_assoc()): ?>

                <article class="product-card">

                    <div class="product-image">

                        <?php if (!empty($product['image'])): ?>

                            <img
                                src="assets/images/<?php echo htmlspecialchars($product['image']); ?>"
                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                            >

                        <?php else: ?>

                            <div class="image-placeholder">
                                🛍️
                            </div>

                        <?php endif; ?>


                        <span class="category-badge">
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </span>


                        <?php if ($product['stock'] > 0 && $product['stock'] <= 5): ?>

                            <span class="stock-badge">
                                Only <?php echo $product['stock']; ?> left
                            </span>

                        <?php elseif ($product['stock'] == 0): ?>

                            <span class="stock-badge sold-out">
                                Sold Out
                            </span>

                        <?php endif; ?>

                    </div>


                    <div class="product-content">

                        <div class="rating">
                            ⭐ 4.8
                        </div>

                        <h3>
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h3>

                        <p>

                            <?php

                            $description = $product['description'] ?? '';

                            if (strlen($description) > 70) {
                                $description = substr($description, 0, 70) . '...';
                            }

                            echo htmlspecialchars($description);

                            ?>

                        </p>


                        <div class="product-price-row">

                            <strong>
                                Rs. <?php echo number_format($product['price'], 2); ?>
                            </strong>


                            <?php if ($product['stock'] > 0): ?>

                                <form
                                    method="POST"
                                    action="add-to-cart.php"
                                >

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

                                    <button
                                        type="submit"
                                        class="cart-button"
                                    >
                                        🛒
                                    </button>

                                </form>

                            <?php endif; ?>

                        </div>


                        <a
                            href="product-details.php?id=<?php echo $product['product_id']; ?>"
                            class="view-product-button"
                        >
                            View Product
                        </a>

                    </div>

                </article>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="empty-products">

                <div>
                    📦
                </div>

                <h3>
                    No products available
                </h3>

                <p>
                    Add products from the admin panel.
                </p>

            </div>

        <?php endif; ?>

    </div>

</section>



<section class="promo-section">

    <div class="promo-content">

        <span class="section-label">
            SHOPSPHERE COLLECTION
        </span>

        <h2>
            Upgrade Your
            <span>Everyday.</span>
        </h2>

        <p>
            From smart gadgets to everyday essentials,
            discover products designed to fit your lifestyle.
        </p>

        <a href="products.php" class="promo-button">
            Explore Collection →
        </a>

    </div>


    <div class="promo-visual">

        <div class="promo-circle circle-large"></div>
        <div class="promo-circle circle-small"></div>

        <div class="promo-product promo-product-one">
            💻
        </div>

        <div class="promo-product promo-product-two">
            🎧
        </div>

        <div class="promo-product promo-product-three">
            🎮
        </div>

        <div class="promo-center">
            🛍️
        </div>

    </div>

</section>



<?php if ($trendingProducts && $trendingProducts->num_rows > 0): ?>

<section class="content-section trending-section">

    <div class="section-heading-row">

        <div>

            <span class="section-label">
                TRENDING NOW
            </span>

            <h2>
                More To Explore
            </h2>

            <p>
                Take a look at more products from ShopSphere.
            </p>

        </div>

        <a href="products.php" class="view-all-link">
            View Products →
        </a>

    </div>


    <div class="product-grid">

        <?php while ($product = $trendingProducts->fetch_assoc()): ?>

            <article class="product-card">

                <div class="product-image">

                    <?php if (!empty($product['image'])): ?>

                        <img
                            src="assets/images/<?php echo htmlspecialchars($product['image']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                        >

                    <?php else: ?>

                        <div class="image-placeholder">
                            🛍️
                        </div>

                    <?php endif; ?>


                    <span class="category-badge">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </span>

                </div>


                <div class="product-content">

                    <div class="rating">
                        ⭐ 4.8
                    </div>

                    <h3>
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h3>

                    <p>

                        <?php

                        $description = $product['description'] ?? '';

                        if (strlen($description) > 70) {
                            $description = substr($description, 0, 70) . '...';
                        }

                        echo htmlspecialchars($description);

                        ?>

                    </p>


                    <div class="product-price-row">

                        <strong>
                            Rs. <?php echo number_format($product['price'], 2); ?>
                        </strong>


                        <?php if ($product['stock'] > 0): ?>

                            <form
                                method="POST"
                                action="add-to-cart.php"
                            >

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

                                <button
                                    type="submit"
                                    class="cart-button"
                                >
                                    🛒
                                </button>

                            </form>

                        <?php endif; ?>

                    </div>


                    <a
                        href="product-details.php?id=<?php echo $product['product_id']; ?>"
                        class="view-product-button"
                    >
                        View Product
                    </a>

                </div>

            </article>

        <?php endwhile; ?>

    </div>

</section>

<?php endif; ?>



<section class="benefits-section">

    <div class="benefits-heading">

        <span class="section-label">
            WHY SHOPSPHERE
        </span>

        <h2>
            Shopping Made Simple
        </h2>

        <p>
            Everything you need for a smooth shopping experience.
        </p>

    </div>


    <div class="benefits-grid">

        <div class="benefit-card">

            <div class="benefit-icon">
                🔎
            </div>

            <h3>
                Easy Discovery
            </h3>

            <p>
                Find products quickly through our organized categories
                and simple product browsing.
            </p>

        </div>


        <div class="benefit-card">

            <div class="benefit-icon">
                🛒
            </div>

            <h3>
                Simple Shopping
            </h3>

            <p>
                Add products to your cart and complete your order
                with a straightforward checkout process.
            </p>

        </div>


        <div class="benefit-card">

            <div class="benefit-icon">
                📍
            </div>

            <h3>
                Order Tracking
            </h3>

            <p>
                Follow your order progress from confirmation
                through delivery.
            </p>

        </div>


        <div class="benefit-card">

            <div class="benefit-icon">
                ❤️
            </div>

            <h3>
                Wishlist
            </h3>

            <p>
                Save products you love and keep them ready
                for your next shopping visit.
            </p>

        </div>

    </div>

</section>



<section class="final-cta">

    <div class="final-cta-content">

        <span>
            READY TO SHOP?
        </span>

        <h2>
            Your Next Favorite
            <br>
            Product Is Here.
        </h2>

        <p>
            Explore ShopSphere and discover something you'll love.
        </p>

        <a href="products.php">
            Start Shopping →
        </a>

    </div>

</section>

</main>



<footer class="site-footer">

    <div class="footer-main">

        <div class="footer-brand">

            <a href="index.php" class="brand footer-logo">

                <span class="brand-icon">
                    S
                </span>

                <span class="brand-text">
                    Shop<span>Sphere</span>
                </span>

            </a>

            <p>
                Your World of Shopping, One Sphere.
                Discover products, shop with ease and enjoy
                a modern online shopping experience.
            </p>

        </div>


        <div class="footer-column">

            <h3>
                Shop
            </h3>

            <a href="products.php">
                All Products
            </a>

            <a href="products.php">
                Categories
            </a>

            <a href="wishlist.php">
                Wishlist
            </a>

            <a href="cart.php">
                Cart
            </a>

        </div>


        <div class="footer-column">

            <h3>
                Account
            </h3>

            <?php if (isLoggedIn()): ?>

                <a href="orders.php">
                    My Orders
                </a>

                <a href="logout.php">
                    Logout
                </a>

            <?php else: ?>

                <a href="login.php">
                    Login
                </a>

                <a href="register.php">
                    Create Account
                </a>

            <?php endif; ?>

        </div>


        <div class="footer-column">

            <h3>
                ShopSphere
            </h3>

            <a href="products.php">
                Explore
            </a>

            <a href="index.php#categories">
                Categories
            </a>

            <a href="index.php">
                Home
            </a>

        </div>

    </div>


    <div class="footer-bottom">

        <span>
            © <?php echo date("Y"); ?> ShopSphere. All rights reserved.
        </span>

        <span>
            Your World of Shopping, One Sphere.
        </span>

    </div>

</footer>


</body>
</html>
