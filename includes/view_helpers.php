<?php

declare(strict_types=1);

if (!function_exists('e')) {
    function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
}

function render_empty_state(string $title, string $message, ?string $actionLabel = null, ?string $actionUrl = null, string $icon = 'fa-inbox'): never
{
    $safeTitle = e($title); $safeMessage = e($message); $action = '';
    if ($actionLabel !== null && $actionUrl !== null) $action = '<a class="submit" href="'.e($actionUrl).'">'.e($actionLabel).'</a>';
    http_response_code(200);
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.$safeTitle.' | Weblogr</title><link rel="stylesheet" href="../posts/style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"></head><body><main class="content"><div class="all-posts-container"><div class="empty-state"><div class="empty-icon"><i class="fas '.$icon.'"></i></div><h2>'.$safeTitle.'</h2><p>'.$safeMessage.'</p>'.$action.'</div></div></main></body></html>';
    exit;
}

function render_not_found(string $title = 'We couldn’t find that', string $message = 'The page or content you requested is no longer available.'): never
{
    http_response_code(404);
    $safeTitle=e($title); $safeMessage=e($message);
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.$safeTitle.' | Weblogr</title><link rel="stylesheet" href="../posts/style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"></head><body><main class="content"><div class="all-posts-container"><div class="empty-state"><div class="empty-icon"><i class="fas fa-compass"></i></div><h2>'.$safeTitle.'</h2><p>'.$safeMessage.'</p><a class="submit" href="../posts/index.php">Back to Discover</a></div></div></main></body></html>';
    exit;
}
