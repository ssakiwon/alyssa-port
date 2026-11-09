<?php
$pageClass = 'page-home';
require __DIR__ . '/includes/header.php';
?>

  <main class="hero">
    <section class="hero-text">
      <p class="kicker">Hello, I'm</p>
      <h1><?= e($site['name']) ?></h1>
      <p class="role">UI/UX Designer</p>
      <p class="lead">
        Hello! Welcome to my portfolio, I hope to see you and work with you in the future.
      </p>

      <ul class="chips">
        <li>Designs</li>
        <li>Achievements</li>
        <li>Open to work ✦</li>
      </ul>

      <div class="actions">
        <a class="btn primary" href="projects.php">View Projects</a>
        <a class="btn ghost" href="contact.php">Get in Touch</a>
      </div>
    </section>

    <section class="hero-visual">
      <div class="photo">
        <img class="hero-dark" src="hero-dark.jpg" alt="Portrait of <?= e($site['name']) ?> in a red dress">
        <img class="hero-light" src="hero-light.jpg" alt="Portrait of <?= e($site['name']) ?> in a white dress" loading="lazy">
      </div>
      <p class="float exp">2 years experience</p>
      <p class="float hire"><span class="dot"></span>Available for hire ✦</p>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
