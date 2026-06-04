<?php
/**
 * api/elimina_cookie.php — Elimina tutti i cookie del sito e distrugge la sessione.
 */
declare(strict_types=1);

require_once '../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

avviaSessione();
logout();
eliminaRicordami(); // Distrugge sessione e cookie "ricordami"

// Elimina anche il cookie consenso
setcookie('cookie_consenso', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Strict',
]);

echo json_encode(['successo' => true]); ?>
