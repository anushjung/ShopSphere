
<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $store_name = trim($_POST["store_name"] ?? "");
    $store_email = trim($_POST["store_email"] ?? "");
    $store_phone = trim($_POST["store_phone"] ?? "");
    $store_address = trim($_POST["store_address"] ?? "");

    if ($store_name === "" || !filter_var($store_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid store name and email address.";
    } else {

        $stmt = $conn->prepare("
            UPDATE store_settings
            SET store_name = ?,
                store_email = ?,
                store_phone = ?,
                store_address = ?
            WHERE setting_id = 1
        ");

        $stmt->bind_param(
            "ssss",
            $store_name,
            $store_email,
            $store_phone,
            $store_address
        );

        if ($stmt->execute()) {
            $success = "Store settings updated successfully.";
        } else {
            $error = "Unable to update settings. Please try again.";
        }
    }
}

$result = $conn->query("
    SELECT *
    FROM store_settings
    WHERE setting_id = 1
");

$settings = $result->fetch_assoc();

if (!$settings) {
    exit("Store settings record not found. Please check your database.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Settings | ShopSphere Admin</title>

    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/settings.css">
    <link rel="stylesheet" href="../assets/css/theme.css">

</head>

<body class="admin-body">

    <?php include "includes/sidebar.php"; ?>

    <div class="admin-main">

        <?php include "includes/topbar.php"; ?>

        <main class="settings-content">

            <div class="settings-heading">
                <div>
                    <span class="settings-breadcrumb">Admin / Settings</span>
                    <h1>Settings</h1>
                    <p>Manage your ShopSphere store preferences.</p>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="settings-alert settings-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="settings-alert settings-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <section class="settings-card">

                <div class="settings-card-heading">
                    <h2>Store Information</h2>
                    <p>Update your basic store details.</p>
                </div>

                <form method="POST" class="settings-form">

                    <div class="settings-field">
                        <label for="store_name">Store Name</label>
                        <input
                            type="text"
                            id="store_name"
                            name="store_name"
                            value="<?php echo htmlspecialchars($settings["store_name"]); ?>"
                            required
                        >
                    </div>

                    <div class="settings-field">
                        <label for="store_email">Store Email</label>
                        <input
                            type="email"
                            id="store_email"
                            name="store_email"
                            value="<?php echo htmlspecialchars($settings["store_email"]); ?>"
                            required
                        >
                    </div>

                    <div class="settings-field">
                        <label for="store_phone">Phone Number</label>
                        <input
                            type="text"
                            id="store_phone"
                            name="store_phone"
                            value="<?php echo htmlspecialchars($settings["store_phone"] ?? ""); ?>"
                        >
                    </div>

                    <div class="settings-field settings-full">
                        <label for="store_address">Store Address</label>
                        <textarea
                            id="store_address"
                            name="store_address"
                            rows="4"
                        ><?php echo htmlspecialchars($settings["store_address"] ?? ""); ?></textarea>
                    </div>

                    <div class="settings-actions">
                        <button type="submit" class="settings-save-button">
                            Save Settings
                        </button>
                    </div>

                </form>

            </section>

            <section class="settings-card appearance-card">

                <div class="settings-card-heading">
                    <h2>Appearance</h2>
                    <p>Choose how ShopSphere looks across the website and admin panel.</p>
                </div>

                <div class="appearance-options">

                    <div class="appearance-option">
                        <div>
                            <h3>Light Mode</h3>
                            <p>Use a bright and clean appearance.</p>
                        </div>

                        <button
                            type="button"
                            class="theme-option-button"
                            data-theme-choice="light"
                        >
                            ☀ Light
                        </button>
                    </div>

                    <div class="appearance-option">
                        <div>
                            <h3>Dark Mode</h3>
                            <p>Use a darker appearance that's easier on the eyes.</p>
                        </div>

                        <button
                            type="button"
                            class="theme-option-button"
                            data-theme-choice="dark"
                        >
                            ☾ Dark
                        </button>
                    </div>

                </div>

            </section>

        </main>

    </div>

    <?php include "includes/footer.php"; ?>

    <script src="../assets/js/theme.js"></script>

</body>
</html>
