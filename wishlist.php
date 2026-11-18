<?php

require_once "includes/db.php";
require_once "includes/auth.php";

requireLogin();

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT 
        w.wishlist_id,
        p.product_id,
        p.name,
        p.description,
        p.price,
        p.stock,
        p.image,
        c.name AS category_name
    FROM wishlist w
    JOIN products p ON w.product_id = p.product_id
    JOIN categories c ON p.category_id = c.category_id
    WHERE w.user_id = $user_id
    ORDER BY w.created_at DESC
";

$wishlist = $conn->query($sql);

$count_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM wishlist
    WHERE user_id = $user_id
");

$wishlist_count = $count_result->fetch_assoc()['total'];

$cart_result = $conn->query("
    SELECT COALESCE(SUM(quantity), 0) AS total
    FROM cart
    WHERE user_id = $user_id
");

$cart_count = $cart_result->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Wishlist | ShopSphere</title>

    <link rel="stylesheet" href="assets/css/wishlist.css">
        <link rel="stylesheet" href="assets/css/navbar.css">

</head>

<body>
<?php include "includes/navbar.php"; ?>

<main>

    <section class="wishlist-hero">

        <div class="hero-content">

            <span class="hero-label">YOUR COLLECTION</span>

            <h1>My Wishlist <span>♡</span></h1>

            <p>
                Keep the products you love close and come back to them whenever you're ready.
            </p>

        </div>

        <div class="hero-decoration">
            ♡
        </div>

    </section>


    <section class="wishlist-section">

        <div class="section-header">

            <div>
                <span class="section-label">SAVED ITEMS</span>

                <h2>
                    <?php echo $wishlist_count; ?>
                    <?php echo $wishlist_count == 1 ? 'Item' : 'Items'; ?>
                </h2>
            </div>

            <a href="products.php" class="continue-shopping">
                Continue Shopping →
            </a>

        </div>


        <?php if ($wishlist && $wishlist->num_rows > 0): ?>

            <div class="wishlist-grid">

                <?php while ($item = $wishlist->fetch_assoc()): ?>

                    <article class="wishlist-card">

                        <div class="product-image">

                            <?php if (!empty($item['image'])): ?>

                                <img
                                    src="assets/images/<?php echo htmlspecialchars($item['image']); ?>"
                                    alt="<?php echo htmlspecialchars($item['name']); ?>"
                                >

                            <?php else: ?>

                                <div class="no-image">
                                    🛍️
                                </div>

                            <?php endif; ?>


                            <div class="wishlist-badge">
                                ♡ Saved
                            </div>

                        </div>


                        <div class="product-content">

                            <span class="category">
                                <?php echo htmlspecialchars($item['category_name']); ?>
                            </span>

                            <h3>
                                <?php echo htmlspecialchars($item['name']); ?>
                            </h3>

                            <p class="description">
                                <?php echo htmlspecialchars($item['description']); ?>
                            </p>


                            <div class="product-bottom">

                                <div class="price">
                                    Rs. <?php echo number_format($item['price'], 2); ?>
                                </div>

                                <?php if ($item['stock'] > 0): ?>

                                    <span class="stock available">
                                        ● In Stock
                                    </span>

                                <?php else: ?>

                                    <span class="stock unavailable">
                                        ● Out of Stock
                                    </span>

                                <?php endif; ?>

                            </div>


                            <div class="card-actions">

                                <?php if ($item['stock'] > 0): ?>

                                    <form method="POST" action="add-to-cart.php">

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="<?php echo $item['product_id']; ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="quantity"
                                            value="1"
                                        >

                                        <button type="submit" class="add-cart">
                                            🛒 Add to Cart
                                        </button>

                                    </form>

                                <?php else: ?>

                                    <button class="add-cart disabled" disabled>
                                        Out of Stock
                                    </button>

                                <?php endif; ?>


                                <a
                                    href="wishlist-remove.php?id=<?php echo $item['wishlist_id']; ?>"
                                    class="remove-btn"
                                    title="Remove from wishlist"
                                >
                                    ×
                                </a>

                            </div>

                        </div>

                    </article>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div class="empty-wishlist">

                <div class="empty-icon">
                    ♡
                </div>

                <h2>Your wishlist is empty</h2>

                <p>
                    Save products you love here and easily find them later.
                </p>

                <a href="products.php" class="browse-btn">
                    Explore Products →
                </a>

            </div>

        <?php endif; ?>

    </section>


    <section class="wishlist-benefits">

        <div class="benefit">
            <div class="benefit-icon">🔒</div>
            <div>
                <strong>Secure Shopping</strong>
                <p>Your information stays protected.</p>
            </div>
        </div>

        <div class="benefit">
            <div class="benefit-icon">🚚</div>
            <div>
                <strong>Fast Delivery</strong>
                <p>Reliable delivery to your doorstep.</p>
            </div>
        </div>

        <div class="benefit">
            <div class="benefit-icon">↩️</div>
            <div>
                <strong>Easy Returns</strong>
                <p>Shop confidently with easy returns.</p>
            </div>
        </div>

    </section>

</main>


<footer>

    <div class="footer-content">

        <div>
            <a href="index.php" class="footer-logo">
                <span>Shop</span>Sphere
            </a>

            <p>
                Your World of Shopping, One Sphere.
            </p>
        </div>

        <div class="footer-links">
            <a href="index.php">Home</a>
            <a href="products.php">Products</a>
            <a href="wishlist.php">Wishlist</a>
            <a href="cart.php">Cart</a>
        </div>

    </div>

    <div class="footer-bottom">
        © <?php echo date("Y"); ?> ShopSphere. All rights reserved.
    </div>

</footer>

</body>
</html>