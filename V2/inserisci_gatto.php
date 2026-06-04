<?php
/**
 * inserisci_gatto.php — Solo amministratori.
 * Inserimento nuovo gatto con immagine placeholder.
 */
declare(strict_types=1);

require_once 'includes/layout.php';
require_once 'includes/db.php';

avviaSessione();
richiedeAdmin();

$errore   = '';
$successo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome            = trim(filter_input(INPUT_POST, 'nome',            FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $descrizione     = trim(filter_input(INPUT_POST, 'descrizione',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $peso            = filter_input(INPUT_POST, 'peso',                 FILTER_VALIDATE_FLOAT);
    $colore_mantello = trim(filter_input(INPUT_POST, 'colore_mantello', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $lunghezza_pelo  = trim(filter_input(INPUT_POST, 'lunghezza_pelo',  FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $razza           = trim(filter_input(INPUT_POST, 'razza',           FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $colore_occhi    = trim(filter_input(INPUT_POST, 'colore_occhi',    FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $eta             = filter_input(INPUT_POST, 'eta',  FILTER_VALIDATE_INT,
                           ['options' => ['min_range' => 0, 'max_range' => 300]]);
    $sesso           = filter_input(INPUT_POST, 'sesso',                FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $data_arrivo     = filter_input(INPUT_POST, 'data_arrivo',          FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

    $errori = [];
    if (strlen($nome) < 1 || strlen($nome) > 50)     $errori[] = 'Nome: 1-50 caratteri.';
    if (strlen($descrizione) < 10)                    $errori[] = 'Descrizione: almeno 10 caratteri.';
    if ($peso === false || $peso < 0.1 || $peso > 20) $errori[] = 'Peso: tra 0.1 e 20 kg.';
    if (!in_array($sesso, ['M', 'F'], true))          $errori[] = 'Seleziona il sesso del gatto.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_arrivo) || !strtotime($data_arrivo))
        $errori[] = 'Data di arrivo non valida.';
    if ($eta === false) $errori[] = 'Età in mesi: numero intero tra 0 e 300.';

    if (empty($errori)) {
        try {
            $db  = getDB('modifier');
            $stm = $db->prepare(
                'INSERT INTO gatti
                 (nome, descrizione, peso, colore_mantello, lunghezza_pelo,
                  razza, colore_occhi, eta, sesso, data_arrivo)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stm->execute([
                $nome, $descrizione, $peso, $colore_mantello, $lunghezza_pelo,
                $razza, $colore_occhi, $eta, $sesso, $data_arrivo,
            ]);
            $successo = 'Gatto «' . esc($nome) . '» inserito con successo (immagine placeholder assegnata).';
        } catch (PDOException $e) {
            error_log('[inserisci_gatto] Errore DB: ' . $e->getMessage());
            $errore = 'Errore del database durante l\'inserimento. Riprova tra qualche minuto.';
        }
    } else {
        $errore = implode(' ', $errori);
    }
}

stampaTesta(
    'Inserisci nuovo gatto',
    'Area riservata agli amministratori: inserisci un nuovo gatto nella struttura.',
    'inserisci_gatto.php'
);
stampaHeader();
apriMain();
?>

<section aria-labelledby="titolo-inserisci">
    <h1 id="titolo-inserisci">Inserisci un nuovo ospite</h1>
    <p>
        <strong>Area amministrativa.</strong>
        Il sistema assegna automaticamente un'immagine
        <abbr title="Immagine di segnaposto">placeholder</abbr>.
        Le foto reali saranno disponibili in una futura versione.
    </p>

    <?php if ($errore):   echo messaggioUtente($errore, 'errore');   endif; ?>
    <?php if ($successo): echo messaggioUtente($successo, 'successo'); ?>
        <p><a href="gatti.php" class="btn btn-secondario">Vedi tutti i gatti</a></p>
    <?php endif; ?>

    <form id="form-inserisci-gatto" method="post" action="inserisci_gatto.php"
          novalidate aria-label="Modulo inserimento nuovo gatto">

        <fieldset>
            <legend>Identità del gatto</legend>

            <label for="gatto-nome" class="campo-obbligatorio">
                Nome
                <input type="text" id="gatto-nome" name="nome"
                       required aria-required="true" maxlength="50" placeholder="Es. Fuffi">
                <b class="errore-campo" id="err-gatto-nome" role="alert" aria-live="polite" hidden></b>
            </label>

            <label for="gatto-razza" class="campo-obbligatorio">
                Razza
                <input type="text" id="gatto-razza" name="razza"
                       required aria-required="true" maxlength="50" placeholder="Es. Europeo, Persiano">
                <b class="errore-campo" id="err-gatto-razza" role="alert" aria-live="polite" hidden></b>
            </label>

            <label for="gatto-sesso" class="campo-obbligatorio">
                Sesso
                <select id="gatto-sesso" name="sesso" required aria-required="true">
                    <option value="">— Seleziona —</option>
                    <option value="M">Maschio</option>
                    <option value="F">Femmina</option>
                </select>
                <b class="errore-campo" id="err-gatto-sesso" role="alert" aria-live="polite" hidden></b>
            </label>

            <label for="gatto-eta" class="campo-obbligatorio">
                Età (mesi)
                <input type="number" id="gatto-eta" name="eta"
                       required aria-required="true" min="0" max="300" placeholder="Es. 24">
                <b class="errore-campo" id="err-gatto-eta" role="alert" aria-live="polite" hidden></b>
            </label>
        </fieldset>

        <fieldset>
            <legend>Caratteristiche fisiche</legend>

            <label for="gatto-peso" class="campo-obbligatorio">
                Peso (kg)
                <input type="number" id="gatto-peso" name="peso"
                       required aria-required="true" min="0.1" max="20" step="0.01" placeholder="Es. 4.20">
                <b class="errore-campo" id="err-gatto-peso" role="alert" aria-live="polite" hidden></b>
            </label>

            <label for="gatto-colore-mantello" class="campo-obbligatorio">
                Colore del mantello
                <input type="text" id="gatto-colore-mantello" name="colore_mantello"
                       required aria-required="true" maxlength="30" placeholder="Es. Tigrato, Bianco">
                <b class="errore-campo" id="err-gatto-colore-mantello" role="alert" aria-live="polite" hidden></b>
            </label>

            <label for="gatto-lunghezza-pelo" class="campo-obbligatorio">
                Lunghezza del pelo
                <select id="gatto-lunghezza-pelo" name="lunghezza_pelo" required aria-required="true">
                    <option value="">— Seleziona —</option>
                    <option value="Corto">Corto</option>
                    <option value="Medio">Medio</option>
                    <option value="Lungo">Lungo</option>
                </select>
                <b class="errore-campo" id="err-gatto-lunghezza-pelo" role="alert" aria-live="polite" hidden></b>
            </label>

            <label for="gatto-colore-occhi" class="campo-obbligatorio">
                Colore degli occhi
                <input type="text" id="gatto-colore-occhi" name="colore_occhi"
                       required aria-required="true" maxlength="30" placeholder="Es. Verdi, Azzurri">
                <b class="errore-campo" id="err-gatto-colore-occhi" role="alert" aria-live="polite" hidden></b>
            </label>
        </fieldset>

        <fieldset>
            <legend>Arrivo in struttura</legend>

            <label for="gatto-data-arrivo" class="campo-obbligatorio">
                Data di arrivo
                <input type="date" id="gatto-data-arrivo" name="data_arrivo"
                       required aria-required="true" max="<?= date('Y-m-d') ?>">
                <b class="errore-campo" id="err-gatto-data-arrivo" role="alert" aria-live="polite" hidden></b>
            </label>

            <label for="gatto-descrizione" class="campo-obbligatorio">
                Descrizione carattere e storia
                <textarea id="gatto-descrizione" name="descrizione"
                          required aria-required="true"
                          aria-describedby="aiuto-gatto-descrizione"
                          minlength="10" maxlength="2000" rows="5"
                          placeholder="Racconta la personalità del gatto…"></textarea>
                <em id="aiuto-gatto-descrizione" class="aiuto-campo">
                    Almeno 10 caratteri. Rimanenti: <output id="contatore-desc">2000</output>.
                </em>
                <b class="errore-campo" id="err-gatto-descrizione" role="alert" aria-live="polite" hidden></b>
            </label>
        </fieldset>

        <button type="submit" id="btn-inserisci" class="btn btn-primario">
            Salva scheda gatto
        </button>
    </form>
</section>

<script src="js/inserisci_gatto.js" defer></script>

<?php chiudiMain(); stampaFooter(); ?>
