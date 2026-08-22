<?php

declare(strict_types=1);

function weblogr_env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    if ($value !== false) return trim($value);
    static $loaded = false;
    static $values = [];
    if (!$loaded) {
        $loaded = true;
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
        if (is_readable($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
                [$name, $raw] = explode('=', $line, 2);
                $name = trim($name);
                $raw = trim($raw);
                if (strlen($raw) >= 2 && (($raw[0] === '"' && substr($raw, -1) === '"') || ($raw[0] === "'" && substr($raw, -1) === "'"))) $raw = substr($raw, 1, -1);
                $values[$name] = $raw;
            }
        }
    }
    return array_key_exists($key, $values) ? $values[$key] : $default;
}

function google_oauth_config(): array
{
    return [
        'client_id' => weblogr_env('GOOGLE_CLIENT_ID', ''),
        'client_secret' => weblogr_env('GOOGLE_CLIENT_SECRET', ''),
        'redirect_uri' => weblogr_env('GOOGLE_REDIRECT_URI', ''),
    ];
}

function google_oauth_configured(): bool
{
    $config = google_oauth_config();
    return $config['client_id'] !== '' && $config['client_secret'] !== '' && $config['redirect_uri'] !== '';
}

function google_oauth_base64url(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function google_oauth_code_challenge(string $verifier): string
{
    return google_oauth_base64url(hash('sha256', $verifier, true));
}
