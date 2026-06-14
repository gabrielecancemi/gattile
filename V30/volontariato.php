<?php
// Prenotazione turni di volontariato.

require_once 'includes/layout.php';

aprireSessione();
$loggato = (profiloAttivo() !== null);

// Intestazione della pagina
$titolo_pagina = 'Volontariato';
$descrizione_pagina = 'Diventa volontario al Gattile San Paolo di Torino: scegli le fasce orarie in cui prestare aiuto.';


?>
<!DOCTYPE html>
<html lang="it">

<?php require 'includes/head.php'; ?>
<?php require 'includes/header.php'; ?>
<main id="contenuto-principale" class="volontario-main">

    <!-- intestazione -->
    <section aria-labelledby="titolo-volontariato">
        <h1 id="titolo-volontariato">Fai volontariato</h1>
        <p>
            Il tuo aiuto fa la differenza. Scegli quante fasce orarie vuoi.
            La struttura accoglie al <strong>massimo 2 volontari per fascia oraria</strong>.
        </p>
        <?php if (!$loggato): ?>
            <aside class="messaggio messaggio-avviso" role="note">
                <p>
                    Per prenotare un turno devi prima
                    <a href="login.php">accedere</a> o
                    <a href="registrazione.php">registrarti</a>.
                </p>
            </aside>
        <?php endif; ?>
    </section>

    <!-- ruoli volontario -->
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

    <!-- form turni volontario -->
    <?php if ($loggato): ?>
        <section>
            <h2>Prenota i turni</h2>
            <aside id="successo-volontariato" aria-live="polite"></aside>
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
                    <output class="errore-campo" id="err-data-turno" role="alert" aria-live="polite" hidden></output>

                    <section id="contenitore-turni" aria-live="polite" aria-busy="true"
                        aria-label="Fasce orarie disponibili">
                        <p class="caricamento">Caricamento fasce orarie…</p>
                    </section>
                    <output class="errore-campo" id="err-fasce-turni" role="alert" aria-live="polite" hidden></output>
                </fieldset>

                <output id="msg-volontariato" aria-live="polite" class="sr-solo"></output>
                <button type="reset" id="btn-reset-volontariato" class="btn btn-secondario">
                    Cancella
                </button>
                <button type="submit" id="btn-volontariato" class="btn btn-primario">
                    Conferma turni selezionati
                </button>
            </form>
        </section>
    <?php endif; ?>

    <script src="js/volontariato.js" defer></script>

</main>
<?php require 'includes/footer.php'; ?>