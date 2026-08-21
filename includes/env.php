<?php

declare(strict_types=1);

/**
 * Load a small .env file for local development while preserving
 * environment variables supplied by the hosting platform in production.
 */
function load_environment(string $path): void
{
    static $loaded = false;

    if ($loaded || !is_file($path) || !is_readable($path)) {
        $loaded = true;
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if ($name === '' || getenv($name) !== false) {
            continue;
        }

        if (strlen($value) >= 2 && (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"))) {
            $value = substr($value, 1, -1);
        }

        putenv($name . '=' . $value);
    }

    $loaded = true;
}

load_environment(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');
