<?php
// Prenotazione turni di volontariato.
declare(strict_types=1);

require_once 'includes/layout.php';

aprireSessione();
$loggato = (profiloAttivo() !== null);

// Intestazione della pagina (titolo + descrizione per SEO).
$titolo_pagina = 'Volontariato';
$descrizione_pagina = 'Diventa volontario al Gattile San Paolo di Torino: scegli le fasce orarie in cui prestare aiuto.';

// Header di sicurezza HTTP: difesa in profondità contro XSS, clickjacking e
// MIME-sniffing. Vanno emessi prima di qualsiasi output.
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    // CSP: tutto dal proprio dominio, React/ReactDOM solo da unpkg. Niente
    // 'unsafe-inline' perché nel sito non uso script o stili inline.
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

// Tema da cookie: solo 'chiaro'/'scuro' sono validi, altrimenti tema di sistema.
$tema_cookie = $_COOKIE['tema'] ?? '';
$attributo_tema = in_array($tema_cookie, ['chiaro', 'scuro'], true)
    ? ' data-tema="' . $tema_cookie . '"'
    : '';
?>
<!DOCTYPE html>
<html lang="it" <?= $attributo_tema ?>>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?= ripulisci($titolo_pagina) ?></title>
    <meta name="description" content="<?= ripulisci($descrizione_pagina) ?>">
    <meta name="keywords" content="gattile, adozione gatti, volontariato, felini, Torino">
    <meta name="author" content="Gabriele Cancemi">
    <meta name="robots" content="index, follow">
    <meta name="color-scheme" content="light dark">
    <link rel="icon" href="img/logo.png" type="image/png">
    <link rel="stylesheet" href="css/stile.css?v=10">
    <link rel="stylesheet" href="css/stampa.css?v=10" media="print">
    <script src="js/tema-iniziale.js"></script>
</head>
<?php require 'includes/header.php'; ?>
<main id="contenuto-principale" tabindex="-1" class="volontario-main">


    <section aria-labelledby="titolo-volontariato">
        <h1 id="titolo-volontariato">Fai volontariato</h1>
        <p>
            Il tuo aiuto fa la differenza. Scegli quante fasce orarie vuoi.
            La struttura accoglie <strong>al massimo 2 volontari per fascia oraria</strong>.
        </p>
        <?php if (!$loggato): ?>
            <aside class="messaggio messaggio-avviso" role="note">
                <p>
                    <strong>Per prenotare un turno devi prima</strong>
                    <a href="login.php">accedere</a> o
                    <a href="registrazione.php">registrarti</a>.
                </p>
            </aside>
        <?php endif; ?>
    </section>
    <section aria-labelledby="titolo-info-vol">
        <h2 id="titolo-info-vol">Cosa fare da volontario</h2>
        <ul>
            <li>Socializzare con i gatti e giocare con loro</li>
            <li>Aiutare con la pulizia degli spazi</li>
            <li>Supportare durante le visite dei potenziali adottanti</li>
            <li>Assistere il personale nella gestione della struttura</li>
            <li>Non è richiesta alcuna esperienza specifica.
                Per info: <a href="mailto:info@gattile-sanpaolo.it">info@gattile-sanpaolo.it</a>.</li>
        </ul>
    </section>
    <section>
        <?php if ($loggato): ?>
            <h2>Prenota i turni</h2>
            <form id="form-volontariato" method="post" action="api/turni.php" novalidate
                aria-label="Modulo prenotazione turni volontariato">
                <fieldset>
                    <legend>Seleziona giorno e fasce orarie</legend>
                    <p class="aiuto-campo">
                        Le fasce con 2/2 volontari sono disabilitate automaticamente.
                    </p>

                    <label for="data-turno" class="campo-obbligatorio">Giorno</label>
                    <input type="date" id="data-turno" name="data_turno" required aria-describedby="aiuto-data-turno"
                        min="<?= date('Y-m-d') ?>">
                    <em id="aiuto-data-turno" class="aiuto-campo">
                        Scegli prima un giorno: verranno mostrate solo le sue fasce orarie.
                    </em>

                    <section id="contenitore-turni" aria-live="polite" aria-busy="true"
                        aria-label="Fasce orarie disponibili">
                        <p class="caricamento">Caricamento fasce orarie…</p>
                    </section>
                </fieldset>

                <output id="msg-volontariato" aria-live="polite" class="sr-solo"></output>

                <button type="submit" id="btn-volontariato" class="btn btn-primario" disabled>
                    Conferma turni selezionati
                </button>
                <p class="aiuto-campo" aria-live="polite" id="note-btn-volontariato">
                    Seleziona almeno una fascia oraria disponibile.
                </p>
            </form>

        <?php endif; ?>
    </section>

    <script src="js/volontariato.js" defer></script>

</main>
<?php require 'includes/footer.php'; ?>