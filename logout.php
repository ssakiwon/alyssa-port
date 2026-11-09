<?php
require_once __DIR__ . '/includes/config.php';

// Logging out is a POST (from the nav button) so other sites can't log people out with a link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? '')) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();

    // Start a fresh session just to carry the goodbye message
    session_start();
    flash('ok', 'You have been logged out.');
}

redirect('index.php');
