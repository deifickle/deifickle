<?php
// Reads the Markdown files and turns them into pages. Posts live in posts/,
// standalone pages (about.md, projects.md, ...) live in the site root.

require_once __DIR__ . '/Parsedown.php';

const ROOT = __DIR__ . '/..';

function config(): array
{
    static $config;
    if ($config === null) {
        $config = require ROOT . '/config.php';
        date_default_timezone_set($config['timezone'] ?? 'UTC');
    }
    return $config;
}

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Hugo-style URL slug: lowercase, spaces to hyphens, drop anything odd.
function slugify(string $s): string
{
    $s = strtolower(trim($s));
    $s = preg_replace('/\s+/', '-', $s);
    return preg_replace('/[^a-z0-9_-]/', '', $s);
}

// Splits "---\nkey: value\n---\nbody" into [meta, body]. Understands the
// small subset of YAML the posts use: strings, true/false, [a, b] lists and
// "- item" lists.
function parse_markdown_file(string $path): array
{
    $text = str_replace("\r\n", "\n", file_get_contents($path));
    $meta = [];
    if (preg_match('/\A---\n(.*?)\n---\n?(.*)\z/s', $text, $m)) {
        $lastKey = null;
        foreach (explode("\n", $m[1]) as $line) {
            if (preg_match('/^\s+-\s*(.+)$/', $line, $item) && $lastKey !== null) {
                $meta[$lastKey] = (array)($meta[$lastKey] ?: []);
                $meta[$lastKey][] = unquote($item[1]);
            } elseif (preg_match('/^([A-Za-z_][\w-]*)\s*:\s*(.*)$/', $line, $kv)) {
                $lastKey = strtolower($kv[1]);
                $meta[$lastKey] = parse_value($kv[2]);
            }
        }
        $text = $m[2];
    }
    return [$meta, $text];
}

function parse_value(string $v)
{
    $v = trim($v);
    if ($v === 'true') return true;
    if ($v === 'false') return false;
    if (preg_match('/^\[(.*)\]$/', $v, $m)) {
        return array_values(array_filter(array_map('unquote', explode(',', $m[1])), 'strlen'));
    }
    return unquote($v);
}

function unquote(string $v): string
{
    $v = trim($v);
    if (strlen($v) >= 2 && ($v[0] === '"' || $v[0] === "'") && substr($v, -1) === $v[0]) {
        return substr($v, 1, -1);
    }
    return $v;
}

function markdown(string $text): string
{
    return (new Parsedown())->text($text);
}

// All published posts, newest first.
function all_posts(): array
{
    config(); // sets the timezone before dates are parsed
    static $posts;
    if ($posts !== null) return $posts;

    $posts = [];
    foreach (glob(ROOT . '/posts/*.md') as $file) {
        [$meta, $body] = parse_markdown_file($file);
        if (!empty($meta['draft'])) continue;
        $name = basename($file, '.md');
        $slug = slugify($name);
        $date = strtotime($meta['date'] ?? '') ?: filemtime($file);
        $posts[$slug] = [
            'slug'        => $slug,
            'url'         => "/posts/$slug/",
            'title'       => $meta['title'] ?? $name,
            'description' => $meta['description'] ?? '',
            'date'        => $date,
            'tags'        => (array)($meta['tags'] ?? []),
            'body'        => $body,
        ];
    }
    uasort($posts, fn($a, $b) => $b['date'] <=> $a['date']);
    return $posts;
}

function find_post(string $slug): ?array
{
    return all_posts()[slugify($slug)] ?? null;
}

// Standalone pages are the .md files in the site root, except README.md.
function find_page(string $slug): ?array
{
    foreach (glob(ROOT . '/*.md') as $file) {
        $name = basename($file, '.md');
        if (strcasecmp($name, 'README') === 0 || slugify($name) !== slugify($slug)) continue;
        [$meta, $body] = parse_markdown_file($file);
        return ['title' => $meta['title'] ?? $name, 'body' => $body];
    }
    return null;
}

// tag slug => ['name' => display name, 'posts' => [...]]
function all_tags(): array
{
    $tags = [];
    foreach (all_posts() as $post) {
        foreach ($post['tags'] as $tag) {
            $slug = slugify($tag);
            $tags[$slug]['name'] ??= $tag;
            $tags[$slug]['posts'][] = $post;
        }
    }
    ksort($tags);
    return $tags;
}

function format_date(int $ts): string
{
    return date('j M Y', $ts);
}

function tag_links(array $tags): string
{
    return implode(' ', array_map(
        fn($t) => '<a class="tag" href="/tags/' . h(slugify($t)) . '/">' . h($t) . '</a>',
        $tags
    ));
}

function post_list(array $posts): string
{
    $out = '<ul class="post-list">';
    foreach ($posts as $p) {
        $out .= '<li><time>' . format_date($p['date']) . '</time> '
            . '<a href="' . h($p['url']) . '">' . h($p['title']) . '</a>';
        if ($p['description'] !== '') $out .= '<p>' . h($p['description']) . '</p>';
        $out .= '</li>';
    }
    return $out . '</ul>';
}

function render(string $title, string $content, int $status = 200): void
{
    http_response_code($status);
    $c = config();
    $pageTitle = $title === '' ? $c['title'] : "$title · {$c['title']}";
    require ROOT . '/lib/layout.php';
}

function render_feed(): void
{
    $c = config();
    header('Content-Type: application/rss+xml; charset=utf-8');
    $xml = '<?xml version="1.0" encoding="utf-8"?>' . "\n"
        . '<rss version="2.0"><channel>'
        . '<title>' . h($c['title']) . '</title>'
        . '<link>' . h($c['url']) . '/</link>'
        . '<description>' . h($c['description']) . '</description>';
    foreach (all_posts() as $p) {
        $link = $c['url'] . $p['url'];
        $xml .= '<item>'
            . '<title>' . h($p['title']) . '</title>'
            . '<link>' . h($link) . '</link>'
            . '<guid>' . h($link) . '</guid>'
            . '<pubDate>' . date(DATE_RSS, $p['date']) . '</pubDate>'
            . '<description>' . h(markdown($p['body'])) . '</description>'
            . '</item>';
    }
    echo $xml . '</channel></rss>';
}
