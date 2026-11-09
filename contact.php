<?php
require_once __DIR__ . '/includes/config.php';

$user   = current_user();
$errors = [];
$old    = [
    'name'    => $user['name']  ?? '',
    'email'   => $user['email'] ?? '',
    'subject' => '',
    'message' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$user) {
        flash('error', 'Please log in to send an inquiry.');
        redirect('login.php?next=contact.php');
    }

    $old = [
        'name'    => clean_line($_POST['name'] ?? ''),
        'email'   => clean_line($_POST['email'] ?? ''),
        'subject' => clean_line($_POST['subject'] ?? ''),
        'message' => trim(strip_tags($_POST['message'] ?? '')),
    ];

    if (!csrf_check($_POST['csrf'] ?? '')) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    } elseif (!empty($_POST['website'])) {
        // Honeypot filled in: pretend it worked, save nothing
        flash('ok', 'Inquiry sent! I will reply within 24 hours.');
        redirect('inquiries.php');
    } elseif (time() - ($_SESSION['last_sent'] ?? 0) < 30) {
        $errors[] = 'Please wait a few seconds before sending another inquiry.';
    } else {
        if ($old['name'] === '' || mb_strlen($old['name']) > 100)         $errors[] = 'Please enter your name.';
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL))           $errors[] = 'Please enter a valid email.';
        if ($old['subject'] === '' || mb_strlen($old['subject']) > 150)  $errors[] = 'Please enter a subject.';
        if ($old['message'] === '' || mb_strlen($old['message']) > 5000) $errors[] = 'Please enter a message.';

        if (!$errors) {
            $st = db()->prepare(
                'INSERT INTO inquiries (user_id, name, email, subject, message, created_at) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $st->execute([
                $user['id'], $old['name'], $old['email'], $old['subject'], $old['message'], date('Y-m-d H:i:s'),
            ]);

            $_SESSION['last_sent'] = time();
            flash('ok', 'Inquiry sent! I will reply within 24 hours.');
            redirect('inquiries.php');
        }
    }
}

$pageClass = 'page-contact';
$pageTitle = 'Contact';
require __DIR__ . '/includes/header.php';
?>

  <main class="contact">
    <section class="intro">
      <p class="kicker">Get in touch</p>
      <h1>Let's build <em>something great.</em></h1>
      <p class="lead">
        I'm currently open to full-time roles, freelance contracts, and interesting
        collaborations. If you have a project in mind or just want to say hi, my inbox
        is always open.
      </p>
      <p class="reply">I typically reply within 24 hours.</p>
      <p class="badge"><span class="dot"></span>Available for new projects</p>

      <ul class="links">
        <li><a href="mailto:<?= e($site['email']) ?>">✉ <?= e($site['email']) ?></a></li>
        <li><a href="<?= e($site['facebook']) ?>" target="_blank" rel="noopener">fb facebook.com/Alyssa Abundo</a></li>
        <li><a href="<?= e($site['instagram']) ?>" target="_blank" rel="noopener">◆ instagram.com/yssatwotatlo</a></li>
      </ul>
    </section>

    <section class="form-wrap">
      <?php if (!$user): ?>
        <h2 class="gate-title">Log in to send an inquiry</h2>
        <p class="gate-text">Create a free account or log in so you can send an inquiry and see it afterwards under My Inquiries.</p>
        <p class="gate-actions">
          <a class="gate-btn" href="login.php?next=contact.php">Log in</a>
          <a class="gate-btn ghost" href="register.php">Create account</a>
        </p>
      <?php else: ?>
        <form id="contactForm" action="contact.php" method="POST">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

          <label for="name">Name</label>
          <input type="text" id="name" name="name" required maxlength="100" value="<?= e($old['name']) ?>">

          <label for="email">Email</label>
          <input type="email" id="email" name="email" required value="<?= e($old['email']) ?>">

          <label for="subject">Subject</label>
          <input type="text" id="subject" name="subject" required maxlength="150" value="<?= e($old['subject']) ?>">

          <label for="message">Message</label>
          <textarea id="message" name="message" rows="6" required maxlength="5000"><?= e($old['message']) ?></textarea>

          <!-- honeypot: hidden from real users, catches bots -->
          <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">

          <button type="submit">Send Inquiry ✦</button>
          <p id="formStatus" class="error" role="status" aria-live="polite"><?= e(implode(' ', $errors)) ?></p>
        </form>
      <?php endif; ?>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
