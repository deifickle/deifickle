<?php
// Every request that isn't an existing file (image, stylesheet, ...) lands
// here via .htaccess and is matched against the routes below.

require __DIR__ . '/lib/site.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . trim(rawurldecode($path), '/');

// Local testing with `php -S localhost:8000 index.php`: serve real files as-is.
if (PHP_SAPI === 'cli-server' && $path !== '/' && is_file(__DIR__ . $path)) {
    return false;
}

// Hugo served everything with a trailing slash; keep one canonical form.
if ($path !== '/' && !str_ends_with($path, '.xml') && !str_ends_with($_SERVER['REQUEST_URI'], '/')
    && !str_contains($_SERVER['REQUEST_URI'], '?')) {
    header('Location: ' . $path . '/', true, 301);
    exit;
}

if ($path === '/' || $path === '/posts') {
    render('', post_list(all_posts()));
} elseif (in_array($path, ['/index.xml', '/posts/index.xml', '/feed.xml'], true)) {
    render_feed();
} elseif (preg_match('#^/posts/([^/]+)$#', $path, $m) && ($post = find_post($m[1]))) {
    render($post['title'],
        '<article><h1>' . h($post['title']) . '</h1>'
        . '<p class="meta"><time>' . format_date($post['date']) . '</time> ' . tag_links($post['tags']) . '</p>'
        . markdown($post['body']) . '</article>');
} elseif ($path === '/tags') {
    $out = '<h1>Tags</h1><ul class="tag-list">';
    foreach (all_tags() as $slug => $tag) {
        $out .= '<li><a href="/tags/' . h($slug) . '/">' . h($tag['name']) . '</a> (' . count($tag['posts']) . ')</li>';
    }
    render('Tags', $out . '</ul>');
} elseif (preg_match('#^/tags/([^/]+)$#', $path, $m) && ($tag = all_tags()[slugify($m[1])] ?? null)) {
    render($tag['name'], '<h1>Posts tagged “' . h($tag['name']) . '”</h1>' . post_list($tag['posts']));
} elseif (preg_match('#^/([^/]+)$#', $path, $m) && ($page = find_page($m[1]))) {
    render($page['title'], '<article><h1>' . h($page['title']) . '</h1>' . markdown($page['body']) . '</article>');
} else {
    render('Not found', '<h1>Page not found</h1><p><a href="/">Back to the posts</a></p>', 404);
}
