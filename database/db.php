<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/env.php';

$server = getenv('DB_HOST') ?: '127.0.0.1';
$port = (int) (getenv('DB_PORT') ?: 3306);
$app_env = strtolower((string) (getenv('APP_ENV') ?: 'local'));
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME') ?: 'weblogr';

// XAMPP's default local MySQL account is root with an empty password.
// Production/staging environments must provide DB_USER and DB_PASSWORD explicitly.
if ($app_env !== 'production') {
    $username = $username !== false && $username !== '' ? $username : 'root';
    $password = $password !== false ? $password : '';
}

if ($username === false || $password === false || $username === '' || $dbname === '') {
    throw new RuntimeException('Database configuration is incomplete. Please configure DB_HOST, DB_NAME, DB_USER, and DB_PASSWORD.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $con = new mysqli($server, $username, $password, $dbname, $port);
    $con->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log('Database connection failed: ' . $exception->getMessage());
    throw new RuntimeException('We could not connect to the database. Please make sure MySQL is running and your database settings are correct.');
}
