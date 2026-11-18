
<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$search = trim($_GET["search"] ?? "");
$role = $_GET["role"] ?? "";

$sql = "SELECT user_id, name, email, phone, address, role, created_at
        FROM users
        WHERE 1=1";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $keyword = "%" . $search . "%";

    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;

    $types .= "sss";
}

if (in_array($role, ["customer", "seller", "admin", "delivery"], true)) {
    $sql .= " AND role = ?";
    $params[] = $role;
    $types .= "s";
}

$sql .= " ORDER BY user_id DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$users = $stmt->get_result();

$total_users = $conn->query(
    "SELECT COUNT(*) AS total FROM users"
)->fetch_assoc()["total"];

$total_customers = $conn->query(
    "SELECT COUNT(*) AS total FROM users WHERE role = 'customer'"
)->fetch_assoc()["total"];

$total_sellers = $conn->query(
    "SELECT COUNT(*) AS total FROM users WHERE role = 'seller'"
)->fetch_assoc()["total"];

$total_delivery = $conn->query(
    "SELECT COUNT(*) AS total FROM users WHERE role = 'delivery'"
)->fetch_assoc()["total"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Users Management | ShopSphere Admin</title>

    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/users.css">

</head>

<body class="admin-body">

    <?php include "includes/sidebar.php"; ?>

    <div class="admin-main">

        <?php include "includes/topbar.php"; ?>

        <main class="users-content">

            <div class="users-heading">
                <div>
                    <span class="users-breadcrumb">Admin / Users</span>
                    <h1>Users Management</h1>
                    <p>View and manage all registered ShopSphere users.</p>
                </div>
            </div>

            <section class="users-stat-grid">

                <div class="users-stat-card">
                    <div class="users-stat-icon orange">👥</div>
                    <div>
                        <span>Total Users</span>
                        <h2><?php echo $total_users; ?></h2>
                    </div>
                </div>

                <div class="users-stat-card">
                    <div class="users-stat-icon blue">🛍️</div>
                    <div>
                        <span>Customers</span>
                        <h2><?php echo $total_customers; ?></h2>
                    </div>
                </div>

                <div class="users-stat-card">
                    <div class="users-stat-icon green">🏪</div>
                    <div>
                        <span>Sellers</span>
                        <h2><?php echo $total_sellers; ?></h2>
                    </div>
                </div>

                <div class="users-stat-card">
                    <div class="users-stat-icon purple">🚚</div>
                    <div>
                        <span>Delivery Personnel</span>
                        <h2><?php echo $total_delivery; ?></h2>
                    </div>
                </div>

            </section>

            <section class="users-panel">

                <div class="users-panel-header">
                    <div>
                        <h2>Registered Users</h2>
                        <p>All accounts registered in ShopSphere.</p>
                    </div>

                    <span class="users-total-badge">
                        <?php echo $users->num_rows; ?> Results
                    </span>
                </div>

                <form method="GET" class="users-filter-form">

                    <div class="users-search-box">
                        <span>⌕</span>
                        <input
                            type="text"
                            name="search"
                            placeholder="Search name, email or phone..."
                            value="<?php echo htmlspecialchars($search); ?>"
                        >
                    </div>

                    <select name="role">
                        <option value="">All Roles</option>
                        <option value="customer" <?php echo $role === "customer" ? "selected" : ""; ?>>Customer</option>
                        <option value="seller" <?php echo $role === "seller" ? "selected" : ""; ?>>Seller</option>
                        <option value="admin" <?php echo $role === "admin" ? "selected" : ""; ?>>Admin</option>
                        <option value="delivery" <?php echo $role === "delivery" ? "selected" : ""; ?>>Delivery</option>
                    </select>

                    <button type="submit" class="users-search-button">
                        Search
                    </button>

                    <a href="users.php" class="users-clear-button">
                        Clear
                    </a>

                </form>

                <div class="users-table-wrapper">

                    <table class="users-table">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User Information</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Registered Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($users->num_rows > 0): ?>

                                <?php while ($user = $users->fetch_assoc()): ?>

                                    <tr>

                                        <td>
                                            <span class="users-id">
                                                #<?php echo $user["user_id"]; ?>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="users-user-info">
                                                <div class="users-avatar">
                                                    <?php echo strtoupper(substr($user["name"], 0, 1)); ?>
                                                </div>

                                                <div>
                                                    <strong>
                                                        <?php echo htmlspecialchars($user["name"]); ?>
                                                    </strong>

                                                    <span>
                                                        <?php echo htmlspecialchars($user["email"]); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($user["phone"] ?? "—"); ?>
                                        </td>

                                        <td>
                                            <span class="users-role users-role-<?php echo htmlspecialchars($user["role"]); ?>">
                                                <?php echo htmlspecialchars(ucfirst($user["role"])); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?php echo date("d M Y", strtotime($user["created_at"])); ?>
                                        </td>

                                        <td>
                                            <a href="user-details.php?id=<?php echo (int)$user['user_id']; ?>"class="users-view-button">View Details</a>
                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="6" class="users-empty">
                                        <div>👥</div>
                                        <strong>No users found</strong>
                                        <p>Try changing your search or role filter.</p>
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>

        </main>

    </div>

    <?php include "includes/footer.php"; ?>

</body>
</html>
