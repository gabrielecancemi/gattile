<?php
/**
 * layout.php — Funzioni di layout condivise.
 * header e footer sono file separati (includes/header.php, includes/footer.php).
 */

require_once 'auth.php';

/* Head HTML */

function stampaTesta(string $titolo, string $descrizione): void
{
    $titoloPagina = $titolo;
    // Tema scelto dall'utente (cookie tecnico): 'scuro' o 'chiaro'.
    $tema = (($_COOKIE['tema'] ?? '') === 'scuro') ? 'scuro' : 'chiaro';
    echo <<<HTML
<!DOCTYPE html>
<html lang="it" data-tema="{$tema}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>{$titoloPagina}</title>
    <meta name="description" content="{$descrizione}">
    <meta name="keywords" content="gattile, adozione gatti, volontariato, felini, Torino">
    <meta name="author" content="Gabriele Cancemi">
    <meta name="robots" content="index, follow">
    <meta name="color-scheme" content="light dark">
    <link rel="icon" href="img/logo.png" type="image/png">
    <link rel="stylesheet" href="css/stile.css">
    <link rel="stylesheet" href="css/stampa.css" media="print">
</head>
HTML;
}

/* Header / Footer */

function stampaHeader(): void
{
    require 'header.php';
}

function apriMain(string $classe = ''): void
{
    $cls = $classe !== '' ? ' class="' . htmlspecialchars($classe, ENT_QUOTES) . '"' : '';
    echo '<main id="contenuto-principale" tabindex="-1"' . $cls . '>';
}

function chiudiMain(): void
{
    echo '</main>';
}

function stampaFooter(): void
{
    require 'footer.php';
}

/* Alias vuoti per retrocompatibilità */
function stampaBannerCookie(): void
{
}
function chiudiHTML(): void
{
}

/* Helpers */

/** Output HTML sicuro */
function esc(mixed $val): string
{
    return htmlspecialchars((string) $val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* Messaggio per l'utente ('errore' | 'successo' | 'avviso') */
function messaggioUtente(string $testo, string $tipo = 'errore'): string
{
    $icone = ['errore' => '⚠', 'successo' => '✓', 'avviso' => 'ℹ'];
    $icona = $icone[$tipo] ?? '•';
    $ruolo = ($tipo === 'errore') ? 'alert' : 'status';
    $cls = "messaggio messaggio-{$tipo}";
    $testoSafe = esc($testo);
    return "<output class=\"{$cls}\" role=\"{$ruolo}\" aria-live=\"assertive\">"
        . "<abbr aria-hidden=\"true\">{$icona}</abbr> {$testoSafe}</output>";
}
