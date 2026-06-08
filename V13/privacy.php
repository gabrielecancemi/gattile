<?php
// Informativa privacy e gestione cookie. Layout a due colonne con indice
// laterale, diverso dalle altre pagine.
declare(strict_types=1);

require_once 'includes/layout.php';

aprireSessione();
$eliminati = isset($_GET['eliminati']) && $_GET['eliminati'] === '1';

generaIntestazioneHtml(
    'Privacy e Cookie',
    'Informativa sulla privacy e gestione dei cookie del sito Gattile San Paolo.'
);
generaTestata();
aprireContenuto();
?>

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

        <div class="privacy-contenuto">

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
                    In fase di registrazione raccogliamo nome, cognome, indirizzo e credenziali.
                    Per scelta di configurazione attuale la password viene conservata
                    <strong>in chiaro</strong> nel database: questa <em>non</em> è l'opzione più
                    sicura, perché chiunque abbia accesso in lettura al database potrebbe
                    vederla. In un contesto di produzione andrebbe sostituita con un digest
                    calcolato tramite una funzione robusta dedicata alle password. I dati non
                    vengono ceduti a terzi.
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

        </div>
    </div>
</section>

<script src="js/privacy.js" defer></script>

<?php chiudereContenuto();
generaPiePagina(); ?>
