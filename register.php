<?php

require_once "includes/db.php";
require_once "includes/mailer.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);
    $password = $_POST["password"];

    if ($name === "" || $email === "" || $password === "") {

        $message = "Please fill in all required fields.";

    } else {

        $check = $conn->prepare(
            "SELECT user_id FROM users WHERE email = ?"
        );

        $check->bind_param("s", $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "An account with this email already exists.";

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "INSERT INTO users
                (name, email, password, phone, address)
                VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssss",
                $name,
                $email,
                $hashedPassword,
                $phone,
                $address
            );

            if ($stmt->execute()) {

                $subject = "Welcome to ShopSphere!";

                $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 30px; background: #f5f7fb;'>
                    <div style='background: #111827; padding: 25px; text-align: center;'>
                        <h1 style='color: #ffffff; margin: 0;'>ShopSphere</h1>
                        <p style='color: #f97316; margin: 8px 0 0;'>Your World of Shopping, One Sphere.</p>
                    </div>

                    <div style='background: #ffffff; padding: 30px;'>
                        <h2 style='color: #111827;'>Welcome, " . htmlspecialchars($name) . "! 🎉</h2>

                        <p style='color: #4b5563; font-size: 15px;'>
                            Thank you for creating your ShopSphere account.
                        </p>

                        <p style='color: #4b5563; font-size: 15px;'>
                            Your account has been successfully created. You can now explore products,
                            add items to your cart, create your wishlist and place orders.
                        </p>

                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='http://localhost/ShopSphere/login.php'
                               style='background: #f97316; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 8px; display: inline-block;'>
                                Login to ShopSphere
                            </a>
                        </div>

                        <p style='color: #6b7280; font-size: 13px;'>
                            If you did not create this account, please ignore this email.
                        </p>
                    </div>

                    <div style='text-align: center; padding: 20px;'>
                        <p style='color: #9ca3af; font-size: 12px;'>
                            © " . date("Y") . " ShopSphere. All rights reserved.
                        </p>
                    </div>
                </div>
                ";

                sendEmail(
                    $email,
                    $name,
                    $subject,
                    $body
                );

                header("Location: login.php?registered=1");
                exit;

            } else {

                $message = "Registration failed. Please try again.";

            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account - ShopSphere</title>

    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/register.css">

    <script src="assets/js/navbar.js" defer></script>

</head>

<body>

<?php include "includes/navbar.php"; ?>


<main class="register-page">

    <div class="register-background">

        <div class="register-glow glow-one"></div>
        <div class="register-glow glow-two"></div>

        <div class="register-circle circle-one"></div>
        <div class="register-circle circle-two"></div>

    </div>


    <section class="register-container">

        <div class="register-showcase">

            <div class="register-showcase-content">

                <div class="register-logo">

                    <img
                        src="assets/images/shopsphere-logo.png"
                        alt="ShopSphere"
                    >

                </div>


                <span class="register-badge">
                    ✨ Join ShopSphere
                </span>


                <h1>
                    Start your
                    <span>shopping journey.</span>
                </h1>


                <p>
                    Create your ShopSphere account and unlock
                    a smarter, simpler and more enjoyable way to shop.
                </p>


                <div class="register-benefits">

                    <div class="benefit-item">

                        <div class="benefit-icon">
                            🛍️
                        </div>

                        <div>
                            <strong>Thousands of Products</strong>
                            <small>Explore products across multiple categories</small>
                        </div>

                    </div>


                    <div class="benefit-item">

                        <div class="benefit-icon">
                            ❤️
                        </div>

                        <div>
                            <strong>Wishlist & Cart</strong>
                            <small>Save products and shop whenever you want</small>
                        </div>

                    </div>


                    <div class="benefit-item">

                        <div class="benefit-icon">
                            🚚
                        </div>

                        <div>
                            <strong>Easy Order Tracking</strong>
                            <small>Follow your order from purchase to delivery</small>
                        </div>

                    </div>


                    <div class="benefit-item">

                        <div class="benefit-icon">
                            🔐
                        </div>

                        <div>
                            <strong>Secure Account</strong>
                            <small>Your account information stays protected</small>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="register-card">

            <div class="register-card-header">

                <div class="register-icon">
                    👤
                </div>

                <div>

                    <span class="register-mini-title">
                        SHOPSPHERE ACCOUNT
                    </span>

                    <h2>Create Account</h2>

                    <p>
                        Join us and start shopping today.
                    </p>

                </div>

            </div>


            <?php if ($message !== ""): ?>

                <div class="register-message">

                    <span>!</span>

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <form method="POST" class="register-form" id="registerForm">


                <div class="form-row">

                    <div class="input-group">

                        <label for="name">
                            Full Name
                        </label>

                        <div class="input-wrapper">

                            <span class="input-icon">
                                👤
                            </span>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                placeholder="Your full name"
                                value="<?php echo isset($_POST["name"]) ? htmlspecialchars($_POST["name"]) : ""; ?>"
                                required
                            >

                        </div>

                    </div>


                    <div class="input-group">

                        <label for="email">
                            Email Address
                        </label>

                        <div class="input-wrapper">

                            <span class="input-icon">
                                ✉️
                            </span>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Your email"
                                value="<?php echo isset($_POST["email"]) ? htmlspecialchars($_POST["email"]) : ""; ?>"
                                required
                            >

                        </div>

                    </div>

                </div>


                <div class="form-row">

                    <div class="input-group">

                        <label for="phone">
                            Phone Number
                        </label>

                        <div class="input-wrapper">

                            <span class="input-icon">
                                📱
                            </span>

                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                placeholder="Your phone number"
                                value="<?php echo isset($_POST["phone"]) ? htmlspecialchars($_POST["phone"]) : ""; ?>"
                            >

                        </div>

                    </div>


                    <div class="input-group">

                        <label for="password">
                            Password
                        </label>

                        <div class="input-wrapper">

                            <span class="input-icon">
                                🔒
                            </span>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Create a password"
                                required
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                id="passwordToggle"
                            >
                                👁️
                            </button>

                        </div>

                    </div>

                </div>


                <div class="input-group">

                    <label for="address">
                        Delivery Address
                    </label>

                    <div class="input-wrapper textarea-wrapper">

                        <span class="input-icon textarea-icon">
                            📍
                        </span>

                        <textarea
                            id="address"
                            name="address"
                            placeholder="Enter your delivery address"
                        ><?php echo isset($_POST["address"]) ? htmlspecialchars($_POST["address"]) : ""; ?></textarea>

                    </div>

                </div>


                <div class="input-group">

                    <label for="confirmPassword">
                        Confirm Password
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            🔐
                        </span>

                        <input
                            type="password"
                            id="confirmPassword"
                            placeholder="Confirm your password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            id="confirmPasswordToggle"
                        >
                            👁️
                        </button>

                    </div>

                    <span class="password-error" id="passwordError">
                        Passwords do not match.
                    </span>

                </div>


                <label class="terms-check">

                    <input
                        type="checkbox"
                        id="terms"
                        required
                    >

                    <span class="terms-box"></span>

                    <span>
                        I agree to the ShopSphere
                        <a href="#">Terms & Conditions</a>
                    </span>

                </label>


                <button type="submit" class="register-btn">

                    <span>Create My Account</span>

                    <span class="register-arrow">
                        →
                    </span>

                </button>

            </form>


            <div class="register-divider">
                <span>Already registered?</span>
            </div>


            <div class="login-prompt">

                <span>Already have a ShopSphere account?</span>

                <a href="login.php">
                    Login Now →
                </a>

            </div>


            <div class="register-security">

                <span>🔐</span>

                <p>
                    Your information is securely protected.
                </p>

            </div>

        </div>

    </section>

</main>


<footer class="register-footer">

    <div class="footer-brand">

        <img
            src="assets/images/shopsphere-logo.png"
            alt="ShopSphere"
        >

    </div>

    <p>
        Your World of Shopping, One Sphere.
    </p>

    <div class="footer-links">

        <a href="index.php">Home</a>
        <a href="products.php">Products</a>
        <a href="help-support.php">Help & Support</a>

    </div>

    <span class="footer-copy">
        © <?php echo date("Y"); ?> ShopSphere. All rights reserved.
    </span>

</footer>


<script>

const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirmPassword");

const passwordToggle = document.getElementById("passwordToggle");
const confirmPasswordToggle = document.getElementById("confirmPasswordToggle");

const passwordError = document.getElementById("passwordError");
const registerForm = document.getElementById("registerForm");


passwordToggle.addEventListener("click", function () {

    if (password.type === "password") {

        password.type = "text";
        passwordToggle.textContent = "🙈";

    } else {

        password.type = "password";
        passwordToggle.textContent = "👁️";

    }

});


confirmPasswordToggle.addEventListener("click", function () {

    if (confirmPassword.type === "password") {

        confirmPassword.type = "text";
        confirmPasswordToggle.textContent = "🙈";

    } else {

        confirmPassword.type = "password";
        confirmPasswordToggle.textContent = "👁️";

    }

});


confirmPassword.addEventListener("input", function () {

    if (confirmPassword.value === "") {

        passwordError.style.display = "none";
        confirmPassword.classList.remove("password-invalid");
        return;

    }

    if (password.value !== confirmPassword.value) {

        passwordError.style.display = "block";
        confirmPassword.classList.add("password-invalid");

    } else {

        passwordError.style.display = "none";
        confirmPassword.classList.remove("password-invalid");

    }

});


registerForm.addEventListener("submit", function (event) {

    if (password.value !== confirmPassword.value) {

        event.preventDefault();

        passwordError.style.display = "block";
        confirmPassword.classList.add("password-invalid");

        confirmPassword.focus();

    }

});

</script>

</body>

</html>