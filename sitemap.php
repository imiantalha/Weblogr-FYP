<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/env.php';

header('Content-Type: application/xml; charset=UTF-8');
header('Cache-Control: public, max-age=3600');

$configuredUrl = trim((string) (getenv('APP_URL') ?: ''));
if ($configuredUrl !== '') {
    $baseUrl = rtrim($configuredUrl, '/');
} else {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl = $scheme . '://' . $host;
}

$urls = [
    ['path' => '/', 'changefreq' => 'weekly', 'priority' => '1.0'],
    ['path' => '/seo-links.html', 'changefreq' => 'monthly', 'priority' => '0.6'],
];

$lastModified = gmdate('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

foreach ($urls as $url) {
    echo '<url>';
    echo '<loc>' . htmlspecialchars($baseUrl . $url['path'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
    echo '<lastmod>' . $lastModified . '</lastmod>';
    echo '<changefreq>' . $url['changefreq'] . '</changefreq>';
    echo '<priority>' . $url['priority'] . '</priority>';
    echo '</url>';
}

echo '</urlset>';
