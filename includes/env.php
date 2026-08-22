<?php

declare(strict_types=1);

function load_environment(string $path): void
{
    static $loaded = false;
    if ($loaded || !is_file($path) || !is_readable($path)) {
        $loaded = true;
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if ($name === '' || getenv($name) !== false) continue;
        if (strlen($value) >= 2 && (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"))) {
            $value = substr($value, 1, -1);
        }
        putenv($name . '=' . $value);
    }
    $loaded = true;
}

function register_application_exception_handler(): void
{
    static $registered = false;
    if ($registered) return;
    $registered = true;

    set_exception_handler(static function (Throwable $exception): void {
        error_log(sprintf('[Weblogr] %s in %s:%d%s', $exception->getMessage(), $exception->getFile(), $exception->getLine(), PHP_EOL . $exception->getTraceAsString()));
        $is_json = str_contains(strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json') || str_starts_with((string)($_SERVER['REQUEST_URI'] ?? ''), '/api/');
        http_response_code($exception instanceof RuntimeException ? 503 : 500);

        if ($is_json) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'The service is temporarily unavailable. Please try again later.']);
            exit;
        }

        $message = $exception instanceof RuntimeException ? $exception->getMessage() : 'We could not complete your request right now. Please try again in a moment.';
        $safe = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $local_hint = strtolower((string)(getenv('APP_ENV') ?: 'local')) !== 'production'
            ? '<p class="hint">Local development: make sure MySQL is running and your <code>.env</code> database settings are configured.</p>' : '';

        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Weblogr | Something went wrong</title><style>body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:linear-gradient(135deg,#eef4ff,#f8fafc);color:#172033;font-family:Inter,system-ui,sans-serif}.card{width:min(540px,100%);background:#fff;border:1px solid #e6eaf0;border-radius:20px;padding:34px;box-shadow:0 18px 45px rgba(23,32,51,.1);text-align:center}.icon{width:58px;height:58px;margin:0 auto 18px;display:grid;place-items:center;border-radius:50%;background:#fef2f2;color:#b42318;font-size:28px;font-weight:800}h1{margin:0 0 10px;font-size:26px}p{color:#697386;line-height:1.6}.message{margin-top:18px;padding:13px 15px;border:1px solid #fecaca;border-radius:10px;background:#fef2f2;color:#b42318;text-align:left}.actions{display:flex;justify-content:center;gap:10px;margin-top:24px}.button{padding:11px 17px;border-radius:10px;text-decoration:none;font-weight:700;background:#2563eb;color:#fff}.secondary{background:#eef2f7;color:#172033}.hint{margin-top:18px;font-size:12px}.hint code{background:#f1f5f9;padding:2px 5px;border-radius:5px}@media(max-width:520px){.actions{flex-direction:column}}</style></head><body><main class="card"><div class="icon">!</div><h1>Something went wrong</h1><p>We could not complete your request right now.</p><div class="message" role="alert">'.$safe.'</div><div class="actions"><a class="button" href="javascript:location.reload()">Try again</a><a class="button secondary" href="javascript:history.back()">Go back</a></div>'.$local_hint.'</main></body></html>';
        exit;
    });
}

load_environment(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');
register_application_exception_handler();
