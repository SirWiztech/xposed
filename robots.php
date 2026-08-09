<?php
/**
 * XPOSED — Dynamic robots.txt
 * Served as /robots.txt via the .htaccess rewrite. Reflects the configured
 * domain/base URL so the Sitemap line is always absolute and correct.
 */

require __DIR__ . '/app/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');

$sitemapUrl = absolute_url('sitemap.xml');
?>
User-agent: *
Allow: /

Disallow: /admin/
Disallow: /cart.php
Disallow: /checkout.php
Disallow: /order-confirmation.php
Disallow: /test.php

Sitemap: <?= $sitemapUrl ?>