
<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$user_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$user_id || $user_id < 1) {
    header("Location: users.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT user_id, name, email, phone, address, role, created_at
     FROM users
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: users.php");
    exit;
}

$initial = strtoupper(substr($user["name"], 0, 1));

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Details | ShopSphere Admin</title>

    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/user-details.css">

</head>

<body class="admin-body">

    <?php include "includes/sidebar.php"; ?>

    <div class="admin-main">

        <?php include "includes/topbar.php"; ?>

        <main class="user-details-content">

            <div class="details-heading">

                <div>
                    <span class="details-breadcrumb">Admin / Users / User Details</span>
                    <h1>User Details</h1>
                    <p>View complete registered user information.</p>
                </div>

                <a href="users.php" class="details-back-button">
                    ← Back to Users
                </a>

            </div>

            <section class="user-profile-card">

                <div class="profile-header">

                    <div class="profile-avatar">
                        <?php echo htmlspecialchars($initial); ?>
                    </div>

                    <div class="profile-heading-info">

                        <h2>
                            <?php echo htmlspecialchars($user["name"]); ?>
                        </h2>

                        <p>
                            <?php echo htmlspecialchars($user["email"]); ?>
                        </p>

                        <span class="details-role details-role-<?php echo htmlspecialchars($user["role"]); ?>">
                            <?php echo htmlspecialchars(ucfirst($user["role"])); ?>
                        </span>

                    </div>

                </div>

                <div class="profile-information">

                    <h3>Personal Information</h3>

                    <div class="details-grid">

                        <div class="details-item">
                            <span>User ID</span>
                            <strong>#<?php echo (int)$user["user_id"]; ?></strong>
                        </div>

                        <div class="details-item">
                            <span>Full Name</span>
                            <strong><?php echo htmlspecialchars($user["name"]); ?></strong>
                        </div>

                        <div class="details-item">
                            <span>Email Address</span>
                            <strong><?php echo htmlspecialchars($user["email"]); ?></strong>
                        </div>

                        <div class="details-item">
                            <span>Phone Number</span>
                            <strong><?php echo htmlspecialchars($user["phone"] ?: "Not provided"); ?></strong>
                        </div>

                        <div class="details-item details-full">
                            <span>Address</span>
                            <strong><?php echo nl2br(htmlspecialchars($user["address"] ?: "Not provided")); ?></strong>
                        </div>

                        <div class="details-item">
                            <span>Account Role</span>
                            <strong><?php echo htmlspecialchars(ucfirst($user["role"])); ?></strong>
                        </div>

                        <div class="details-item">
                            <span>Registration Date</span>
                            <strong>
                                <?php echo date("d M Y, h:i A", strtotime($user["created_at"])); ?>
                            </strong>
                        </div>

                    </div>

                </div>

            </section>

        </main>

    </div>

    <?php include "includes/footer.php"; ?>

</body>
</html>
