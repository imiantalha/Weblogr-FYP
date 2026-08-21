<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

try {
    require_once __DIR__ . '/database/db.php';
    $con->query('SELECT 1');
    $con->close();

    http_response_code(200);
    echo json_encode([
        'status' => 'ok',
        'service' => 'weblogr',
    ], JSON_UNESCAPED_SLASHES);
} catch (Throwable $exception) {
    error_log('Health check failed: ' . $exception->getMessage());
    http_response_code(503);
    echo json_encode([
        'status' => 'degraded',
        'service' => 'weblogr',
    ], JSON_UNESCAPED_SLASHES);
}
