<?php
/**
 * volontariato.php — Prenotazione turni volontariato.
 */
declare(strict_types=1);

require_once 'includes/layout.php';

avviaSessione();
$loggato = (utenteLoggato() !== null);

stampaTesta(
    'Volontariato',
    'Diventa volontario al Gattile Felice di Torino: scegli le fasce orarie in cui prestare aiuto.',
    'volontariato.php'
);
stampaHeader();
apriMain();
?>

<section aria-labelledby="titolo-volontariato">
    <h1 id="titolo-volontariato">Fai volontariato</h1>
    <p>
        Il tuo aiuto fa la differenza. Scegli quante fasce orarie vuoi.
        La struttura accoglie <strong>al massimo 2 volontari per fascia oraria</strong>.
    </p>

    <?php if (!$loggato): ?>
        <aside class="messaggio messaggio-avviso" role="note">
            <p>
                Per prenotare un turno devi prima
                <a href="login.php">accedere</a> o
                <a href="registrazione.php">registrarti</a>.
            </p>
        </aside>
    <?php else: ?>

    <form id="form-volontariato" method="post" action="api/prenota_turno.php"
          novalidate aria-label="Modulo prenotazione turni volontariato">
        <fieldset>
            <legend>Seleziona le fasce orarie</legend>
            <p class="aiuto-campo">
                Le fasce con 2/2 volontari sono disabilitate automaticamente.
            </p>
            <section id="contenitore-turni" aria-live="polite" aria-busy="true"
                 aria-label="Fasce orarie disponibili">
                <p class="caricamento">Caricamento fasce orarie…</p>
            </section>
        </fieldset>

        <output id="msg-volontariato" role="status" aria-live="polite" class="sr-solo"></output>

        <button type="submit" id="btn-volontariato" class="btn btn-primario"
                disabled aria-disabled="true">
            Conferma turni selezionati
        </button>
        <p class="aiuto-campo" aria-live="polite" id="note-btn-volontariato">
            Seleziona almeno una fascia oraria disponibile.
        </p>
    </form>

    <?php endif; ?>
</section>

<hr class="separatore">

<section aria-labelledby="titolo-info-vol">
    <h2 id="titolo-info-vol">Cosa fare da volontario</h2>
    <ul>
        <li>Socializzare con i gatti e giocare con loro</li>
        <li>Aiutare con la pulizia degli spazi</li>
        <li>Supportare durante le visite dei potenziali adottanti</li>
        <li>Assistere il personale nella gestione della struttura</li>
    </ul>
    <p>
        Non è richiesta alcuna esperienza specifica.
        Per info: <a href="mailto:info@gattile-felice.example.it">info@gattile-felice.example.it</a>.
    </p>
</section>

<script src="js/volontariato.js" defer></script>

<?php chiudiMain(); stampaFooter(); ?>
