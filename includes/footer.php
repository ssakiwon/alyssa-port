  <footer class="footer">
    <p>A.A. © <?= date('Y') ?> <?= e($site['name']) ?>. All rights reserved.</p>
  </footer>

  <script>
    // Dark / light toggle (choice is remembered in this browser)
    (function () {
      const root = document.documentElement;
      const btn = document.getElementById('themeToggle');
      if (!btn) return;

      function sync() {
        const light = root.getAttribute('data-theme') === 'light';
        btn.setAttribute('aria-label', light ? 'Switch to dark mode' : 'Switch to light mode');
        btn.setAttribute('aria-pressed', light ? 'true' : 'false');
      }

      btn.addEventListener('click', function () {
        const next = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
        root.setAttribute('data-theme', next);
        try { localStorage.setItem('theme', next); } catch (e) {}
        sync();
      });

      sync();
    })();
  </script>

<?php if (($pageClass ?? '') === 'page-about'): ?>
  <script>
    // Fill the skill bars when they scroll into view
    const bars = document.querySelectorAll('.bar');
    if ('IntersectionObserver' in window) {
      const io = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            io.unobserve(entry.target);
          }
        });
      }, { threshold: 0.4 });
      bars.forEach(b => io.observe(b));
    } else {
      bars.forEach(b => b.classList.add('is-visible'));
    }
  </script>
<?php elseif (($pageClass ?? '') === 'page-projects'): ?>
  <script>
    // Click (or Enter/Space) a card to focus it; click again or press Esc to clear.
    const grid = document.getElementById('projectGrid');
    const cards = [...grid.querySelectorAll('.card')];

    function setFocus(card) {
      const already = card.classList.contains('is-focused');
      cards.forEach(c => c.classList.remove('is-focused'));
      grid.classList.toggle('has-focus', !already);
      if (!already) card.classList.add('is-focused');
    }

    cards.forEach(card => {
      card.addEventListener('click', (e) => {
        if (e.target.closest('a')) return;
        setFocus(card);
      });
      card.addEventListener('keydown', (e) => {
        if (e.target !== card) return;
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); setFocus(card); }
      });
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        cards.forEach(c => c.classList.remove('is-focused'));
        grid.classList.remove('has-focus');
      }
    });
  </script>
<?php endif; ?>
</body>
</html>
