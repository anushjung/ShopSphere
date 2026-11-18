<?php

require_once "includes/db.php";
require_once "includes/mailer.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO newsletter_subscribers (email) VALUES (?)"
        );

        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {

            $subject = "Welcome to ShopSphere Newsletter";

            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 30px;'>
                <h2 style='color: #ff6b00;'>Welcome to ShopSphere! 🛍️</h2>

                <p>Thank you for subscribing to the ShopSphere newsletter.</p>

                <p>You will receive:</p>

                <ul>
                    <li>Latest product updates</li>
                    <li>Special offers and discounts</li>
                    <li>New arrivals</li>
                    <li>Exclusive ShopSphere promotions</li>
                </ul>

                <p>Stay connected and keep shopping with ShopSphere!</p>

                <p style='margin-top: 30px;'>
                    <strong>ShopSphere</strong><br>
                    Your World of Shopping, One Sphere.
                </p>
            </div>
            ";

            sendEmail(
                $email,
                "ShopSphere Customer",
                $subject,
                $body
            );

            $message = "Successfully subscribed! A welcome email has been sent to your email address.";
            $messageType = "success";

        } else {

            if ($conn->errno === 1062) {
                $message = "This email is already subscribed.";
            } else {
                $message = "Something went wrong. Please try again.";
            }

            $messageType = "error";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Newsletter | ShopSphere</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
</head>

<body>

<?php require_once "includes/navbar.php"; ?>

<div style="max-width: 600px; margin: 80px auto; padding: 40px; text-align: center;">

    <h1>Subscribe to ShopSphere Newsletter 📧</h1>

    <p>
        Get the latest products, special offers, discounts,
        and exclusive updates directly in your inbox.
    </p>

    <?php if ($message !== ""): ?>

        <div style="
            margin: 25px 0;
            padding: 15px;
            border-radius: 8px;
            background: <?= $messageType === "success" ? "#0d2312" : "#22070a" ?>;
            color: <?= $messageType === "success" ? "#155724" : "#721c24" ?>;
        ">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <input
            type="email"
            name="email"
            placeholder="Enter your email address"
            required
            style="
                width: 100%;
                padding: 14px;
                margin-bottom: 15px;
                border: 1px solid #301a1a;
                border-radius: 8px;
                font-size: 16px;
                box-sizing: border-box;
            "
        >

        <button
            type="submit"
            style="
                width: 100%;
                padding: 14px;
                border: none;
                border-radius: 8px;
                background: #ff6b00;
                color: white;
                font-size: 16px;
                font-weight: bold;
                cursor: pointer;
            "
        >
            Subscribe
        </button>

    </form>

</div>

</body>
</html>