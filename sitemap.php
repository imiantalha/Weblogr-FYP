<?php

declare(strict_types=1);

header('Content-Type: application/xml; charset=UTF-8');

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
$scheme = $https ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $scheme . '://' . $host;

$urls = [
    ['path' => '/', 'changefreq' => 'weekly', 'priority' => '1.0'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

foreach ($urls as $url) {
    echo '<url>';
    echo '<loc>' . htmlspecialchars($baseUrl . $url['path'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
    echo '<changefreq>' . $url['changefreq'] . '</changefreq>';
    echo '<priority>' . $url['priority'] . '</priority>';
    echo '</url>';
}

echo '</urlset>';
