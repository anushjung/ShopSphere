
<?php

require_once __DIR__ . "/../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$host = $_ENV["DB_HOST"] ?? "localhost";
$username = $_ENV["DB_USERNAME"] ?? "root";
$password = $_ENV["DB_PASSWORD"] ?? "";
$database = $_ENV["DB_NAME"] ?? "shopsphere";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");

?>
