<?php
/**
 * layout.php — Punto di ingresso per le funzioni comuni di layout.
 * Le pagine includono questo file; header e footer sono ora file separati
 * (includes/header.php e includes/footer.php) per maggiore manutenibilità.
 */
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

/**
 * Stampa il blocco <head> HTML con tutti i meta necessari.
 * NON chiude il tag <body> — lo apre header.php.
 */
function stampaTesta(string $titolo, string $descrizione, string $canonical = ''): void
{
    $titoloPagina = htmlspecialchars($titolo) . ' — Gattile San Paolo';
    $descSafe     = htmlspecialchars($descrizione);
    $base         = 'https://gattile-San Paolo.example.it/';
    $canonUrl     = $canonical ? $base . ltrim($canonical, '/') : $base;
    echo <<<HTML
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>{$titoloPagina}</title>
    <meta name="description" content="{$descSafe}">
    <meta name="keywords" content="gattile, adozione gatti, volontariato, felini, Torino">
    <meta name="author" content="Gattile San Paolo">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{$canonUrl}">
    <link rel="icon" href="img/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="css/stile.css">
    <link rel="stylesheet" href="css/stampa.css" media="print">
</head>
HTML;
}

/** Include il file header (apre <body>, stampa <header>). */
function stampaHeader(): void
{
    require __DIR__ . '/header.php';
}

/** Apre il <main> con id per lo skip-link. */
function apriMain(): void
{
    echo '<main id="contenuto-principale" tabindex="-1">';
}

/** Chiude il <main>. */
function chiudiMain(): void
{
    echo '</main>';
}

/**
 * Include il file footer (stampa banner cookie, <footer>, pulsante FAQ,
 * script footer e chiude </body></html>).
 */
function stampaFooter(): void
{
    require __DIR__ . '/footer.php';
}

/**
 * Alias mantenuto per compatibilità: non fa nulla perché
 * footer.php gestisce già il banner cookie prima del tag <footer>.
 */
function stampaBannerCookie(): void
{
    // Il banner è incluso dentro footer.php — nessuna azione qui.
}

/** Alias mantenuto: chiusura HTML già avviene dentro footer.php. */
function chiudiHTML(): void
{
    // Gestito da footer.php — nessuna azione aggiuntiva.
}

/** Escape HTML sicura — usare sempre per output dati utente. */
function esc(mixed $val): string
{
    return htmlspecialchars((string)$val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Restituisce HTML per un messaggio inline all'utente.
 * @param string $tipo  'errore' | 'successo' | 'avviso'
 */
function messaggioUtente(string $testo, string $tipo = 'errore'): string
{
    $icone = ['errore' => '⚠', 'successo' => '✓', 'avviso' => 'ℹ'];
    $icona = $icone[$tipo] ?? '•';
    $ruolo = ($tipo === 'errore') ? 'alert' : 'status';
    $testoSafe = esc($testo);
    return "<output class=\"messaggio messaggio-{$tipo}\" role=\"{$ruolo}\" aria-live=\"assertive\">"
         . "<span aria-hidden=\"true\">{$icona}</span> {$testoSafe}</output>";
}
