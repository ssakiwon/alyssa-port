<?php
$pageClass = 'page-about';
require __DIR__ . '/includes/header.php';

$stats = [
    ['value' => 1, 'label' => 'Year Learning'],
    ['value' => 4, 'label' => 'Projects shipped'],
    ['value' => 2, 'label' => 'Happy clients'],
];

$skills = [
    'HTML & CSS'        => 70,
    'Graphic Designing' => 80,
    'UI / UX Design'    => 80,
    'Networking'        => 70,
];
?>

  <main class="about">

    <section class="intro">
      <p class="kicker">About me</p>
      <h1>Who I am &amp; <em>what I do.</em></h1>
      <p class="lead">
        I'm an Information Technology undergraduate with a deep passion for building
        design products that balance beautiful designs. I've shipped features for
        startups and personal projects alike — always with attention to detail and the
        user at the centre.
      </p>
      <p class="lead">
        Outside of designing, I enjoy dancing, minimal design, and obsessing over good
        typography. I believe the best interfaces are the invisible ones — they simply
        <em class="soft">feel</em> right.
      </p>

      <dl class="stats">
        <?php foreach ($stats as $s): ?>
          <div><dt><?= e($s['label']) ?></dt><dd><?= (int)$s['value'] ?></dd></div>
        <?php endforeach; ?>
      </dl>
    </section>

    <section class="skills" aria-labelledby="skillsTitle">
      <h2 id="skillsTitle">Core skills</h2>
      <ul>
        <?php foreach ($skills as $name => $level): ?>
          <li>
            <div class="skill-head"><span><?= e($name) ?></span><span><?= (int)$level ?>%</span></div>
            <div class="bar" role="progressbar" aria-valuenow="<?= (int)$level ?>" aria-valuemin="0" aria-valuemax="100">
              <span style="--level: <?= (int)$level ?>%"></span>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>

    <section class="watch" aria-labelledby="watchTitle">
      <p class="kicker">Watch</p>
      <h2 id="watchTitle" class="watch-title">A quick intro <em>to my world.</em></h2>
      <p class="lead">
        A 1 minute walkthrough of who I am and what I've built.
        No slides, no script — just me.
      </p>
      <video controls preload="metadata" playsinline>
        <source src="alyvid.mp4" type="video/mp4">
        Your browser does not support the video tag.
      </video>
    </section>

  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
