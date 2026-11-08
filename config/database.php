<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$dbname = "pharmacy_db";
$dbuser = "root";
$dbpass = "";

try {
    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Database connection failed. Check config/database.php and ensure MySQL is running.");
}
