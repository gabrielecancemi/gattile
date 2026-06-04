<?php
/**
 * volontariato.php — Pagina prenotazione turni volontariato.
 * Mostra fasce orarie; quelle piene vengono disabilitate lato client via JS.
 * Il backend PHP verifica nuovamente l'integrità prima di inserire.
 */
declare(strict_types=1);

require_once 'includes/layout.php';
require_once 'includes/db.php';

avviaSessione();
$utente  = utenteLoggato();
$loggato = ($utente !== null);

stampaTesta(
    'Volontariato',
    'Diventa volontario al Gattile Felice di Torino: scegli le fasce orarie in cui prestare il tuo aiuto ai gatti.',
    'volontariato.php'
);
echo '<body>';
stampaHeader();
stampaBannerCookie();
apriMain();
?>

<section aria-labelledby="titolo-volontariato">
    <h1 id="titolo-volontariato">Fai volontariato</h1>
    <p>
        Il tuo aiuto fa la differenza! Puoi scegliere quante fasce orarie vuoi
        e prenotarti comodamente da qui. La struttura accoglie 
        <strong>al massimo 2 volontari per fascia oraria</strong>.
    </p>

    <?php if (!$loggato): ?>
        <aside class="messaggio messaggio-avviso" role="note">
            <p>
                Per prenotare un turno di volontariato devi prima 
                <a href="login.php">accedere</a> o 
                <a href="registrazione.php">registrarti</a>.
            </p>
        </aside>
    <?php else: ?>

    <!-- Form prenotazione turni — gestito in Vanilla JS -->
    <form
        id="form-volontariato"
        method="post"
        action="api/prenota_turno.php"
        novalidate
        aria-label="Modulo prenotazione turni volontariato"
    >
        <fieldset>
            <legend>Seleziona le fasce orarie</legend>
            <p class="aiuto-campo">
                Le fasce con 2/2 volontari sono disabilitate automaticamente.
                Puoi prenotare un numero illimitato di turni disponibili.
            </p>

            <!--
                La lista dei turni viene generata e gestita da volontariato.js.
                Lo script interroga l'API per recuperare la disponibilità in tempo reale.
            -->
            <div
                id="contenitore-turni"
                aria-live="polite"
                aria-busy="true"
                aria-label="Fasce orarie disponibili"
            >
                <p class="caricamento">Caricamento fasce orarie…</p>
            </div>
        </fieldset>

        <output
            id="msg-volontariato"
            role="status"
            aria-live="polite"
            class="sr-solo"
        ></output>

        <button
            type="submit"
            id="btn-volontariato"
            class="btn btn-primario"
            disabled
            aria-disabled="true"
        >
            Conferma turni selezionati
        </button>
        <p class="aiuto-campo" aria-live="polite" id="note-btn-volontariato">
            Seleziona almeno una fascia oraria disponibile.
        </p>
    </form>

    <?php endif; ?>
</section>

<hr class="separatore">

<section aria-labelledby="titolo-info-volontariato">
    <h2 id="titolo-info-volontariato">Cosa fare da volontario</h2>
    <ul>
        <li>Socializzare con i gatti e giocare con loro</li>
        <li>Aiutare con la pulizia degli spazi</li>
        <li>Supportare durante le visite dei potenziali adottanti</li>
        <li>Assistere il personale nella gestione della struttura</li>
    </ul>
    <p>
        Non è richiesta esperienza specifica: solo amore per i gatti e voglia di fare!
        Per maggiori informazioni scrivici a 
        <a href="mailto:info@gattile-felice.example.it">info@gattile-felice.example.it</a>.
    </p>
</section>

<script src="js/volontariato.js"></script>

<?php
chiudiMain();
stampaFooter();
chiudiHTML();
?>
