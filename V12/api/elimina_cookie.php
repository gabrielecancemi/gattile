<?php
/**
 * api/elimina_cookie.php — Elimina DAVVERO tutti i cookie del sito e
 * distrugge la sessione.
 *
 * (#10) Oltre a sessione e cookie "ricordami", si itera su TUTTI i cookie
 * presenti in $_COOKIE e li si fa scadere, sia su path '/' sia sul path
 * corrente, così non resta alcun cookie impostato dal sito.
 */
declare(strict_types=1);

require_once '../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

avviaSessione();

// Token "ricordami" rimosso lato server prima di distruggere la sessione
eliminaRicordami();
logout();

$opzioniBase = [
    'expires' => time() - 3600,
    'httponly' => true,
    'samesite' => 'Strict',
];

// Elimina ogni cookie presente, su più path per sicurezza
foreach (array_keys($_COOKIE) as $nome) {
    foreach (['/', ''] as $path) {
        $opz = $opzioniBase;
        if ($path !== '') {
            $opz['path'] = $path;
        }
        setcookie($nome, '', $opz);
    }
    unset($_COOKIE[$nome]);
}

// Elimina esplicitamente anche il cookie di sessione PHP
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => $p['path'] ?: '/',
        'domain' => $p['domain'] ?? '',
        'secure' => $p['secure'] ?? false,
        'httponly' => $p['httponly'] ?? true,
        'samesite' => $p['samesite'] ?? 'Strict',
    ]);
}

echo json_encode(['successo' => true]);
