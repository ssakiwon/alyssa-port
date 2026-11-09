<?php
require_once __DIR__ . '/includes/config.php';

if (current_user()) redirect('index.php');

$errors = [];
$old    = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'name'  => clean_line($_POST['name'] ?? ''),
        'email' => mb_strtolower(clean_line($_POST['email'] ?? '')),
    ];
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['confirm'] ?? '');

    if (!csrf_check($_POST['csrf'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        if ($old['name'] === '' || mb_strlen($old['name']) > 100)  $errors[] = 'Please enter your name.';
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 190) $errors[] = 'Please enter a valid email.';
        if (strlen($password) < 8)                                 $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm)                                $errors[] = 'Passwords do not match.';

        if (!$errors) {
            $st = db()->prepare('SELECT 1 FROM users WHERE email = ?');
            $st->execute([$old['email']]);
            if ($st->fetchColumn()) {
                $errors[] = 'That email is already registered. Try logging in instead.';
            }
        }

        if (!$errors) {
            $st = db()->prepare('INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, ?)');
            $st->execute([$old['name'], $old['email'], password_hash($password, PASSWORD_DEFAULT), 'user', date('Y-m-d H:i:s')]);
            $id = (int) db()->lastInsertId();

            login_user(['id' => $id]);
            flash('ok', 'Account created. Welcome, ' . first_name($old['name']) . '!');
            redirect('index.php');
        }
    }
}

$pageClass = 'page-auth';
$pageTitle = 'Create account';
require __DIR__ . '/includes/header.php';
?>

  <main class="auth">
    <section class="auth-card">
      <p class="kicker">Join in</p>
      <h1>Create an <em>account.</em></h1>

      <form method="post" action="register.php">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <label for="name">Name</label>
        <input type="text" id="name" name="name" required maxlength="100" value="<?= e($old['name']) ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required maxlength="190" value="<?= e($old['email']) ?>">

        <label for="password">Password <span class="hint">(at least 8 characters)</span></label>
        <input type="password" id="password" name="password" required minlength="8">

        <label for="confirm">Confirm password</label>
        <input type="password" id="confirm" name="confirm" required minlength="8">

        <button type="submit">Create account</button>
        <p id="formStatus" class="error" role="status" aria-live="polite"><?= e(implode(' ', $errors)) ?></p>
      </form>

      <p class="alt">Already have an account? <a href="login.php">Log in</a></p>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
