<?php

require_once "includes/db.php";
require_once "includes/auth.php";

$message = "";

if (isset($_GET["registered"])) {
    $message = "Registration successful. Please login.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT user_id, name, email, password, role
         FROM users
         WHERE email = ?"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {

            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = $user["role"];

            if ($user["role"] === "admin") {

                header("Location: admin/dashboard.php");

            } elseif ($user["role"] === "seller") {

                header("Location: seller/dashboard.php");

            } else {

                header("Location: index.php");

            }

            exit;

        } else {

            $message = "Invalid email or password.";

        }

    } else {

        $message = "Invalid email or password.";

    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - ShopSphere</title>

    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/login.css">

    <script src="assets/js/navbar.js" defer></script>

</head>

<body>

<?php include "includes/navbar.php"; ?>


<main class="login-page">

    <div class="login-background">

        <div class="glow glow-one"></div>
        <div class="glow glow-two"></div>
        <div class="floating-circle circle-one"></div>
        <div class="floating-circle circle-two"></div>

    </div>


    <section class="login-container">

        <div class="login-showcase">

            <div class="showcase-content">

                <div class="showcase-logo">
                    <img src="assets/images/shopsphere-logo.png" alt="ShopSphere">
                </div>

                <span class="showcase-badge">
                    ✨ Welcome Back
                </span>

                <h1>
                    Your shopping world
                    <span>awaits.</span>
                </h1>

                <p>
                    Sign in to continue exploring products,
                    manage your orders and enjoy a smarter shopping experience.
                </p>

                <div class="showcase-features">

                    <div class="showcase-feature">
                        <span>🛍️</span>
                        <div>
                            <strong>Easy Shopping</strong>
                            <small>Discover products you love</small>
                        </div>
                    </div>

                    <div class="showcase-feature">
                        <span>🚚</span>
                        <div>
                            <strong>Track Orders</strong>
                            <small>Stay updated from checkout to delivery</small>
                        </div>
                    </div>

                    <div class="showcase-feature">
                        <span>🔒</span>
                        <div>
                            <strong>Secure Account</strong>
                            <small>Your account stays protected</small>
                        </div>
                    </div>

                </div>

            </div>

        </div>


        <div class="login-card">

            <div class="login-card-header">

                <div class="login-icon">
                    👤
                </div>

                <div>
                    <span class="login-mini-title">SHOPSPHERE ACCOUNT</span>

                    <h2>Welcome Back</h2>

                    <p>
                        Login to continue shopping.
                    </p>
                </div>

            </div>


            <?php if ($message !== ""): ?>

                <div class="login-message">
                    <span>✓</span>

                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php endif; ?>


            <form method="POST" class="login-form">

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
                            placeholder="Enter your email"
                            autocomplete="email"
                            required
                        >

                    </div>

                </div>


                <div class="input-group">

                    <div class="password-label">

                        <label for="password">
                            Password
                        </label>

                        <a href="#">
                            Forgot Password?
                        </a>

                    </div>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            🔒
                        </span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
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


                <label class="remember-me">

                    <input type="checkbox" name="remember">

                    <span class="checkmark"></span>

                    <span>Remember me</span>

                </label>


                <button type="submit" class="login-btn">

                    <span>Login to ShopSphere</span>

                    <span class="login-arrow">→</span>

                </button>

            </form>


            <div class="login-divider">

                <span>or</span>

            </div>


            <div class="register-prompt">

                <span>New to ShopSphere?</span>

                <a href="register.php">
                    Create an Account →
                </a>

            </div>


            <div class="login-security">

                <span>🔐</span>

                <p>
                    Your information is securely protected.
                </p>

            </div>

        </div>

    </section>

</main>


<footer class="login-footer">

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
const passwordToggle = document.getElementById("passwordToggle");

passwordToggle.addEventListener("click", function () {

    if (password.type === "password") {

        password.type = "text";
        passwordToggle.textContent = "🙈";

    } else {

        password.type = "password";
        passwordToggle.textContent = "👁️";

    }

});

</script>

</body>

</html>
