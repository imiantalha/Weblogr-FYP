<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/public_helpers.php';

start_secure_session();
http_response_code(404);
$request_path = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#2563eb">
    <link rel="icon" type="image/svg+xml" href="<?= e(public_url('assets/weblogr-mark.svg')) ?>">
    <link rel="apple-touch-icon" href="<?= e(public_url('assets/weblogr-mark.svg')) ?>">
    <link rel="stylesheet" href="<?= e(public_url('assets/public.css')) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <title>Page not found | Weblogr</title>
    <style>
        .error-page{min-height:100vh;display:flex;flex-direction:column}
        .error-content{flex:1;display:grid;place-items:center;padding:48px 20px}
        .error-card{width:min(680px,100%);box-sizing:border-box;text-align:center;background:#fff;border:1px solid var(--border,#e5e7eb);border-radius:24px;padding:58px 34px;box-shadow:var(--shadow,0 18px 50px rgba(15,23,42,.08))}
        .error-logo{width:62px;height:62px;margin:0 auto 22px}
        .error-code{margin:0;color:var(--primary,#2563eb);font-size:clamp(76px,16vw,128px);font-weight:800;line-height:.85;letter-spacing:-.08em}
        .error-card h1{margin:24px 0 10px;color:var(--ink,#172033);font-size:32px;letter-spacing:-.04em}
        .error-card p{max-width:500px;margin:0 auto 26px;color:var(--muted,#687386);line-height:1.7}
        .error-actions{display:flex;justify-content:center;gap:10px;flex-wrap:wrap}
        .error-actions .button{min-width:160px}
        .error-path{margin-top:26px!important;color:#98a2b3!important;font-size:12px;overflow-wrap:anywhere}
        @media(max-width:560px){.error-card{padding:42px 20px}.error-card h1{font-size:26px}.error-actions{flex-direction:column}.error-actions .button{width:100%}}
    </style>
</head>
<body>
<div class="error-page">
    <?php include __DIR__ . '/includes/public_header.php'; ?>
    <main class="error-content" id="main">
        <section class="error-card" aria-labelledby="error-title">
            <img class="error-logo" src="<?= e(public_url('assets/weblogr-mark.svg')) ?>" alt="Weblogr">
            <p class="error-code" aria-hidden="true">404</p>
            <h1 id="error-title">This page wandered off.</h1>
            <p>The page you're looking for doesn't exist, may have moved, or may no longer be available. Let's get you back to something worth reading.</p>
            <div class="error-actions">
                <a class="button button-primary" href="<?= e(public_url('index.html')) ?>"><i class="fas fa-home" aria-hidden="true"></i> Back to Weblogr</a>
                <a class="button button-secondary" href="<?= e(public_url('blog.php')) ?>"><i class="fas fa-compass" aria-hidden="true"></i> Discover stories</a>
            </div>
            <p class="error-path">Requested path: <?= e($request_path) ?></p>
        </section>
    </main>
</div>
</body>
</html>
