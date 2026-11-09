<?php
require_once __DIR__ . '/includes/config.php';
require_login();

$user    = current_user();
$isAdmin = $user['role'] === 'admin';

// Admin actions: mark read/unread, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isAdmin && csrf_check($_POST['csrf'] ?? '')) {
        $id     = (int) ($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($id > 0 && $action === 'toggle') {
            db()->prepare('UPDATE inquiries SET is_read = 1 - is_read WHERE id = ?')->execute([$id]);
        } elseif ($id > 0 && $action === 'delete') {
            db()->prepare('DELETE FROM inquiries WHERE id = ?')->execute([$id]);
            flash('ok', 'Inquiry deleted.');
        }
    }
    redirect('inquiries.php');
}

if ($isAdmin) {
    $rows = db()->query(
        'SELECT i.*, u.name AS account_name
           FROM inquiries i LEFT JOIN users u ON u.id = i.user_id
          ORDER BY i.created_at DESC, i.id DESC LIMIT 200'
    )->fetchAll();
} else {
    $st = db()->prepare('SELECT * FROM inquiries WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT 200');
    $st->execute([$user['id']]);
    $rows = $st->fetchAll();
}

$unread = $isAdmin ? count(array_filter($rows, fn($r) => !$r['is_read'])) : 0;

$pageClass = 'page-inquiries';
$pageTitle = $isAdmin ? 'Inquiries' : 'My Inquiries';
require __DIR__ . '/includes/header.php';
?>

  <main class="inbox">
    <section class="intro">
      <p class="kicker"><?= $isAdmin ? 'Admin inbox' : 'Your messages' ?></p>
      <h1><?= $isAdmin ? 'All <em>inquiries.</em>' : 'My <em>inquiries.</em>' ?></h1>
      <p class="lead">
        <?php if ($isAdmin): ?>
          <?= count($rows) ?> total · <?= $unread ?> unread
        <?php else: ?>
          Here is everything you have sent me. I will reply by email.
        <?php endif; ?>
      </p>
    </section>

    <?php if (!$rows): ?>
      <div class="empty">
        <p>No inquiries yet.</p>
        <?php if (!$isAdmin): ?><a class="gate-btn" href="contact.php">Send an inquiry</a><?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="inq-list">
      <?php foreach ($rows as $r): ?>
        <article class="inq<?= $isAdmin && !$r['is_read'] ? ' is-new' : '' ?>">
          <div class="inq-head">
            <h3><?= e($r['subject']) ?></h3>
            <?php if ($isAdmin): ?>
              <span class="pill <?= $r['is_read'] ? '' : 'new' ?>"><?= $r['is_read'] ? 'Read' : 'New' ?></span>
            <?php endif; ?>
          </div>

          <p class="inq-meta">
            <?= e($r['name']) ?> ·
            <a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a> ·
            <time datetime="<?= e($r['created_at']) ?>"><?= e(date('M j, Y · g:i A', strtotime($r['created_at']))) ?></time>
            <?php if ($isAdmin && $r['account_name'] === null): ?> · <span class="muted">(account deleted)</span><?php endif; ?>
          </p>

          <p class="inq-body"><?= nl2br(e($r['message'])) ?></p>

          <?php if ($isAdmin): ?>
            <div class="inq-actions">
              <form method="post" action="inquiries.php">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <button type="submit" name="action" value="toggle" class="small"><?= $r['is_read'] ? 'Mark as unread' : 'Mark as read' ?></button>
              </form>
              <form method="post" action="inquiries.php" onsubmit="return confirm('Delete this inquiry? This cannot be undone.');">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <button type="submit" name="action" value="delete" class="small danger">Delete</button>
              </form>
            </div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
