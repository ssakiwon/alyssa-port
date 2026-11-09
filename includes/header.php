<?php
require_once __DIR__ . '/config.php';

$pageClass = $pageClass ?? 'page-home';
$cssMap = [
    'page-home'      => 'index.css',
    'page-about'     => 'about.css',
    'page-projects'  => 'projects.css',
    'page-contact'   => 'contact.css',
    'page-auth'      => 'auth.css',
    'page-inquiries' => 'auth.css',
];
$pageCss  = $cssMap[$pageClass] ?? 'index.css';
$title    = isset($pageTitle) ? $pageTitle . ' — ' . $site['name'] : $site['name'] . ' — Developer & Designer';
$user     = current_user();
$current  = basename($_SERVER['SCRIPT_NAME']);
$navLinks = [
    'index.php'    => 'Home',
    'about.php'    => 'About',
    'projects.php' => 'Projects',
    'contact.php'  => 'Contact',
];
$flash = get_flash();

// Admin sees how many inquiries are still unread
$unread = 0;
if ($user && $user['role'] === 'admin') {
    $unread = (int) db()->query('SELECT COUNT(*) FROM inquiries WHERE is_read = 0')->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <script>
    // Apply the saved theme before the page paints (no flash). Default is dark.
    try {
      if (localStorage.getItem('theme') === 'light') document.documentElement.setAttribute('data-theme', 'light');
    } catch (e) {}
  </script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Fraunces:ital,opsz,wght@0,9..144,500;1,9..144,500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e($pageCss) ?>">
  <link rel="stylesheet" href="site.css">
</head>
<body class="<?= e($pageClass) ?>">

  <header class="nav">
    <a href="index.php" class="brand">
      <img src="alyy_logo.png" alt="" class="brand-logo">
      <span>A.A.</span>
    </a>
    <nav>
      <?php foreach ($navLinks as $file => $label): ?>
        <a href="<?= e($file) ?>"<?= $current === $file ? ' class="active" aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>

      <?php if ($user): ?>
        <a href="inquiries.php"<?= $current === 'inquiries.php' ? ' class="active" aria-current="page"' : '' ?>><?= $user['role'] === 'admin' ? 'Inquiries' : 'My Inquiries' ?><?php if ($unread > 0): ?> <span class="count" aria-label="<?= $unread ?> unread"><?= $unread ?></span><?php endif; ?></a>
        <span class="nav-hello">Hi, <?= e(first_name($user['name'])) ?></span>
        <form class="nav-form" method="post" action="logout.php">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <button type="submit" class="linkbtn">Log out</button>
        </form>
      <?php else: ?>
        <a href="login.php"<?= in_array($current, ['login.php', 'register.php'], true) ? ' class="active" aria-current="page"' : '' ?>>Log in</a>
      <?php endif; ?>

      <button type="button" class="theme-toggle" id="themeToggle" aria-label="Switch to light mode" title="Switch theme">
        <span class="i-sun" aria-hidden="true">☀</span><span class="i-moon" aria-hidden="true">☾</span>
      </button>
    </nav>
  </header>

  <?php if ($flash): ?>
    <div class="flash <?= e($flash['type']) ?>" role="status"><?= e($flash['msg']) ?></div>
  <?php endif; ?>
