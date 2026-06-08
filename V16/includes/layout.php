<?php
// Funzioni di impaginazione condivise. Testata e piè di pagina stanno in
// file separati (header.php, footer.php).

require_once 'sessione.php';

// <head> della pagina. Il tema 'chiaro'/'scuro' arriva dal cookie; qualsiasi
// altro valore (o cookie assente) = tema di sistema, nessun attributo.
function generaIntestazioneHtml(string $titolo, string $descrizione): void
{
    // Header di sicurezza HTTP (OWASP A2 Security Misconfiguration e difesa in
    // profondità contro XSS/clickjacking/MIME-sniffing). Emessi prima di
    // qualsiasi output; la guardia headers_sent() evita warning se per qualche
    // motivo l'output fosse già iniziato.
    if (!headers_sent()) {
        // Evita che il browser "indovini" il MIME type (teoria: nosniff).
        header('X-Content-Type-Options: nosniff');
        // Anti-clickjacking: la pagina non può essere messa in un frame altrui.
        header('X-Frame-Options: DENY');
        // Non trasmettere il path completo come referrer verso altri siti.
        header('Referrer-Policy: strict-origin-when-cross-origin');
        // Limita le API potenti del browser non usate dal sito.
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        // Content-Security-Policy: tutto dal proprio dominio; gli unici script
        // esterni ammessi sono React/ReactDOM da unpkg (CDN pinnato). Nessuno
        // script o stile inline è usato nel progetto, quindi niente
        // 'unsafe-inline': difesa forte contro l'iniezione di script (XSS).
        header(
            "Content-Security-Policy: "
            . "default-src 'self'; "
            . "script-src 'self' https://unpkg.com; "
            . "style-src 'self'; "
            . "img-src 'self' data:; "
            . "connect-src 'self'; "
            . "base-uri 'self'; "
            . "form-action 'self'; "
            . "frame-ancestors 'none'; "
            . "object-src 'none'"
        );
    }

    $tema_cookie = $_COOKIE['tema'] ?? '';
    $attributo_tema = in_array($tema_cookie, ['chiaro', 'scuro'], true)
        ? ' data-tema="' . $tema_cookie . '"'
        : '';
    echo <<<HTML
<!DOCTYPE html>
<html lang="it"{$attributo_tema}>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>{$titolo}</title>
    <meta name="description" content="{$descrizione}">
    <meta name="keywords" content="gattile, adozione gatti, volontariato, felini, Torino">
    <meta name="author" content="Gabriele Cancemi">
    <meta name="robots" content="index, follow">
    <meta name="color-scheme" content="light dark">
    <link rel="icon" href="img/logo.png" type="image/png">
    <link rel="stylesheet" href="css/stile.css?v=10">
    <link rel="stylesheet" href="css/stampa.css?v=10" media="print">
    <script src="js/tema-iniziale.js"></script>
</head>
HTML;
}

function generaTestata(): void
{
    require 'header.php';
}

function aprireContenuto(string $classe = ''): void
{
    $cls = $classe !== '' ? ' class="' . htmlspecialchars($classe, ENT_QUOTES) . '"' : '';
    echo '<main id="contenuto-principale" tabindex="-1"' . $cls . '>';
}

function chiudereContenuto(): void
{
    echo '</main>';
}

function generaPiePagina(): void
{
    require 'footer.php';
}

// Output HTML sicuro.
function ripulisci(mixed $valore): string
{
    return htmlspecialchars((string) $valore, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Messaggio per l'utente: 'errore' | 'successo' | 'avviso'.
function avvisoUtente(string $testo, string $tipo = 'errore'): string
{
    $etichette = ['errore' => 'Errore', 'successo' => 'OK', 'avviso' => 'Info'];
    $etichetta = $etichette[$tipo] ?? 'Nota';
    $ruolo = ($tipo === 'errore') ? 'alert' : 'status';
    $cls = "messaggio messaggio-{$tipo}";
    $testo_pulito = ripulisci($testo);
    return "<output class=\"{$cls}\" role=\"{$ruolo}\" aria-live=\"assertive\">"
        . "<strong class=\"messaggio-tag\" aria-hidden=\"true\">{$etichetta}</strong> {$testo_pulito}</output>";
}
