<?php
// Informativa privacy e gestione cookie. Layout a due colonne con indice
// laterale, diverso dalle altre pagine.
declare(strict_types=1);

require_once 'includes/layout.php';

aprireSessione();
$eliminati = isset($_GET['eliminati']) && $_GET['eliminati'] === '1';

// Intestazione della pagina (titolo + descrizione per SEO).
$titolo_pagina = 'Privacy e Cookie';
$descrizione_pagina = 'Informativa sulla privacy e gestione dei cookie del sito Gattile San Paolo.';

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


<section class="privacy-hero" aria-labelledby="titolo-privacy">
    <h1 id="titolo-privacy">Privacy &amp; Cookie</h1>
    <p class="privacy-sottotitolo">
        <strong>Trasparenza totale</strong>: usiamo solo cookie tecnici, nessuna profilazione.
    </p>
    <p><time datetime="<?= date('Y-m-d') ?>">Aggiornata il <?= date('d/m/Y') ?></time></p>
</section>

<section class="privacy-layout" aria-label="Contenuto informativa">
    <?php if ($eliminati): ?>
        <?= avvisoUtente('I tuoi cookie sono stati eliminati con successo.', 'successo') ?>
    <?php endif; ?>

    <div class="privacy-griglia">

        <nav class="privacy-indice" aria-label="Indice della pagina">
            <h2>In questa pagina</h2>
            <ul>
                <li><a href="#sez-titolare">Titolare</a></li>
                <li><a href="#sez-cookie">Cookie usati</a></li>
                <li><a href="#sez-dati">Dati raccolti</a></li>
                <li><a href="#sez-diritti">I tuoi diritti</a></li>
                <li><a href="#elimina">Elimina i cookie</a></li>
            </ul>
        </nav>

        <section class="privacy-contenuto">

            <article class="privacy-blocco" id="sez-titolare" aria-labelledby="h-titolare">
                <h2 id="h-titolare">Titolare del trattamento</h2>
                <address>
                    <strong>Gattile San Paolo</strong>
                    <p>Via San Paolo 1, 10100 Torino (TO)</p>
                    <p><a href="tel:+390111234567">011 123 4567</a></p>
                    <p><a href="mailto:privacy@gattile-sanpaolo.it">privacy@gattile-sanpaolo.it</a></p>
                </address>
            </article>

            <article class="privacy-blocco" id="sez-cookie" aria-labelledby="h-cookie">
                <h2 id="h-cookie">Cookie utilizzati</h2>
                <p>
                    Questo sito usa <strong>esclusivamente cookie tecnici</strong> necessari al
                    funzionamento. Nessuna profilazione, nessun cookie di terze parti.
                </p>

                <ul class="privacy-cookie-lista">
                    <li class="privacy-cookie-card">
                        <h3><code>PHPSESSID</code></h3>
                        <dl>
                            <dt>Tipo</dt><dd>Sessione (tecnico)</dd>
                            <dt>Durata</dt><dd>Chiusura del browser</dd>
                            <dt>Finalità</dt><dd>Gestione della sessione autenticata</dd>
                        </dl>
                    </li>
                    <li class="privacy-cookie-card">
                        <h3><code>remember_username</code></h3>
                        <dl>
                            <dt>Tipo</dt><dd>Persistente (tecnico)</dd>
                            <dt>Durata</dt><dd>72 ore</dd>
                            <dt>Finalità</dt>
                            <dd>
                                Contiene solo un token opaco per precompilare lo
                                username al login. Il token è associato all'utente lato server
                                in un file dedicato. Nessuna credenziale in chiaro.
                            </dd>
                        </dl>
                    </li>
                    <li class="privacy-cookie-card">
                        <h3><code>cookie_consenso</code></h3>
                        <dl>
                            <dt>Tipo</dt><dd>Persistente (tecnico)</dd>
                            <dt>Durata</dt><dd>1 anno</dd>
                            <dt>Finalità</dt><dd>Memorizza la lettura dell'informativa cookie</dd>
                        </dl>
                    </li>
                    <li class="privacy-cookie-card">
                        <h3><code>tema</code></h3>
                        <dl>
                            <dt>Tipo</dt><dd>Persistente (tecnico)</dd>
                            <dt>Durata</dt><dd>1 anno</dd>
                            <dt>Finalità</dt><dd>Ricorda la preferenza tema chiaro/scuro</dd>
                        </dl>
                    </li>
                </ul>
                <p class="privacy-nota">Nessun cookie di profilazione o di terze parti è presente sul sito.</p>
            </article>

            <article class="privacy-blocco" id="sez-dati" aria-labelledby="h-dati">
                <h2 id="h-dati">Dati personali raccolti</h2>
                <p>
                    In fase di registrazione raccogliamo nome, cognome, indirizzo e
                    credenziali di accesso. Questi dati servono solo a gestire il tuo
                    profilo, l'autenticazione e le prenotazioni di visite e turni di
                    volontariato.
                </p>
                <p>
                    Conserviamo i dati per il tempo necessario a fornire il servizio e
                    non li cediamo a terzi, ne' li usiamo per profilazione o marketing.
                    Puoi chiederne in qualsiasi momento l'accesso, la rettifica o la
                    cancellazione (vedi <a href="#sez-diritti">I tuoi diritti</a>).
                </p>
            </article>

            <article class="privacy-blocco" id="sez-diritti" aria-labelledby="h-diritti">
                <h2 id="h-diritti">I tuoi diritti</h2>
                <p>
                    Puoi richiedere l'accesso, la rettifica o la cancellazione dei tuoi dati e
                    del tuo account scrivendo a
                    <a href="mailto:privacy@gattile-sanpaolo.it">privacy@gattile-sanpaolo.it</a>.
                </p>
            </article>

            <article class="privacy-blocco privacy-blocco-azione" id="elimina" aria-labelledby="h-elimina">
                <h2 id="h-elimina">Elimina i tuoi cookie</h2>
                <p>Rimuovi tutti i cookie e la sessione impostati da questo sito. Verrai disconnesso.</p>
                <button type="button" id="btn-elimina-cookie-privacy" class="btn btn-logout"
                    aria-describedby="nota-elimina">
                    Elimina tutti i miei cookie
                </button>
                <em id="nota-elimina" class="aiuto-campo">
                    Verrai reindirizzato a questa pagina con conferma dell'avvenuta eliminazione.
                </em>
            </article>

        </section>
    </div>
</section>

<script src="js/privacy.js" defer></script>

</main>
<?php require 'includes/footer.php'; ?>
