<?php
/**
 * layout.php — Funzioni di layout condivise.
 * header e footer sono file separati (includes/header.php, includes/footer.php).
 * Nessun JavaScript inline nei file PHP.
 *
 * NOTA: declare(strict_types=1) è gestito dai file chiamanti (pagine .php).
 * I file inclusi via require non possono ridichiararlo dopo output già emesso.
 */
require_once __DIR__ . '/auth.php';

/* ── Head HTML ────────────────────────────────────────────────── */

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

/* ── Header / Footer ──────────────────────────────────────────── */

function stampaHeader(): void
{
    require __DIR__ . '/header.php';
}

function apriMain(): void
{
    echo '<main id="contenuto-principale" tabindex="-1">';
}

function chiudiMain(): void
{
    echo '</main>';
}

function stampaFooter(): void
{
    require __DIR__ . '/footer.php';
}

/* Alias vuoti per retrocompatibilità */
function stampaBannerCookie(): void {}
function chiudiHTML(): void {}

/* ── Helpers ──────────────────────────────────────────────────── */

/** Escape HTML sicura. Usare sempre per output dati utente. */
function esc(mixed $val): string
{
    return htmlspecialchars((string)$val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Messaggio inline per l'utente.
 * @param string $tipo  'errore' | 'successo' | 'avviso'
 */
function messaggioUtente(string $testo, string $tipo = 'errore'): string
{
    $icone    = ['errore' => '⚠', 'successo' => '✓', 'avviso' => 'ℹ'];
    $icona    = $icone[$tipo] ?? '•';
    $ruolo    = ($tipo === 'errore') ? 'alert' : 'status';
    $cls      = esc("messaggio messaggio-{$tipo}");
    $icoSafe  = $icona; // simboli Unicode sicuri, nessun dato utente
    $testoSafe = esc($testo);
    return "<output class=\"{$cls}\" role=\"{$ruolo}\" aria-live=\"assertive\">"
         . "<abbr aria-hidden=\"true\">{$icoSafe}</abbr> {$testoSafe}</output>";
}
