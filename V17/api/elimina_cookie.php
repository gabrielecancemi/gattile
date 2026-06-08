<?php
// Elimina davvero tutti i cookie del sito e distrugge la sessione.
// Oltre a sessione e cookie "ricordami", scorro TUTTI i cookie presenti in
// $_COOKIE e li faccio scadere, sia su path '/' sia sul path corrente.
declare(strict_types=1);

require_once '../includes/sessione.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

aprireSessione();

// Gettone "ricordami" rimosso lato server prima di distruggere la sessione.
cancellaPromemoria();
chiudiProfilo();

$opzioni_base = [
    'expires'  => time() - 3600,
    'httponly' => true,
    'samesite' => 'Strict',
];

foreach (array_keys($_COOKIE) as $nome) {
    foreach (['/', ''] as $path) {
        $opzioni = $opzioni_base;
        if ($path !== '') {
            $opzioni['path'] = $path;
        }
        setcookie($nome, '', $opzioni);
    }
    unset($_COOKIE[$nome]);
}

// Cancella esplicitamente anche il cookie di sessione PHP.
if (ini_get('session.use_cookies')) {
    $parametri = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 3600,
        'path'     => $parametri['path'] ?: '/',
        'domain'   => $parametri['domain'] ?? '',
        'secure'   => $parametri['secure'] ?? false,
        'httponly' => $parametri['httponly'] ?? true,
        'samesite' => $parametri['samesite'] ?? 'Strict',
    ]);
}

echo json_encode(['successo' => true]);
