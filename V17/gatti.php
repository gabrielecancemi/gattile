<?php
// Pagina adozioni: componente React per la lista + form prenotazione in
// Vanilla JS. React comunica col form via CustomEvent sul DOM.
declare(strict_types=1);

require_once 'includes/layout.php';

aprireSessione();
$profilo = profiloAttivo();
$loggato = ($profilo !== null);
$is_admin = $loggato && (bool) $profilo['is_admin'];

// Intestazione della pagina (titolo + descrizione per SEO).
$titolo_pagina = 'Adotta un gatto';
$descrizione_pagina = 'Sfoglia i gatti disponibili per l\'adozione al Gattile San Paolo di Torino. Filtra per età, colore o nome.';

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
<html lang="it"<?= $attributo_tema ?>>
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
<main id="contenuto-principale" tabindex="-1">


<section aria-labelledby="titolo-gatti">
    <h1 id="titolo-gatti">I nostri ospiti felini</h1>
    <p>Seleziona i nostri gatti e <strong>prenota una visita</strong> conoscitiva.</p>
    <?php if (!$loggato): ?>
        <aside class="messaggio messaggio-avviso" role="note" aria-label="Avviso accesso">
            <p>
                <strong>Per prenotare una visita</strong> devi prima
                <a href="login.php">accedere</a> o <a href="registrazione.php">registrarti</a>.
                Puoi comunque sfogliare e filtrare tutti i gatti disponibili.
            </p>
        </aside>
    <?php endif; ?>
</section>
<section id="react-gatti-root" data-utente-loggato="<?= $loggato ? 'true' : 'false' ?>"
    data-is-admin="<?= $is_admin ? 'true' : 'false' ?>" aria-label="Elenco gatti con filtri e ordinamento"
    aria-busy="true">
    <p class="caricamento" aria-live="polite">Caricamento schede gatti in corso…</p>
</section>

<?php if ($loggato): ?>

    <section aria-labelledby="titolo-prenotazione">
        <h2 id="titolo-prenotazione">Prenota una visita conoscitiva</h2>
        <p>Seleziona prima i gatti dalle card qui sopra, poi scegli data e ora.</p>

        <section id="gatti-selezionati-riepilogo" role="status" aria-live="polite" aria-label="Gatti selezionati per la visita">
            <p class="messaggio messaggio-avviso">Nessun gatto selezionato. Clicca sulle card per sceglierli.</p>
        </section>

        <form id="form-prenotazione" method="post" action="api/prenota_visita.php" novalidate
            aria-label="Modulo prenotazione visita">

            <input type="hidden" id="gatti-ids" name="gatti_ids" value="">

            <fieldset>
                <legend>Scegli data e ora della visita</legend>

                <label for="data-visita" class="campo-obbligatorio">
                    Giorno della visita
                </label>
                <input type="date" id="data-visita" name="data_visita" required aria-required="true"
                    aria-describedby="aiuto-data-visita" min="<?= date('Y-m-d') ?>">
                <em id="aiuto-data-visita" class="aiuto-campo">
                    Scegli un giorno da oggi in poi.
                </em>

                <label for="ora-visita" class="campo-obbligatorio">
                    Orario della visita
                </label>
                <select id="ora-visita" name="ora_visita" required aria-required="true"
                    aria-describedby="aiuto-ora-visita">
                    <option value="" selected disabled>Seleziona un orario…</option>
                    <option value="09:00">09:00</option>
                    <option value="10:00">10:00</option>
                    <option value="11:00">11:00</option>
                    <option value="12:00">12:00</option>
                    <option value="13:00">13:00</option>
                    <option value="14:00">14:00</option>
                    <option value="15:00">15:00</option>
                    <option value="16:00">16:00</option>
                    <option value="17:00">17:00</option>
                </select>
                <em id="aiuto-ora-visita" class="aiuto-campo">
                    Le visite sono possibili dalle 9:00 alle 18:00.
                </em>

                <output class="errore-campo" id="err-data-visita" role="alert" aria-live="polite" hidden></output>
            </fieldset>

            <output id="msg-prenotazione" role="status" aria-live="polite" class="sr-solo"></output>

            <button type="submit" id="btn-prenota" class="btn btn-primario" disabled aria-disabled="true">
                Conferma prenotazione
            </button>
            <p class="aiuto-campo" aria-live="polite" id="note-btn-prenota">
                Seleziona almeno un gatto e una data per abilitare la prenotazione.
            </p>
        </form>
    </section>
<?php endif; ?>

<!-- React e ReactDOM da CDN, versione PINNATA a 18.3.1 (ultima con build UMD):
     evita aggiornamenti automatici imprevisti, riduce il rischio supply chain
     (OWASP A3) e mantiene il sito riproducibile. crossorigin="anonymous" è
     raccomandato da React per il caricamento da CDN.
     Per integrità verificata (OWASP A8) si può aggiungere l'attributo
     integrity="sha384-..." con l'hash SRI ufficiale del file servito (vedi
     progettoSito.txt, sez. SUPPLY CHAIN). In alternativa, ospitare i due file
     in locale è la soluzione preferita dalla teoria (risorse sullo stesso
     server della pagina). -->
<script src="https://unpkg.com/react@18.3.1/umd/react.production.min.js" crossorigin="anonymous"></script>
<script src="https://unpkg.com/react-dom@18.3.1/umd/react-dom.production.min.js" crossorigin="anonymous"></script>
<script defer src="js/GattiComponent.js"></script>

<?php if ($loggato && !$is_admin): ?>
    <script src="js/prenotazione.js" defer></script>
<?php endif; ?>

</main>
<?php require 'includes/footer.php'; ?>
