<?php

require_once "includes/db.php";
require_once "includes/auth.php";

requireLogin();

$userId = intval($_SESSION['user_id']);

$cartQuery = $conn->query("
    SELECT
        c.cart_id,
        c.product_id,
        c.quantity,
        p.name,
        p.price,
        p.stock,
        p.image,
        p.status,
        cat.name AS category_name
    FROM cart c
    INNER JOIN products p ON c.product_id = p.product_id
    INNER JOIN categories cat ON p.category_id = cat.category_id
    WHERE c.user_id = $userId
    ORDER BY c.added_at DESC
");

$cartItems = [];
$subtotal = 0;
$totalItems = 0;

if ($cartQuery) {
    while ($item = $cartQuery->fetch_assoc()) {
        $cartItems[] = $item;

        $subtotal += $item['price'] * $item['quantity'];
        $totalItems += $item['quantity'];
    }
}

$deliveryCharge = $subtotal > 5000 || $subtotal == 0 ? 0 : 150;
$grandTotal = $subtotal + $deliveryCharge;

$wishlistCount = 0;

$wishlistQuery = $conn->query("
    SELECT COUNT(*) AS total
    FROM wishlist
    WHERE user_id = $userId
");

if ($wishlistQuery) {
    $wishlistCount = intval($wishlistQuery->fetch_assoc()['total']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | ShopSphere</title>
    <link rel="stylesheet" href="assets/css/cart.css">
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

            <a href="cart.php" class="nav-icon cart-active">
                🛒
                <?php if ($totalItems > 0): ?>
                    <span><?php echo $totalItems; ?></span>
                <?php endif; ?>
            </a>

            <a href="#" class="account-link">
                <span class="account-avatar">
                    <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                </span>

                <span class="account-name">
                    <?php echo htmlspecialchars($_SESSION['name']); ?>
                </span>
            </a>

            <a href="logout.php" class="logout-link">Logout</a>

        </div>

    </div>

</header>

<main>

    <section class="cart-hero">

        <div class="cart-hero-content">

            <span class="eyebrow">SHOPPING CART</span>

            <h1>Your Cart, <span>Ready to Go.</span></h1>

            <p>
                Review your selected products before moving on to checkout.
            </p>

        </div>

    </section>

    <section class="cart-section">

        <div class="cart-container">

            <?php if (count($cartItems) > 0): ?>

                <div class="cart-heading">

                    <div>
                        <span class="section-label">YOUR SELECTION</span>
                        <h2>Shopping Cart</h2>
                        <p>
                            <?php echo $totalItems; ?>
                            <?php echo $totalItems == 1 ? 'item' : 'items'; ?>
                            in your cart
                        </p>
                    </div>

                    <a href="products.php" class="continue-shopping">
                        ← Continue Shopping
                    </a>

                </div>

                <div class="cart-layout">

                    <div class="cart-items">

                        <?php foreach ($cartItems as $item): ?>

                            <article class="cart-item">

                                <div class="cart-product-image">

                                    <a href="product-details.php?id=<?php echo $item['product_id']; ?>">

                                        <?php if (!empty($item['image'])): ?>

                                            <img
                                                src="assets/images/<?php echo htmlspecialchars($item['image']); ?>"
                                                alt="<?php echo htmlspecialchars($item['name']); ?>"
                                            >

                                        <?php else: ?>

                                            <div class="image-placeholder">
                                                📦
                                            </div>

                                        <?php endif; ?>

                                    </a>

                                </div>

                                <div class="cart-product-info">

                                    <span class="cart-category">
                                        <?php echo htmlspecialchars($item['category_name']); ?>
                                    </span>

                                    <a
                                        href="product-details.php?id=<?php echo $item['product_id']; ?>"
                                        class="cart-product-name"
                                    >
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </a>

                                    <div class="stock-status">

                                        <?php if ($item['stock'] > 0): ?>

                                            <span class="stock-dot"></span>
                                            In Stock

                                        <?php else: ?>

                                            <span class="stock-dot out"></span>
                                            Out of Stock

                                        <?php endif; ?>

                                    </div>

                                    <div class="cart-price-mobile">
                                        Rs. <?php echo number_format($item['price'], 2); ?>
                                    </div>

                                </div>

                                <div class="cart-product-price">

                                    <span>Unit Price</span>

                                    <strong>
                                        Rs. <?php echo number_format($item['price'], 2); ?>
                                    </strong>

                                </div>

                                <form method="POST" action="cart-update.php" class="quantity-form">

                                    <input
                                        type="hidden"
                                        name="cart_id"
                                        value="<?php echo $item['cart_id']; ?>"
                                    >

                                    <label>Quantity</label>

                                    <div class="quantity-control">

                                        <button
                                            type="button"
                                            onclick="changeQuantity(this, -1)"
                                        >
                                            −
                                        </button>

                                        <input
                                            type="number"
                                            name="quantity"
                                            value="<?php echo $item['quantity']; ?>"
                                            min="1"
                                            max="<?php echo $item['stock']; ?>"
                                        >

                                        <button
                                            type="button"
                                            onclick="changeQuantity(this, 1)"
                                        >
                                            +
                                        </button>

                                    </div>

                                    <button type="submit" class="update-button">
                                        Update
                                    </button>

                                </form>

                                <div class="cart-item-total">

                                    <span>Total</span>

                                    <strong>
                                        Rs.
                                        <?php
                                        echo number_format(
                                            $item['price'] * $item['quantity'],
                                            2
                                        );
                                        ?>
                                    </strong>

                                </div>

                                <a
                                    href="cart-remove.php?id=<?php echo $item['cart_id']; ?>"
                                    class="remove-button"
                                    onclick="return confirm('Remove this product from your cart?');"
                                >
                                    ×
                                </a>

                            </article>

                        <?php endforeach; ?>

                        <div class="cart-security">

                            <div class="security-item">
                                <span>🔒</span>
                                <div>
                                    <strong>Secure Checkout</strong>
                                    <small>Your order information is protected.</small>
                                </div>
                            </div>

                            <div class="security-item">
                                <span>🚚</span>
                                <div>
                                    <strong>Reliable Delivery</strong>
                                    <small>We'll get your order to you safely.</small>
                                </div>
                            </div>

                            <div class="security-item">
                                <span>↩</span>
                                <div>
                                    <strong>Easy Returns</strong>
                                    <small>Simple return process.</small>
                                </div>
                            </div>

                        </div>

                    </div>

                    <aside class="order-summary">

                        <div class="summary-header">
                            <span class="section-label">ORDER SUMMARY</span>
                            <h3>Cart Summary</h3>
                        </div>

                        <div class="summary-row">
                            <span>Subtotal</span>
                            <strong>
                                Rs. <?php echo number_format($subtotal, 2); ?>
                            </strong>
                        </div>

                        <div class="summary-row">
                            <span>Delivery</span>

                            <strong class="<?php echo $deliveryCharge == 0 ? 'free' : ''; ?>">
                                <?php if ($deliveryCharge == 0): ?>
                                    FREE
                                <?php else: ?>
                                    Rs. <?php echo number_format($deliveryCharge, 2); ?>
                                <?php endif; ?>
                            </strong>
                        </div>

                        <?php if ($subtotal > 0 && $subtotal < 5000): ?>

                            <div class="free-delivery-note">

                                <span>🚚</span>

                                <p>
                                    Add
                                    <strong>
                                        Rs. <?php echo number_format(5000 - $subtotal, 2); ?>
                                    </strong>
                                    more for free delivery.
                                </p>

                            </div>

                        <?php elseif ($subtotal >= 5000): ?>

                            <div class="free-delivery-note success">

                                <span>✓</span>

                                <p>
                                    You've unlocked
                                    <strong>FREE delivery!</strong>
                                </p>

                            </div>

                        <?php endif; ?>

                        <div class="summary-divider"></div>

                        <div class="grand-total">

                            <span>Total</span>

                            <strong>
                                Rs. <?php echo number_format($grandTotal, 2); ?>
                            </strong>

                        </div>

                        <a href="checkout.php" class="checkout-button">
                            Proceed to Checkout
                            <span>→</span>
                        </a>

                        <a href="products.php" class="continue-button">
                            Continue Shopping
                        </a>

                        <div class="payment-note">

                            <span>🔐</span>

                            <div>
                                <strong>Safe & Secure</strong>
                                <p>
                                    Your payment and personal information
                                    are handled securely.
                                </p>
                            </div>

                        </div>

                    </aside>

                </div>

            <?php else: ?>

                <div class="empty-cart">

                    <div class="empty-cart-icon">
                        🛒
                    </div>

                    <span class="section-label">YOUR CART IS EMPTY</span>

                    <h2>Nothing Here Yet.</h2>

                    <p>
                        Looks like you haven't added anything to your cart.
                        Let's find something you'll love.
                    </p>

                    <a href="products.php" class="shop-button">
                        Start Shopping →
                    </a>

                </div>

            <?php endif; ?>

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

            <a href="orders.php">My Orders</a>
            <a href="checkout.php">Checkout</a>
            <a href="logout.php">Logout</a>

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

function changeQuantity(button, amount) {

    const form = button.closest(".quantity-form");
    const input = form.querySelector("input[type='number']");

    let value = parseInt(input.value);
    const min = parseInt(input.min);
    const max = parseInt(input.max);

    value += amount;

    if (value < min) {
        value = min;
    }

    if (value > max) {
        value = max;
    }

    input.value = value;
}

</script>

</body>
</html>