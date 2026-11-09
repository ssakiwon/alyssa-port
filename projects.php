<?php
$pageClass = 'page-projects';
require __DIR__ . '/includes/header.php';

// To add a project, copy one block below and edit it.
$projects = [
    [
        'image' => 'poster-flyer.png',
        'alt'   => 'Product flyer design',
        'tags'  => ['Multimedia Design', 'Graphic Design'],
        'title' => 'Product Flyer',
        'text'  => 'A flyer that will present their product to the customers',
        'url'   => 'https://canva.link/fxwesq302yxhid8',
    ],
    [
        'image' => 'flyer-mockup.png',
        'alt'   => 'Flyer mock-up',
        'tags'  => ['Multimedia Design', 'Graphic Design'],
        'title' => 'Flyer Mock-up',
        'text'  => 'A mockup that will let them see what they should expect on the product itself',
        'url'   => 'https://canva.link/4w55rf2aqwbczop',
    ],
    [
        'image' => 'manga_gaur.png',
        'alt'   => 'E-book wireframe',
        'tags'  => ['Wireframe Design', 'Wireframe'],
        'title' => 'E-Book',
        'text'  => 'A wireframe design for an e-book that has genres, category and communication to the authors',
        'url'   => 'https://www.figma.com/proto/XimMeHAfl2bUhzI4b5awZg?node-id=25-10&t=FsOoGtVFTQmWKdQk-6',
    ],
    [
        'image' => 'navig-e.png',
        'alt'   => 'Navigation app wireframe',
        'tags'  => ['Wireframe Design', 'Wireframe'],
        'title' => 'Navigation App',
        'text'  => 'A navigation app for the users to have a direct tutorial on how to get to where they want to go',
        'url'   => 'https://www.figma.com/proto/5cQXhB1kPBoR8l9saUHDZC?node-id=1-2&t=FsOoGtVFTQmWKdQk-6',
    ],
];
?>

  <main class="projects">
    <section class="intro">
      <p class="kicker">Selected work</p>
      <h1>Projects I've <em>built &amp; shipped.</em></h1>
      <p class="lead">Personal and freelance work — hover a card, click to focus.</p>
    </section>

    <section class="grid" id="projectGrid">
      <?php foreach ($projects as $p): ?>
        <article class="card" tabindex="0">
          <div class="thumb">
            <img src="<?= e(rawurlencode($p['image'])) ?>" alt="<?= e($p['alt']) ?>" loading="lazy">
          </div>
          <div class="body">
            <p class="tags">
              <?php foreach ($p['tags'] as $tag): ?><span><?= e($tag) ?></span><?php endforeach; ?>
            </p>
            <h3><?= e($p['title']) ?></h3>
            <p><?= e($p['text']) ?></p>
            <a class="btn primary" href="<?= e($p['url']) ?>" target="_blank" rel="noopener">View Project</a>
          </div>
        </article>
      <?php endforeach; ?>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
