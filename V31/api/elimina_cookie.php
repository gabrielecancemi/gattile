<?php
// Elimina tutti i cookie del sito e distrugge la sessione.


require_once '../includes/sessione.php';

header('Content-Type: application/json; charset=utf-8');

aprireSessione();

// Token "ricordami" rimosso lato server prima di distruggere la sessione.
cancellaPromemoria();
chiudiProfilo();

$opzioni_base = [
    'expires' => time() - 3600,
    'httponly' => true,
    'samesite' => 'Strict',
];

foreach ($_COOKIE as $nome => $valore) {
    foreach (['/', ''] as $path) {
        $opzioni = $opzioni_base;
        if ($path !== '') {
            $opzioni['path'] = $path;
        }
        setcookie($nome, '', $opzioni);
    }
    unset($_COOKIE[$nome]);
}

// Cancella anche il cookie di sessione PHP.
$parametri = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires' => time() - 3600,
    'path' => $parametri['path'] ?: '/',
    'domain' => $parametri['domain'] ?? '',
    'secure' => $parametri['secure'] ?? false,
    'httponly' => $parametri['httponly'] ?? true,
    'samesite' => $parametri['samesite'] ?? 'Strict',
]);

echo json_encode(['successo' => true]);
