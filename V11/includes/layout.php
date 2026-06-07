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
    // Tema: 'chiaro' | 'scuro' forzati dall'utente; qualsiasi altro valore
    // (o cookie assente) = "sistema", nessun attributo -> segue prefers-color-scheme.
    $temaCookie = $_COOKIE['tema'] ?? '';
    $attrTema = in_array($temaCookie, ['chiaro', 'scuro'], true)
        ? ' data-tema="' . $temaCookie . '"'
        : '';
    echo <<<HTML
<!DOCTYPE html>
<html lang="it"{$attrTema}>
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
    <link rel="stylesheet" href="css/stile.css?v=9">
    <link rel="stylesheet" href="css/stampa.css?v=9" media="print">
    <script>
        /* Applica subito il tema salvato (evita flash). 'chiaro'/'scuro'
           forzano; qualsiasi altro valore = sistema (nessun attributo). */
        (function () {
            try {
                var m = document.cookie.match(/(?:^|; *)tema=([^;]+)/);
                var t = m ? decodeURIComponent(m[1]) : '';
                if (t === 'chiaro' || t === 'scuro') {
                    document.documentElement.setAttribute('data-tema', t);
                } else {
                    document.documentElement.removeAttribute('data-tema');
                }
            } catch (e) { }
        })();
    </script>
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
    // Etichette testuali al posto delle emoji (#9). Sono nascoste agli
    // screen reader (il ruolo alert/status già comunica la natura) e
    // mostrate come "pillola" colorata via CSS.
    $etichette = ['errore' => 'Errore', 'successo' => 'OK', 'avviso' => 'Info'];
    $etichetta = $etichette[$tipo] ?? 'Nota';
    $ruolo = ($tipo === 'errore') ? 'alert' : 'status';
    $cls = "messaggio messaggio-{$tipo}";
    $testoSafe = esc($testo);
    return "<output class=\"{$cls}\" role=\"{$ruolo}\" aria-live=\"assertive\">"
        . "<strong class=\"messaggio-tag\" aria-hidden=\"true\">{$etichetta}</strong> {$testoSafe}</output>";
}
