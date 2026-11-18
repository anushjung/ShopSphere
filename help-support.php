<?php

require_once "includes/db.php";
require_once "includes/auth.php";

requireLogin();

$user_name = $_SESSION["name"] ?? "Customer";

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Help & Support - ShopSphere</title>

<link rel="stylesheet" href="assets/css/help-support.css">

</head>

<body>

<header class="navbar">

    <a href="index.php" class="logo">
        🛍️ Shop<span>Sphere</span>
    </a>

    <nav>
        <a href="index.php">Home</a>
        <a href="products.php">Products</a>
        <a href="orders.php">My Orders</a>
    </nav>

    <div class="nav-actions">

        <a href="wishlist.php" class="nav-icon">♡</a>

        <a href="cart.php" class="nav-icon">🛒</a>

        <a href="logout.php" class="logout-btn">
            Logout
        </a>

    </div>

</header>


<main class="support-page">


    <section class="support-hero">

        <div class="hero-content">

            <span class="eyebrow">
                SHOPSPHERE SUPPORT
            </span>

            <h1>
                How can we help you?
            </h1>

            <p>
                Find answers, get help with your orders,
                or reach out to our support team.
            </p>

            <div class="search-box">

                <span>🔍</span>

                <input
                    type="text"
                    id="faqSearch"
                    placeholder="Search for help..."
                    onkeyup="searchFAQ()"
                >

            </div>

        </div>


        <div class="hero-visual">

            <div class="support-circle">
                💬
            </div>

        </div>

    </section>


    <section class="support-categories">

        <div class="section-heading">

            <span>
                SUPPORT CENTER
            </span>

            <h2>
                What do you need help with?
            </h2>

        </div>


        <div class="category-grid">

            <a href="#orders" class="support-category">

                <div class="category-icon">
                    📦
                </div>

                <div>
                    <h3>Orders & Delivery</h3>

                    <p>
                        Track orders and delivery information
                    </p>
                </div>

                <span class="category-arrow">
                    →
                </span>

            </a>


            <a href="#payment" class="support-category">

                <div class="category-icon">
                    💳
                </div>

                <div>
                    <h3>Payment</h3>

                    <p>
                        Payment methods and billing questions
                    </p>
                </div>

                <span class="category-arrow">
                    →
                </span>

            </a>


            <a href="#returns" class="support-category">

                <div class="category-icon">
                    🔄
                </div>

                <div>
                    <h3>Returns & Refunds</h3>

                    <p>
                        Learn about returns and refunds
                    </p>
                </div>

                <span class="category-arrow">
                    →
                </span>

            </a>


            <a href="#account" class="support-category">

                <div class="category-icon">
                    👤
                </div>

                <div>
                    <h3>Account</h3>

                    <p>
                        Manage your ShopSphere account
                    </p>
                </div>

                <span class="category-arrow">
                    →
                </span>

            </a>

        </div>

    </section>


    <section class="faq-section">

        <div class="faq-heading">

            <span>
                FREQUENTLY ASKED QUESTIONS
            </span>

            <h2>
                Common Questions
            </h2>

            <p>
                Quick answers to some of the most common
                ShopSphere questions.
            </p>

        </div>


        <div class="faq-list" id="faqList">


            <div class="faq-item" id="orders">

                <button class="faq-question" onclick="toggleFAQ(this)">

                    <span>
                        How can I track my order?
                    </span>

                    <strong>
                        +
                    </strong>

                </button>

                <div class="faq-answer">

                    <p>
                        Go to <strong>My Orders</strong> from the
                        navigation menu and select the order you
                        want to track. You can view its current
                        status and delivery progress from the
                        Order Details page.
                    </p>

                </div>

            </div>


            <div class="faq-item">

                <button class="faq-question" onclick="toggleFAQ(this)">

                    <span>
                        What payment methods are available?
                    </span>

                    <strong>
                        +
                    </strong>

                </button>

                <div class="faq-answer">

                    <p>
                        ShopSphere currently supports
                        <strong>Cash on Delivery</strong> and
                        <strong>Online Payment</strong>.
                    </p>

                </div>

            </div>


            <div class="faq-item" id="payment">

                <button class="faq-question" onclick="toggleFAQ(this)">

                    <span>
                        How much does delivery cost?
                    </span>

                    <strong>
                        +
                    </strong>

                </button>

                <div class="faq-answer">

                    <p>
                        Delivery is currently
                        <strong>free</strong> on ShopSphere orders.
                    </p>

                </div>

            </div>


            <div class="faq-item" id="returns">

                <button class="faq-question" onclick="toggleFAQ(this)">

                    <span>
                        Can I return a product?
                    </span>

                    <strong>
                        +
                    </strong>

                </button>

                <div class="faq-answer">

                    <p>
                        Return eligibility depends on the product
                        and its condition. Contact ShopSphere support
                        with your order number for assistance with
                        a return request.
                    </p>

                </div>

            </div>


            <div class="faq-item">

                <button class="faq-question" onclick="toggleFAQ(this)">

                    <span>
                        How long does delivery take?
                    </span>

                    <strong>
                        +
                    </strong>

                </button>

                <div class="faq-answer">

                    <p>
                        Delivery time can vary depending on your
                        location and the product. You can monitor
                        the order status from your Order Details page.
                    </p>

                </div>

            </div>


            <div class="faq-item" id="account">

                <button class="faq-question" onclick="toggleFAQ(this)">

                    <span>
                        I forgot my account password. What should I do?
                    </span>

                    <strong>
                        +
                    </strong>

                </button>

                <div class="faq-answer">

                    <p>
                        Contact ShopSphere support for assistance
                        with recovering access to your account.
                    </p>

                </div>

            </div>


            <div class="faq-item">

                <button class="faq-question" onclick="toggleFAQ(this)">

                    <span>
                        Can I cancel my order?
                    </span>

                    <strong>
                        +
                    </strong>

                </button>

                <div class="faq-answer">

                    <p>
                        Order cancellation depends on the current
                        order status. Contact support as soon as
                        possible if you need to cancel an order.
                    </p>

                </div>

            </div>


            <div class="faq-item">

                <button class="faq-question" onclick="toggleFAQ(this)">

                    <span>
                        How can I contact ShopSphere support?
                    </span>

                    <strong>
                        +
                    </strong>

                </button>

                <div class="faq-answer">

                    <p>
                        You can reach our support team using the
                        contact options provided below.
                    </p>

                </div>

            </div>


        </div>


        <div class="no-results" id="noResults">

            <div>
                🔎
            </div>

            <h3>
                No matching questions
            </h3>

            <p>
                Try searching with a different keyword.
            </p>

        </div>

    </section>


    <section class="contact-section">

        <div class="contact-header">

            <span>
                STILL NEED HELP?
            </span>

            <h2>
                We're here for you 💬
            </h2>

            <p>
                Can't find what you're looking for?
                Get in touch with the ShopSphere support team.
            </p>

        </div>


        <div class="contact-grid">


            <div class="contact-card">

                <div class="contact-icon">
                    📧
                </div>

                <h3>
                    Email Support
                </h3>

                <p>
                    Send us your questions and we'll
                    get back to you.
                </p>

                <a href="mailto:support@shopsphere.com">
                    support@shopsphere.com
                </a>

            </div>


            <div class="contact-card">

                <div class="contact-icon">
                    📦
                </div>

                <h3>
                    Order Support
                </h3>

                <p>
                    Need help with a specific order?
                    Check your order details first.
                </p>

                <a href="orders.php">
                    View My Orders →
                </a>

            </div>


            <div class="contact-card">

                <div class="contact-icon">
                    🛍️
                </div>

                <h3>
                    Shopping Help
                </h3>

                <p>
                    Need help finding the right product?
                    Explore our product collection.
                </p>

                <a href="products.php">
                    Browse Products →
                </a>

            </div>

        </div>

    </section>


    <section class="support-cta">

        <div>

            <span>
                SHOPSPHERE CUSTOMER CARE
            </span>

            <h2>
                Your shopping experience matters to us.
            </h2>

            <p>
                We're committed to making your ShopSphere
                experience simple, secure, and enjoyable.
            </p>

        </div>

        <a href="products.php">
            Continue Shopping →
        </a>

    </section>


