<?php
// Pagina adozioni


require_once 'includes/layout.php';

aprireSessione();
$profilo = profiloAttivo();
$loggato = ($profilo !== null);
$is_admin = $loggato && (bool) $profilo['is_admin'];

// Intestazione della pagina
$titolo_pagina = 'Adozioni';
$descrizione_pagina = 'Sfoglia i gatti disponibili per l\'adozione al Gattile San Paolo di Torino. Filtra per età, colore o nome.';


?>
<!DOCTYPE html>
<html lang="it">

<?php require 'includes/head.php'; ?>
<?php require 'includes/header.php'; ?>
<main id="contenuto-principale">

    <!-- intestazione -->
    <section aria-labelledby="titolo-gatti">
        <h1 id="titolo-gatti">I nostri ospiti felini</h1>
        <p>Seleziona i nostri gatti e prenota una visita conoscitiva.</p>
        <?php if (!$loggato): ?>
            <aside class="messaggio messaggio-avviso" role="note" aria-label="Avviso accesso">
                <p>
                    Per prenotare una visita devi prima
                    <a href="login.php">accedere</a> o <a href="registrazione.php">registrarti</a>.
                    Puoi comunque <strong>sfogliare e filtrare</strong> tutti i gatti disponibili.
                </p>
            </aside>
        <?php elseif ($is_admin): ?>
            <aside class="messaggio messaggio-avviso" role="note" aria-label="Avviso prenotazione">
                <p>
                    Per prenotare una visita devi essere un utente, non un amministratore.
                    Puoi comunque <strong>sfogliare e filtrare</strong> tutti i gatti disponibili.
                </p>
            </aside>
        <?php endif; ?>
    </section>

    <!-- card gatti -->
    <section id="react-gatti-root" data-utente-loggato="<?= $loggato ? 'true' : 'false' ?>"
        data-is-admin="<?= $is_admin ? 'true' : 'false' ?>" aria-label="Elenco gatti con filtri e ordinamento"
        aria-busy="true">
        <p class="caricamento" aria-live="polite">Caricamento schede gatti in corso…</p>
    </section>

    <!-- prenotazione -->
    <?php if ($loggato && !$is_admin): ?>
        <section aria-labelledby="titolo-prenotazione">

            <h2 id="titolo-prenotazione">Prenota una visita conoscitiva</h2>

            <aside id="successo-prenotazione" aria-live="polite"></aside>

            <form id="form-prenotazione" method="post" action="interfaccia/prenota_visita.php" novalidate
                aria-label="Modulo prenotazione visita">

                <fieldset id="gatti-selezionati-riepilogo" aria-live="polite"
                    aria-label="Gatti selezionati per la visita">
                    <p class="messaggio messaggio-avviso">Nessun gatto selezionato. Clicca sulle card per sceglierli.</p>
                    <output class="errore-campo" id="err-gatti-selezione" role="alert" aria-live="polite" hidden></output>
                </fieldset>

                <input type="hidden" id="gatti-ids" name="gatti_ids" value="">

                <fieldset>
                    <legend>Scegli data e ora della visita</legend>

                    <label for="data-visita" class="campo-obbligatorio">
                        Giorno della visita
                    </label>
                    <input type="date" id="data-visita" name="data_visita" required
                        aria-describedby="aiuto-data-visita" min="<?= date('Y-m-d') ?>">
                    <em id="aiuto-data-visita" class="aiuto-campo">
                        Scegli un giorno da oggi in poi.
                    </em>
                    <output class="errore-campo" id="err-giorno-visita" role="alert" aria-live="polite" hidden></output>

                    <label for="ora-visita" class="campo-obbligatorio">
                        Orario della visita
                    </label>
                    <select id="ora-visita" name="ora_visita" required
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
                    <output class="errore-campo" id="err-ora-visita" role="alert" aria-live="polite" hidden></output>

                    <output class="errore-campo" id="err-data-visita" role="alert" aria-live="polite" hidden></output>
                </fieldset>

                <output id="msg-prenotazione" aria-live="polite" class="sr-solo"></output>
                <button type="reset" id="btn-reset-prenota" class="btn btn-secondario">
                    Cancella
                </button>
                <button type="submit" id="btn-prenota" class="btn btn-primario">
                    Conferma prenotazione
                </button>
            </form>
        </section>
    <?php endif; ?>

    <!-- React -->
    <script src="https://unpkg.com/react@18.3.1/umd/react.production.min.js" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/react-dom@18.3.1/umd/react-dom.production.min.js" crossorigin="anonymous"></script>
    <script defer src="js/GattiComponent.js"></script>

    <?php if ($loggato && !$is_admin): ?>
        <script src="js/prenotazione.js" defer></script>
    <?php endif; ?>

</main>
<?php require 'includes/footer.php'; ?>