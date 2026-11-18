
<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$search = trim($_GET["search"] ?? "");
$rating = $_GET["rating"] ?? "";

$sql = "
    SELECT
        r.review_id,
        r.rating,
        r.review,
        r.created_at,
        u.name AS user_name,
        p.name AS product_name
    FROM reviews r
    INNER JOIN users u ON r.user_id = u.user_id
    INNER JOIN products p ON r.product_id = p.product_id
    WHERE 1 = 1
";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (u.name LIKE ? OR p.name LIKE ? OR r.review LIKE ?)";
    $keyword = "%" . $search . "%";
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $types .= "sss";
}

if (in_array($rating, ["1", "2", "3", "4", "5"], true)) {
    $sql .= " AND r.rating = ?";
    $params[] = (int)$rating;
    $types .= "i";
}

$sql .= " ORDER BY r.created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$reviews = $stmt->get_result();

$total_reviews = $conn->query("SELECT COUNT(*) AS total FROM reviews")->fetch_assoc()["total"];

$average_rating = $conn->query("
    SELECT COALESCE(ROUND(AVG(rating), 1), 0) AS average
    FROM reviews
")->fetch_assoc()["average"];

$five_star = $conn->query("
    SELECT COUNT(*) AS total FROM reviews WHERE rating = 5
")->fetch_assoc()["total"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reviews Management | ShopSphere Admin</title>

    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/reviews.css">

</head>

<body class="admin-body">

    <?php include "includes/sidebar.php"; ?>

    <div class="admin-main">

        <?php include "includes/topbar.php"; ?>

        <main class="reviews-content">

            <div class="reviews-heading">
                <div>
                    <span class="reviews-breadcrumb">Admin / Reviews</span>
                    <h1>Reviews Management</h1>
                    <p>View and monitor customer product reviews.</p>
                </div>
            </div>

            <section class="reviews-stats">

                <div class="review-stat-card">
                    <span>Total Reviews</span>
                    <h2><?php echo (int)$total_reviews; ?></h2>
                </div>

                <div class="review-stat-card">
                    <span>Average Rating</span>
                    <h2><?php echo htmlspecialchars((string)$average_rating); ?> / 5</h2>
                </div>

                <div class="review-stat-card">
                    <span>Five Star Reviews</span>
                    <h2><?php echo (int)$five_star; ?></h2>
                </div>

            </section>

            <section class="reviews-table-section">

                <div class="reviews-toolbar">
                    <h2>Customer Reviews</h2>

                    <form method="GET" class="reviews-filters">

                        <input
                            type="text"
                            name="search"
                            placeholder="Search customer, product or review..."
                            value="<?php echo htmlspecialchars($search); ?>"
                        >

                        <select name="rating">
                            <option value="">All Ratings</option>
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?php echo $i; ?>"
                                    <?php echo $rating === (string)$i ? "selected" : ""; ?>>
                                    <?php echo $i; ?> Stars
                                </option>
                            <?php endfor; ?>
                        </select>

                        <button type="submit">Filter</button>

                    </form>
                </div>

                <div class="reviews-table-wrapper">

                    <table class="reviews-table">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Date</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($reviews->num_rows > 0): ?>

                                <?php while ($review = $reviews->fetch_assoc()): ?>

                                    <tr>

                                        <td>#<?php echo (int)$review["review_id"]; ?></td>

                                        <td>
                                            <?php echo htmlspecialchars($review["user_name"]); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($review["product_name"]); ?>
                                        </td>

                                        <td>
                                            <span class="review-rating">
                                                <?php echo str_repeat("★", (int)$review["rating"]); ?>
                                                <small><?php echo (int)$review["rating"]; ?>/5</small>
                                            </span>
                                        </td>

                                        <td class="review-text">
                                            <?php echo htmlspecialchars($review["review"] ?: "No written review"); ?>
                                        </td>

                                        <td>
                                            <?php echo date("d M Y", strtotime($review["created_at"])); ?>
                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="6" class="reviews-empty">
                                        No reviews found.
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
