<?php

require_once 'componenti/layout.php';
require_once 'componenti/connessione_db.php';
require_once 'componenti/gestione_log.php';

aprireSessione();

$errore = '';
$successo = '';
// Se non è admin
$reindirizzato = !esigeAdmin();

if (!$reindirizzato && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $descrizione = trim(filter_input(INPUT_POST, 'descrizione', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $peso = filter_input(INPUT_POST, 'peso', FILTER_VALIDATE_FLOAT);
    $colore_mantello = trim(filter_input(INPUT_POST, 'colore_mantello', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $lunghezza_pelo = trim(filter_input(INPUT_POST, 'lunghezza_pelo', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $razza = trim(filter_input(INPUT_POST, 'razza', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $colore_occhi = trim(filter_input(INPUT_POST, 'colore_occhi', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $eta = filter_input(
        INPUT_POST,
        'eta',
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 0, 'max_range' => 300]]
    );
    $sesso = filter_input(INPUT_POST, 'sesso', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $data_arrivo = filter_input(INPUT_POST, 'data_arrivo', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

    $errori = [];
    if (!preg_match('/^.{1,50}$/s', $nome))
        $errori[] = 'Nome: 1-50 caratteri.';
    if (!preg_match('/^.{10,}$/s', $descrizione))
        $errori[] = 'Descrizione non valida.';
    if ($peso === false || $peso < 0.1 || $peso > 20)
        $errori[] = 'Peso: tra 0.1 e 20 kg.';
    if ($sesso !== 'M' && $sesso !== 'F')
        $errori[] = 'Seleziona il sesso del gatto.';
    // Controlla formato e validità della data.
    $data_valida = false;
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_arrivo)) {
        $pd = explode('-', $data_arrivo);
        $ts_data = mktime(0, 0, 0, (int) $pd[1], (int) $pd[2], (int) $pd[0]);
        if ($ts_data !== false && date('Y-m-d', $ts_data) === $data_arrivo) {
            $data_valida = true;
        }
    }
    if (!$data_valida)
        $errori[] = 'Data di arrivo non valida.';
    if ($eta === false)
        $errori[] = 'Età in mesi: numero intero tra 0 e 300.';

    if (empty($errori)) {
        $conn = connessioneDb('modifier');
        if (!$conn) {
            $errore = "Errore del database durante l'inserimento. Riprova tra qualche minuto.";
        } else {
            $stm = mysqli_prepare(
                $conn,
                'INSERT INTO gatti
                 (nome, descrizione, peso, colore_mantello, lunghezza_pelo,
                  razza, colore_occhi, eta, sesso, data_arrivo)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            if (!$stm) {
                scriviLog('errore', 'inserisci_gatto: prepare fallita - ' . mysqli_error($conn));
                $errore = "Errore del database durante l'inserimento. Riprova tra qualche minuto.";
            } else {
                mysqli_stmt_bind_param(
                    $stm,
                    'ssdssssiss',
                    $nome,
                    $descrizione,
                    $peso,
                    $colore_mantello,
                    $lunghezza_pelo,
                    $razza,
                    $colore_occhi,
                    $eta,
                    $sesso,
                    $data_arrivo
                );
                if (!mysqli_stmt_execute($stm)) {
                    scriviLog('errore', 'inserisci_gatto: execute fallita - ' . mysqli_stmt_error($stm));
                    $errore = "Errore del database durante l'inserimento. Riprova tra qualche minuto.";
                    mysqli_stmt_close($stm);
                } else {
                    // Salvo l'esito in sessione e reindirizzo
                    scriviLog('info', 'inserisci_gatto: nuovo gatto inserito - ' . $nome);
                    impostaMessaggioFlash(
                        'successo',
                        'Gatto «' . $nome . '» inserito con successo (immagine placeholder assegnata).'
                    );
                    mysqli_stmt_close($stm);
                    mysqli_close($conn);
                    header('Location: inserisci_gatto.php');
                    $reindirizzato = true;
                    exit;
                }
            }
            if (!$reindirizzato) {
                mysqli_close($conn);
            }
        }
    } else {
        $errore = implode(' ', $errori);
    }
}

// Recupero l'eventuale messaggio di successo
$flash = leggiMessaggioFlash();
if ($flash) {
    if ($flash['tipo'] === 'successo') {
        $successo = $flash['testo'];
    } else {
        $errore = $flash['testo'];
    }
}

// Intestazione della pagina
$titolo_pagina = 'Inserisci nuovo gatto';
$descrizione_pagina = 'Area riservata: inserisci un nuovo gatto nella struttura.';

// Se è avvenuto il redirect, non si produce alcun output HTML.
if (!$reindirizzato):
    ?>
    <!DOCTYPE html>
    <html lang="it">

    <?php require 'componenti/head.php'; ?>
    <?php require 'componenti/header.php'; ?>
    <main id="contenuto-principale">

        <!-- intestazione -->
        <section aria-labelledby="titolo-inserisci">
            <h1 id="titolo-inserisci">Inserisci un nuovo ospite</h1>
            <p>
                <strong>Area amministrativa.</strong>
                Il sistema assegna automaticamente un'immagine
                <abbr title="Immagine di segnaposto">placeholder</abbr>.
                Le foto reali saranno disponibili in una futura versione.
            </p>
        </section>

        <!-- form inserisci gatto -->
        <section>
            <h2 class="sr-solo">Inserisci gatto</h2>
            <?php if ($errore):
                echo avvisoUtente($errore, 'errore');
            endif; ?>
            <?php if ($successo):
                echo avvisoUtente($successo, 'successo'); ?>
                <p>
                    <a href="gatti.php" class="btn btn-primario">Vedi tutti i gatti</a>
                    <a href="inserisci_gatto.php" class="btn btn-primario">Aggiungi un altro gatto</a>
                </p>
            <?php endif; ?>

            <?php if (!$successo): ?>
                <form id="form-inserisci-gatto" method="post" action="inserisci_gatto.php" novalidate
                    aria-label="Modulo inserimento nuovo gatto">

                    <fieldset>
                        <legend>Identità del gatto</legend>
                        <label for="gatto-nome" class="campo-obbligatorio">Nome</label>
                        <input type="text" id="gatto-nome" name="nome" required maxlength="50"
                            aria-describedby="aiuto-gatto-nome" placeholder="Es. Fuffi">
                        <em id="aiuto-gatto-nome" class="aiuto-campo">Max 50 caratteri.</em>
                        <output class="errore-campo" id="err-gatto-nome" role="alert" aria-live="polite" hidden></output>
                        <label for="gatto-razza" class="campo-obbligatorio">Razza</label>
                        <input type="text" id="gatto-razza" name="razza" required maxlength="50"
                            aria-describedby="aiuto-gatto-razza" placeholder="Es. Europeo, Persiano">
                        <em id="aiuto-gatto-razza" class="aiuto-campo">Max 50 caratteri. Se sconosciuta, indica «Meticcio».</em>
                        <output class="errore-campo" id="err-gatto-razza" role="alert" aria-live="polite" hidden></output>
                        <label for="gatto-sesso" class="campo-obbligatorio">Sesso</label>
                        <select id="gatto-sesso" name="sesso" required>
                            <option value="">— Seleziona —</option>
                            <option value="M">Maschio</option>
                            <option value="F">Femmina</option>
                        </select>
                        <output class="errore-campo" id="err-gatto-sesso" role="alert" aria-live="polite" hidden></output>
                        <label for="gatto-eta" class="campo-obbligatorio">Età (mesi)</label>
                        <input type="number" id="gatto-eta" name="eta" required min="0" max="300"
                            aria-describedby="aiuto-gatto-eta" placeholder="Es. 24">
                        <em id="aiuto-gatto-eta" class="aiuto-campo">
                            Età espressa in mesi: numero intero tra 0 e 300 (es. 24 = 2 anni).
                        </em>
                        <output class="errore-campo" id="err-gatto-eta" role="alert" aria-live="polite" hidden></output>
                    </fieldset>

                    <fieldset>
                        <legend>Caratteristiche fisiche</legend>
                        <label for="gatto-peso" class="campo-obbligatorio">Peso (kg)</label>
                        <input type="number" id="gatto-peso" name="peso" required min="0.1" max="20" step="0.01"
                            aria-describedby="aiuto-gatto-peso" placeholder="Es. 4.20">
                        <em id="aiuto-gatto-peso" class="aiuto-campo">
                            Peso in chilogrammi: valore tra 0.1 e 20 kg (decimali con il punto, es. 4.20).
                        </em>
                        <output class="errore-campo" id="err-gatto-peso" role="alert" aria-live="polite" hidden></output>
                        <label for="gatto-colore-mantello" class="campo-obbligatorio">Colore del mantello</label>
                        <input type="text" id="gatto-colore-mantello" name="colore_mantello" required maxlength="30"
                            aria-describedby="aiuto-gatto-colore-mantello" placeholder="Es. Tigrato, Bianco">
                        <em id="aiuto-gatto-colore-mantello" class="aiuto-campo">Max 30 caratteri.</em>
                        <output class="errore-campo" id="err-gatto-colore-mantello" role="alert" aria-live="polite"
                            hidden></output>
                        <label for="gatto-lunghezza-pelo" class="campo-obbligatorio">Lunghezza del pelo </label>
                        <select id="gatto-lunghezza-pelo" name="lunghezza_pelo" required
                            aria-describedby="aiuto-gatto-lunghezza-pelo">
                            <option value="">— Seleziona —</option>
                            <option value="Corto">Corto</option>
                            <option value="Medio">Medio</option>
                            <option value="Lungo">Lungo</option>
                        </select>
                        <em id="aiuto-gatto-lunghezza-pelo" class="aiuto-campo">Scegli tra pelo corto, medio o lungo.</em>
                        <output class="errore-campo" id="err-gatto-lunghezza-pelo" role="alert" aria-live="polite"
                            hidden></output>
                        <label for="gatto-colore-occhi" class="campo-obbligatorio">Colore degli occhi </label>
                        <input type="text" id="gatto-colore-occhi" name="colore_occhi" required maxlength="30"
                            aria-describedby="aiuto-gatto-colore-occhi" placeholder="Es. Verdi, Azzurri">
                        <em id="aiuto-gatto-colore-occhi" class="aiuto-campo">Max 30 caratteri.</em>
                        <output class="errore-campo" id="err-gatto-colore-occhi" role="alert" aria-live="polite"
                            hidden></output>
                    </fieldset>

                    <fieldset>
                        <legend>Arrivo in struttura</legend>
                        <label for="gatto-data-arrivo" class="campo-obbligatorio">Data di arrivo</label>
                        <input type="date" id="gatto-data-arrivo" name="data_arrivo" required
                            aria-describedby="aiuto-gatto-data-arrivo" max="<?= date('Y-m-d') ?>">
                        <em id="aiuto-gatto-data-arrivo" class="aiuto-campo">Giorno in cui il gatto è arrivato in struttura. Non
                            può essere una data futura.</em>
                        <output class="errore-campo" id="err-gatto-data-arrivo" role="alert" aria-live="polite" hidden></output>

                        <label for="gatto-descrizione" class="campo-obbligatorio">Descrizione carattere e storia</label>
                        <textarea id="gatto-descrizione" name="descrizione" required aria-describedby="aiuto-gatto-descrizione"
                            minlength="10" maxlength="2000" rows="5"
                            placeholder="Racconta la personalità del gatto…"></textarea>
                        <em id="aiuto-gatto-descrizione" class="aiuto-campo">
                            Almeno 10 caratteri. Rimanenti: <output id="contatore-desc">2000</output>.
                        </em>
                        <output class="errore-campo" id="err-gatto-descrizione" role="alert" aria-live="polite" hidden></output>

                    </fieldset>
                    <label class="campo-obbligatorio nota-obbligatori">Campi obbligatori</label>
                    <button type="reset" id="btn-reset-inserisci" class="btn btn-secondario">
                        Cancella
                    </button>
                    <button type="submit" id="btn-inserisci" class="btn btn-primario">
                        Salva scheda gatto
                    </button>
                </form>
            <?php endif; ?>
        </section>

        <script src="js/inserisci_gatto.js" defer></script>
    </main>
    <?php require 'componenti/footer.php'; ?>
<?php endif; ?>