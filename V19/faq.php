<?php
// Domande frequenti, raggiungibili dal pulsante "?" fisso in basso a destra.
declare(strict_types=1);

require_once 'includes/layout.php';

aprireSessione();

// Intestazione della pagina (titolo + descrizione per SEO).
$titolo_pagina = 'Domande frequenti';
$descrizione_pagina = 'Risposte alle domande più comuni su adozioni, volontariato e servizi del Gattile San Paolo di Torino.';

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
<main id="contenuto-principale" tabindex="-1" class="main-faq">


<section aria-labelledby="titolo-faq">
    <h1 id="titolo-faq">Domande frequenti</h1>
    <p>
        Non trovi la risposta che cerchi?
        <a href="mailto:info@gattile-sanpaolo.it">Scrivici</a> e ti risponderemo al più presto.
    </p>
</section>

<section class="faq-categoria" aria-labelledby="faq-adozioni">
    <h2 id="faq-adozioni">Adozioni</h2>

    <details>
        <summary>Posso adottare anche se vivo in appartamento?</summary>
        <p>
            Assolutamente sì. Molti dei nostri gatti sono nati o cresciuti in ambienti chiusi
            e si adattano perfettamente alla vita in appartamento, purché abbiano spazi per
            giocare e qualcuno che li ami.
        </p>
    </details>

    <details>
        <summary>Quanto costa adottare un gatto?</summary>
        <p>
            L'adozione è completamente gratuita. Chiediamo soltanto la disponibilità a
            prendersi cura dell'animale, incluse le spese veterinarie ordinarie (vaccinazioni,
            antiparassitari, visite annuali).
        </p>
    </details>

    <details>
        <summary>Devo avere un giardino per adottare?</summary>
        <p>
            No. Un appartamento ben organizzato con tiragraffi, ripiani e giochi è più che
            sufficiente per la maggior parte dei gatti. Alcune razze più attive potrebbero
            beneficiare di spazi più ampi, ma lo valutiamo insieme.
        </p>
    </details>

    <details>
        <summary>È possibile adottare un gatto anziano?</summary>
        <p>
            Sì, e lo consigliamo! I gatti adulti e anziani sono spesso meno adottati, ma
            hanno già un carattere definito, sono solitamente più tranquilli e richiedono
            meno supervisione. Ci sono tanti benefici nell'accogliere un gatto senior.
        </p>
    </details>

    <details>
        <summary>Come avviene il processo di adozione?</summary>
        <ol class="faq-processo">
            <li><a href="registrazione.php">Registrati</a> al sito (se non l'hai già fatto).</li>
            <li>Sfoglia i gatti disponibili nella sezione <a href="gatti.php">Adotta un gatto</a>.</li>
            <li>Seleziona i gatti che ti interessano e prenota una visita conoscitiva.</li>
            <li>Vieni in struttura per conoscerli di persona.</li>
            <li>Se è amore, completeremo insieme le pratiche di affidamento.</li>
        </ol>
    </details>
</section>


<section class="faq-categoria" aria-labelledby="faq-volontariato">
    <h2 id="faq-volontariato">Volontariato</h2>

    <details>
        <summary>Come posso diventare volontario?</summary>
        <p>
            <a href="registrazione.php">Registrati</a> al sito, poi accedi alla pagina
            <a href="volontariato.php">Volontariato</a> e scegli le fasce orarie in cui
            desideri prestare servizio. La struttura accoglie al massimo due volontari
            per fascia oraria.
        </p>
    </details>

    <details>
        <summary>Quante ore devo impegnarmi?</summary>
        <p>
            Non esiste un minimo obbligatorio. Puoi prenotare una sola fascia oraria o
            quante ne vuoi. L'importante è presentarsi puntuale e avvisare in caso
            di imprevisti.
        </p>
    </details>

    <details>
        <summary>Serve esperienza con i gatti?</summary>
        <p>
            No, nessuna esperienza è richiesta. Ti verrà spiegato come approcciarsi agli
            animali, come pulire gli spazi in sicurezza e come supportare il personale
            durante le visite. Solo buona volontà e amore per i felini!
        </p>
    </details>

    <details>
        <summary>Posso portare i miei figli a fare volontariato?</summary>
        <p>
            I minorenni possono partecipare se accompagnati da un genitore o tutore
            legale registrato al sito. Contattateci preventivamente per organizzare
            la visita in modo adeguato.
        </p>
    </details>
</section>

<section class="faq-categoria" aria-labelledby="faq-struttura">
    <h2 id="faq-struttura">La struttura</h2>

    <details>
        <summary>Dove si trova il gattile?</summary>
        <p>
            Via San Paolo 1, 10100 Torino (TO).
            Siamo raggiungibili con i mezzi pubblici: linea 15 (fermata Felina) o
            linea 52 (fermata Parco Verde).
        </p>
    </details>

    <details>
        <summary>Quali sono gli orari di apertura?</summary>
        <p>
            Il gattile è aperto dal lunedì al sabato, dalle 09:00 alle 18:00.
            La domenica è riservata al personale per le cure ordinarie.
        </p>
    </details>

    <details>
        <summary>Come posso contattarvi?</summary>
        <address>
            <p><a href="tel:+390111234567">011 123 4567</a> (lun-sab, 9:00-18:00)</p>
            <p><a href="mailto:info@gattile-sanpaolo.it">info@gattile-sanpaolo.it</a></p>
        </address>
    </details>
</section>

</main>
<?php require 'includes/footer.php'; ?>
