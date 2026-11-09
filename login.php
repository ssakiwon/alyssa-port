<?php
require_once __DIR__ . '/includes/config.php';

if (current_user()) redirect('index.php');

// Only allow redirecting back to our own pages
$allowedNext = ['contact.php', 'inquiries.php', 'index.php'];
$next = $_POST['next'] ?? $_GET['next'] ?? '';
if (!in_array($next, $allowedNext, true)) $next = 'index.php';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mb_strtolower(clean_line($_POST['email'] ?? ''));
    $now   = time();

    if (!csrf_check($_POST['csrf'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } elseif (($_SESSION['lock_until'] ?? 0) > $now) {
        $error = 'Too many attempts. Please wait a minute and try again.';
    } else {
        $st = db()->prepare('SELECT * FROM users WHERE email = ?');
        $st->execute([$email]);
        $row = $st->fetch();

        if ($row && password_verify($_POST['password'] ?? '', $row['password_hash'])) {
            unset($_SESSION['fails'], $_SESSION['lock_until']);
            login_user($row);
            flash('ok', 'Welcome back, ' . first_name($row['name']) . '!');
            redirect($next);
        }

        $_SESSION['fails'] = ($_SESSION['fails'] ?? 0) + 1;
        if ($_SESSION['fails'] >= 5) {
            $_SESSION['lock_until'] = $now + 60;
            $_SESSION['fails'] = 0;
        }
        $error = 'Incorrect email or password.';
    }
}

$pageClass = 'page-auth';
$pageTitle = 'Log in';
require __DIR__ . '/includes/header.php';
?>

  <main class="auth">
    <section class="auth-card">
      <p class="kicker">Welcome back</p>
      <h1>Log <em>in.</em></h1>

      <form method="post" action="login.php">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="next" value="<?= e($next) ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autofocus value="<?= e($email) ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Log in</button>
        <p id="formStatus" class="error" role="status" aria-live="polite"><?= e($error) ?></p>
      </form>

      <p class="alt">No account yet? <a href="register.php">Create one</a></p>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
