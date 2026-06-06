<?php
/**
 * privacy.php — Informativa privacy e gestione cookie.
 * Layout dedicato "a due colonne" con indice laterale, distinto dalle
 * altre pagine del sito.
 */
declare(strict_types=1);

require_once 'includes/layout.php';

avviaSessione();
$eliminati = isset($_GET['eliminati']) && $_GET['eliminati'] === '1';

stampaTesta(
    'Privacy e Cookie',
    'Informativa sulla privacy e gestione dei cookie del sito Gattile San Paolo.'
);
stampaHeader();
apriMain();
?>

<section class="privacy-hero" aria-labelledby="titolo-privacy">
    <h1 id="titolo-privacy">Privacy &amp; Cookie</h1>
    <p class="privacy-sottotitolo">
        Trasparenza totale: usiamo solo cookie tecnici, nessuna profilazione.
    </p>
    <p><time datetime="<?= date('Y-m-d') ?>">Aggiornata il <?= date('d/m/Y') ?></time></p>
</section>

<section class="privacy-layout" aria-label="Contenuto informativa">
    <?php if ($eliminati): ?>
        <?= messaggioUtente('I tuoi cookie sono stati eliminati con successo.', 'successo') ?>
    <?php endif; ?>

    <div class="privacy-griglia">

        <!-- Indice laterale di navigazione interna -->
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

        <!-- Colonna dei contenuti -->
        <div class="privacy-contenuto">

            <article class="privacy-blocco" id="sez-titolare" aria-labelledby="h-titolare">
                <h2 id="h-titolare"><span class="privacy-icona" aria-hidden="true">🏛️</span> Titolare del trattamento</h2>
                <address>
                    <strong>Gattile San Paolo</strong><br>
                    Via San Paolo 1, 10100 Torino (TO)<br>
                    <a href="tel:+390111234567">011 123 4567</a><br>
                    <a href="mailto:privacy@gattile-sanpaolo.it">privacy@gattile-sanpaolo.it</a>
                </address>
            </article>

            <article class="privacy-blocco" id="sez-cookie" aria-labelledby="h-cookie">
                <h2 id="h-cookie"><span class="privacy-icona" aria-hidden="true">🍪</span> Cookie utilizzati</h2>
                <p>
                    Questo sito usa <strong>esclusivamente cookie tecnici</strong> necessari al
                    funzionamento. Nessuna profilazione, nessun cookie di terze parti.
                </p>

                <!-- Cookie presentati come schede invece che come tabella classica -->
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
                                Contiene solo un <strong>token opaco</strong> per precompilare lo
                                username al login. Il token è associato all'utente lato server
                                in un file dedicato. <strong>Nessuna credenziale in chiaro.</strong>
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
                <h2 id="h-dati"><span class="privacy-icona" aria-hidden="true">🔐</span> Dati personali raccolti</h2>
                <p>
                    In fase di registrazione raccogliamo nome, cognome, indirizzo e credenziali.
                    La password è conservata cifrata con <abbr
                        title="Bcrypt, algoritmo di hashing sicuro per le password">Bcrypt</abbr>
                    e non è mai leggibile dal personale. I dati non vengono ceduti a terzi.
                </p>
            </article>

            <article class="privacy-blocco" id="sez-diritti" aria-labelledby="h-diritti">
                <h2 id="h-diritti"><span class="privacy-icona" aria-hidden="true">⚖️</span> I tuoi diritti</h2>
                <p>
                    Puoi richiedere l'accesso, la rettifica o la cancellazione dei tuoi dati e
                    del tuo account scrivendo a
                    <a href="mailto:privacy@gattile-sanpaolo.it">privacy@gattile-sanpaolo.it</a>.
                </p>
            </article>

            <article class="privacy-blocco privacy-blocco-azione" id="elimina" aria-labelledby="h-elimina">
                <h2 id="h-elimina"><span class="privacy-icona" aria-hidden="true">🧹</span> Elimina i tuoi cookie</h2>
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

<?php chiudiMain();
stampaFooter(); ?>
