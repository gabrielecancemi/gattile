<?php
// Informativa privacy e gestione cookie


require_once 'includes/layout.php';

aprireSessione();
$eliminati = isset($_GET['eliminati']) && $_GET['eliminati'] === '1';

// Intestazione della pagina
$titolo_pagina = 'Privacy e Cookie';
$descrizione_pagina = 'Informativa sulla privacy e gestione dei cookie del sito Gattile San Paolo.';


?>
<!DOCTYPE html>
<html lang="it">

<?php require 'includes/head.php'; ?>
<?php require 'includes/header.php'; ?>
<main id="contenuto-principale">

    <!-- intestazione -->
    <section class="privacy-hero" aria-labelledby="titolo-privacy">
        <h1 id="titolo-privacy">Privacy &amp; Cookie</h1>
        <p class="privacy-sottotitolo">
            <strong>Trasparenza totale</strong>: usiamo solo cookie tecnici, nessuna profilazione.
        </p>
        <p><time datetime="<?= date('Y-m-d') ?>">Aggiornata il <?= date('d/m/Y') ?></time></p>
    </section>

    <!-- info privacy -->
    <section class="privacy-layout" aria-label="Contenuto informativa">
        <h2 class="sr-solo">Informazioni privacy</h2>
        <?php if ($eliminati): ?>
            <?= avvisoUtente('I tuoi cookie sono stati eliminati con successo.', 'successo') ?>
        <?php endif; ?>

        <article class="privacy-griglia">

            <!-- navigazione tra paragrafi -->
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

            <!-- uso dei dati -->
            <section class="privacy-contenuto">
                <h2 class="sr-solo">Privacy</h2>
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
                                <dt>Tipo</dt>
                                <dd>Sessione (tecnico)</dd>
                                <dt>Durata</dt>
                                <dd>Chiusura del browser</dd>
                                <dt>Finalità</dt>
                                <dd>Gestione della sessione autenticata</dd>
                            </dl>
                        </li>
                        <li class="privacy-cookie-card">
                            <h3><code>ricorda_username</code></h3>
                            <dl>
                                <dt>Tipo</dt>
                                <dd>Persistente (tecnico)</dd>
                                <dt>Durata</dt>
                                <dd>72 ore</dd>
                                <dt>Finalità</dt>
                                <dd>
                                    Contiene solo un token opaco per precompilare lo
                                    username al login. Il token è associato all'utente lato server
                                    in un file dedicato. Nessuna credenziale in chiaro.
                                </dd>
                            </dl>
                        </li>
                        <li class="privacy-cookie-card">
                            <h3><code>consenso_cookie</code></h3>
                            <dl>
                                <dt>Tipo</dt>
                                <dd>Persistente (tecnico)</dd>
                                <dt>Durata</dt>
                                <dd>1 anno</dd>
                                <dt>Finalità</dt>
                                <dd>Memorizza la lettura dell'informativa cookie</dd>
                            </dl>
                        </li>
                    </ul>
                    <p class="privacy-nota">
                        La preferenza del tema chiaro/scuro <strong>non</strong> usa cookie:
                        è salvata nel <code>localStorage</code> del browser (chiave
                        <code>tema</code>). Trattandosi di una semplice impostazione tecnica
                        di interfaccia, è disponibile anche senza accettare i cookie e non
                        viene trasmessa al server.
                    </p>
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
        </article>
    </section>

    <script src="js/privacy.js" defer></script>

</main>
<?php require 'includes/footer.php'; ?>