<?php

declare(strict_types=1);

function weblogr_env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    if ($value !== false) return trim($value);
    static $loaded = false; static $values = [];
    if (!$loaded) {
        $loaded = true; $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
        if (is_readable($file)) foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line); if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
            [$name, $raw] = explode('=', $line, 2); $name = trim($name); $raw = trim($raw);
            if (strlen($raw) >= 2 && (($raw[0] === '"' && substr($raw, -1) === '"') || ($raw[0] === "'" && substr($raw, -1) === "'"))) $raw = substr($raw, 1, -1);
            $values[$name] = $raw;
        }
    }
    return array_key_exists($key, $values) ? $values[$key] : $default;
}

function google_oauth_config(): array
{
    return ['client_id' => weblogr_env('GOOGLE_CLIENT_ID', ''), 'client_secret' => weblogr_env('GOOGLE_CLIENT_SECRET', ''), 'redirect_uri' => weblogr_env('GOOGLE_REDIRECT_URI', '')];
}

function google_oauth_configured(): bool
{
    $config = google_oauth_config(); return $config['client_id'] !== '' && $config['client_secret'] !== '' && $config['redirect_uri'] !== '';
}

function google_oauth_base64url(string $value): string { return rtrim(strtr(base64_encode($value), '+/', '-_'), '='); }
function google_oauth_code_challenge(string $verifier): string { return google_oauth_base64url(hash('sha256', $verifier, true)); }

function google_oauth_http(string $url, array $post = [], array $headers = ['Accept: application/json']): array
{
    if (!function_exists('curl_init')) throw new RuntimeException('PHP cURL is required for Google authentication.');
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => $post !== [], CURLOPT_POSTFIELDS => $post !== [] ? http_build_query($post, '', '&', PHP_QUERY_RFC3986) : null, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 10, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_HTTPHEADER => $headers]);
    $body = curl_exec($ch); $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE); $error = curl_error($ch); curl_close($ch);
    if ($body === false || $error !== '') throw new RuntimeException('Unable to contact Google authentication service.');
    $json = json_decode((string)$body, true);
    if (!is_array($json)) throw new RuntimeException('Google returned an invalid authentication response.');
    return [$status, $json];
}

function google_oauth_authorization_url(array $config, string $state, string $codeChallenge): string
{
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query(['client_id' => $config['client_id'], 'redirect_uri' => $config['redirect_uri'], 'response_type' => 'code', 'scope' => 'openid email profile', 'state' => $state, 'code_challenge' => $codeChallenge, 'code_challenge_method' => 'S256', 'access_type' => 'online', 'prompt' => 'select_account'], '', '&', PHP_QUERY_RFC3986);
}
