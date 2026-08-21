<?php

declare(strict_types=1);

$server = getenv('DB_HOST') ?: '127.0.0.1';
$port = (int) (getenv('DB_PORT') ?: 3306);
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME') ?: 'weblogr';

if ($username === false || $password === false) {
    throw new RuntimeException('Database credentials are not configured. Set DB_USER and DB_PASSWORD in the server environment.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $con = new mysqli($server, $username, $password, $dbname, $port);
    $con->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log('Database connection failed: ' . $exception->getMessage());
    throw new RuntimeException('Unable to connect to the database.');
}
