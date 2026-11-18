<?php

require_once "../includes/db.php";
require_once "../includes/auth.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$totalVisits = 0;
$todayVisits = 0;
$mostVisitedPage = "No data";
$mostVisitedCount = 0;

$totalQuery = $conn->query("
    SELECT COUNT(*) AS total
    FROM visitor_tracking
");

if ($totalQuery) {
    $totalVisits = intval($totalQuery->fetch_assoc()["total"]);
}

$todayQuery = $conn->query("
    SELECT COUNT(*) AS total
    FROM visitor_tracking
    WHERE DATE(visited_at) = CURDATE()
");

if ($todayQuery) {
    $todayVisits = intval($todayQuery->fetch_assoc()["total"]);
}

$popularQuery = $conn->query("
    SELECT page, COUNT(*) AS total
    FROM visitor_tracking
    GROUP BY page
    ORDER BY total DESC
    LIMIT 1
");

if ($popularQuery && $popularQuery->num_rows > 0) {
    $popular = $popularQuery->fetch_assoc();
    $mostVisitedPage = $popular["page"];
    $mostVisitedCount = intval($popular["total"]);
}

$pageStats = $conn->query("
    SELECT
        page,
        COUNT(*) AS visits
    FROM visitor_tracking
    GROUP BY page
    ORDER BY visits DESC
");

$recentVisits = $conn->query("
    SELECT
        page,
        visited_at
    FROM visitor_tracking
    ORDER BY visited_at DESC
    LIMIT 10
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Reports | ShopSphere Admin</title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

    <style>

        .report-content {
            padding: 30px;
        }

        .report-header {
            margin-bottom: 30px;
        }

        .report-header h1 {
            margin: 0 0 8px;
        }

        .report-header p {
            color: #777;
            margin: 0;
        }

        .report-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .report-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
        }

        .report-card span {
            display: block;
            color: #777;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .report-card h2 {
            margin: 0;
            font-size: 30px;
        }

        .report-card p {
            margin: 8px 0 0;
            color: #777;
            word-break: break-word;
        }

        .report-section {
            background: #ffffff;
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
        }

        .report-section h2 {
            margin-top: 0;
            margin-bottom: 20px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table th,
        .report-table td {
            padding: 14px;
            border-bottom: 1px solid #eeeeee;
            text-align: left;
        }

        .report-table th {
            background: #f8f8f8;
        }

        .report-table tr:last-child td {
            border-bottom: none;
        }

        @media (max-width: 900px) {

            .report-cards {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<?php require_once "includes/sidebar.php"; ?>

<div class="admin-main">

    <?php require_once "includes/topbar.php"; ?>

    <main class="report-content">

        <div class="report-header">

            <h1>Reports</h1>

            <p>
                Monitor ShopSphere visitor activity and website performance.
            </p>

        </div>

        <div class="report-cards">

            <div class="report-card">

                <span>Total Visits</span>

                <h2>
                    <?php echo number_format($totalVisits); ?>
                </h2>

                <p>
                    All recorded website visits
                </p>

            </div>

            <div class="report-card">

                <span>Today's Visits</span>

                <h2>
                    <?php echo number_format($todayVisits); ?>
                </h2>

                <p>
                    Visits recorded today
                </p>

            </div>

            <div class="report-card">

                <span>Most Visited Page</span>

                <h2>
                    <?php echo number_format($mostVisitedCount); ?>
                </h2>

                <p>
                    <?php echo htmlspecialchars($mostVisitedPage); ?>
                </p>

            </div>

        </div>

        <section class="report-section">

            <h2>Page Visit Statistics</h2>

            <?php if ($pageStats && $pageStats->num_rows > 0): ?>

                <table class="report-table">

                    <thead>

                        <tr>
                            <th>Page</th>
                            <th>Total Visits</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php while ($row = $pageStats->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars($row["page"]); ?>
                                </td>

                                <td>
                                    <?php echo number_format($row["visits"]); ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <p>No visitor data available yet.</p>

            <?php endif; ?>

        </section>

        <section class="report-section">

            <h2>Recent Visitor Activity</h2>

            <?php if ($recentVisits && $recentVisits->num_rows > 0): ?>

                <table class="report-table">

                    <thead>

                        <tr>
                            <th>Page</th>
                            <th>Visited At</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php while ($row = $recentVisits->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars($row["page"]); ?>
                                </td>

                                <td>
                                    <?php echo date(
                                        "M d, Y h:i A",
                                        strtotime($row["visited_at"])
                                    ); ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <p>No visitor activity available yet.</p>

            <?php endif; ?>

        </section>

    </main>

</div>

</body>

</html>