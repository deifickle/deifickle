<?php /* Page layout. $pageTitle, $content and $c are set by render(). */ ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle) ?></title>
<meta name="description" content="<?= h($c['description']) ?>">
<link rel="alternate" type="application/rss+xml" title="<?= h($c['title']) ?>" href="/index.xml">
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" type="image/png" href="/res/favicon-32.png" sizes="32x32">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="stylesheet" href="/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css" media="(prefers-color-scheme: light)">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css" media="(prefers-color-scheme: dark)">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js" defer onload="hljs.highlightAll()"></script>
</head>
<body>
<header class="site-header">
  <a class="brand" href="/"><img src="<?= h($c['logo']) ?>" alt="" width="36" height="36"><?= h($c['title']) ?></a>
  <nav>
    <?php foreach ($c['nav'] as $label => $href): ?>
      <a href="<?= h($href) ?>"><?= h($label) ?></a>
    <?php endforeach ?>
  </nav>
</header>
<main>
<?= $content ?>
</main>
<footer class="site-footer">
  <?php foreach ($c['footer_links'] as $label => $href): ?>
    <a href="<?= h($href) ?>"><?= h($label) ?></a>
  <?php endforeach ?>
  <span>Content under the <a href="/www.wtfpl.net.txt">WTFPL</a>.</span>
</footer>
</body>
</html>