</main>


<footer>

    <div class="footer-content">

        <div>

            <div class="footer-logo">
                🛍️ Shop<span>Sphere</span>
            </div>

            <p>
                Your World of Shopping, One Sphere.
            </p>

        </div>


        <div class="footer-links">

            <a href="index.php">Home</a>
            <a href="products.php">Products</a>
            <a href="orders.php">My Orders</a>
            <a href="wishlist.php">Wishlist</a>
            <a href="cart.php">Cart</a>

        </div>

    </div>


    <div class="copyright">
        © 2026 ShopSphere. All rights reserved.
    </div>

</footer>


<script>

function toggleFAQ(button) {

    const item = button.parentElement;

    const isOpen = item.classList.contains("open");

    document.querySelectorAll(".faq-item").forEach(function(faq) {
        faq.classList.remove("open");
    });

    if (!isOpen) {
        item.classList.add("open");
    }

}


function searchFAQ() {

    const search = document
        .getElementById("faqSearch")
        .value
        .toLowerCase()
        .trim();

    const items = document.querySelectorAll(".faq-item");

    let found = false;

    items.forEach(function(item) {

        const text = item.innerText.toLowerCase();

        if (text.includes(search)) {

            item.style.display = "block";
            found = true;

        } else {

            item.style.display = "none";

        }

    });

    document.getElementById("noResults").style.display =
        found ? "none" : "block";

}

</script>

</body>
</html>