<?php
/**
 * ============================================================
 * DYNAMIC XML SITEMAP GENERATOR
 * ============================================================
 * Purpose: Generates an XML sitemap for search engine crawlers
 *          (Google, Bing, etc.) to efficiently discover and index
 *          all public pages on the site.
 *
 * Sections:
 *   1. Static pages (homepage, about, contact, privacy)
 *   2. Dynamic category pages (from categories table)
 *   3. Dynamic app pages (all approved apps from apps table)
 *
 * URL Mapping: Accessible at /sitemap.xml via .htaccess rewrite
 * Output:      application/xml content type
 * ============================================================
 */
require_once 'includes/init.php';

// ── Set content type to XML so browsers and crawlers parse it correctly ──
header("Content-Type: application/xml; charset=utf-8");

// ── Build the full base URL dynamically ──
// Detects HTTP vs HTTPS and constructs the complete domain + path
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$clean_base = trim($base_url, '/');
$full_base_url = $protocol . "://" . $host . '/' . ($clean_base ? $clean_base . '/' : '');

// ── Output the XML declaration and opening tag ──
echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

// ── Section 1: Static Pages ──
// These are fixed pages with manually set priority and change frequency
$static_pages = [
    '' => ['priority' => '1.0', 'changefreq' => 'daily'],
    'about.php' => ['priority' => '0.7', 'changefreq' => 'monthly'],
    'contact.php' => ['priority' => '0.7', 'changefreq' => 'monthly'],
    'privacy.php' => ['priority' => '0.6', 'changefreq' => 'monthly'],
];

foreach ($static_pages as $page => $meta) {
    echo '  <url>' . PHP_EOL;
    echo '    <loc>' . $full_base_url . $page . '</loc>' . PHP_EOL;
    echo '    <lastmod>' . date('Y-m-d') . '</lastmod>' . PHP_EOL;
    echo '    <changefreq>' . $meta['changefreq'] . '</changefreq>' . PHP_EOL;
    echo '    <priority>' . $meta['priority'] . '</priority>' . PHP_EOL;
    echo '  </url>' . PHP_EOL;
}

// ── Section 2: Dynamic Category Pages ──
// Fetches all categories from the database and generates a URL for each
try {
    $stmt = $pdo->query("SELECT slug FROM categories");
    while ($row = $stmt->fetch()) {
        echo '  <url>' . PHP_EOL;
        echo '    <loc>' . $full_base_url . 'category/' . htmlspecialchars($row['slug']) . '</loc>' . PHP_EOL;
        echo '    <lastmod>' . date('Y-m-d') . '</lastmod>' . PHP_EOL;
        echo '    <changefreq>weekly</changefreq>' . PHP_EOL;
        echo '    <priority>0.8</priority>' . PHP_EOL;
        echo '  </url>' . PHP_EOL;
    }
} catch (PDOException $e) {}

// ── Section 3: All Approved App Pages ──
// Lists every approved app with its slug-based clean URL
// Priority 0.9 = high importance (these are the main content pages)
try {
    $stmt = $pdo->query("SELECT slug, created_at FROM apps WHERE status = 'approved' ORDER BY created_at DESC");
    while ($row = $stmt->fetch()) {
        echo '  <url>' . PHP_EOL;
        echo '    <loc>' . $full_base_url . htmlspecialchars($row['slug']) . '</loc>' . PHP_EOL;
        echo '    <lastmod>' . date('Y-m-d', strtotime($row['created_at'])) . '</lastmod>' . PHP_EOL;
        echo '    <changefreq>weekly</changefreq>' . PHP_EOL;
        echo '    <priority>0.9</priority>' . PHP_EOL;
        echo '  </url>' . PHP_EOL;
    }
} catch (PDOException $e) {}

echo '</urlset>';
