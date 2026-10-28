<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$databaseHost = 'localhost';
$databaseName = 'cspit';
$databaseUser = 'root';
$databasePassword = '';

try {
    $mysqli = new mysqli($databaseHost, $databaseUser, $databasePassword, $databaseName);
    $mysqli->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log('StudentHub MySQLi connection failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('Database connection is unavailable. Import user_registration.sql and check MySQL in XAMPP.');
}
