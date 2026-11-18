<?php

require_once "includes/db.php";

$page = isset($_POST["page"]) ? trim($_POST["page"]) : "";

if ($page !== "") {

    $stmt = $conn->prepare(
        "INSERT INTO visitor_tracking (page) VALUES (?)"
    );

    $stmt->bind_param("s", $page);
    $stmt->execute();

    $stmt->close();
}

$conn->close();

http_response_code(204);
exit;

?>