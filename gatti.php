<?php
/**
 * gatti.php — Pagina principale di adozione.
 * Il componente React mostra e filtra le card dei gatti (via API JSON).
 * Il form prenotazione visita è in Vanilla JS.
 * La comunicazione React → form avviene tramite CustomEvent.
 */
declare(strict_types=1);

require_once 'includes/layout.php';

avviaSessione();
$utente   = utenteLoggato();
$loggato  = ($utente !== null);
$isAdmin  = $loggato && (bool)$utente['is_admin'];

stampaTesta(
    'Adotta un gatto',
    'Sfoglia i gatti disponibili per l\'adozione nel Gattile Felice di Torino. Filtra per età, colore o nome.',
    'gatti.php'
);
echo '<body>';
stampaHeader();
stampaBannerCookie();
apriMain();
?>

<section aria-labelledby="titolo-gatti">
    <h1 id="titolo-gatti">I nostri ospiti felini</h1>

    <?php if (!$loggato): ?>
        <aside class="messaggio messaggio-avviso" role="note" aria-label="Avviso accesso">
            <p>
                <strong>ℹ️ Per prenotare una visita</strong> devi prima 
                <a href="login.php">accedere</a> o 
                <a href="registrazione.php">registrarti</a>.
                Puoi comunque sfogliare e filtrare tutti i gatti disponibili.
            </p>
        </aside>
    <?php endif; ?>

    <!--
        Contenitore del componente React.
        React usa 'data-utente-loggato' e 'data-is-admin'
        per sapere se mostrare la selezione.
    -->
    <div
        id="react-gatti-root"
        data-utente-loggato="<?= $loggato ? 'true' : 'false' ?>"
        data-is-admin="<?= $isAdmin ? 'true' : 'false' ?>"
        aria-label="Elenco gatti con filtri e ordinamento"
        aria-busy="true"
    >
        <!-- Fallback non-JS / caricamento -->
        <p class="caricamento" aria-live="polite">
            Caricamento schede gatti in corso…
        </p>
    </div>
</section>

<?php if ($loggato && !$isAdmin): ?>
<!-- Form prenotazione visita — Vanilla JS, fuori dal componente React -->
<hr class="separatore">

<section aria-labelledby="titolo-prenotazione">
    <h2 id="titolo-prenotazione">Prenota una visita conoscitiva</h2>
    <p>
        Seleziona prima i gatti di tuo interesse dalle card qui sopra,
        poi scegli data e ora per la tua visita in struttura.
    </p>

    <!-- Lista gatti selezionati (aggiornata via CustomEvent da React) -->
    <output
        id="gatti-selezionati-riepilogo"
        aria-live="polite"
        aria-label="Gatti selezionati per la visita"
    >
        <p class="messaggio messaggio-avviso">Nessun gatto selezionato. Clicca sulle card per sceglierli.</p>
    </output>

    <form
        id="form-prenotazione"
        method="post"
        action="api/prenota_visita.php"
        novalidate
        aria-label="Modulo prenotazione visita"
    >
        <!-- Campo nascosto con gli id dei gatti (valorizzato da Vanilla JS) -->
        <input type="hidden" id="gatti-ids" name="gatti_ids" value="">

        <fieldset>
            <legend>Scegli data e ora della visita</legend>

            <label for="data-visita" class="campo-obbligatorio">
                Data e ora
                <input
                    type="datetime-local"
                    id="data-visita"
                    name="data_ora"
                    required
                    aria-required="true"
                    aria-describedby="aiuto-data-visita"
                    min="<?= date('Y-m-d\TH:i') ?>"
                >
                <span id="aiuto-data-visita" class="aiuto-campo">
                    Seleziona una data futura. Gli orari disponibili sono dalle 9:00 alle 18:00.
                </span>
                <span class="errore-campo" id="err-data-visita" role="alert" aria-live="polite" hidden></span>
            </label>
        </fieldset>

        <output
            id="msg-prenotazione"
            role="status"
            aria-live="polite"
            class="sr-solo"
        ></output>

        <button type="submit" id="btn-prenota" class="btn btn-primario" disabled aria-disabled="true">
            Conferma prenotazione
        </button>
        <p class="aiuto-campo" aria-live="polite" id="note-btn-prenota">
            Seleziona almeno un gatto e una data per abilitare la prenotazione.
        </p>
    </form>
</section>
<?php endif; ?>

<!-- React (via CDN per semplicità; in produzione: bundler) -->
<script src="https://unpkg.com/react@18/umd/react.production.min.js" crossorigin="anonymous"></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js" crossorigin="anonymous"></script>
<!-- Babel standalone solo in development — in produzione compilare con build tool -->
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

<!-- Componente React -->
<script type="text/babel" src="js/GattiComponent.jsx" data-presets="react"></script>

<?php if ($loggato && !$isAdmin): ?>
<!-- Form prenotazione Vanilla JS -->
<script src="js/prenotazione.js"></script>
<?php endif; ?>

<?php
chiudiMain();
stampaFooter();
chiudiHTML();
?>
