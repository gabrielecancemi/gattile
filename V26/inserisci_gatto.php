<?php
declare(strict_types=1);

require_once 'includes/layout.php';
require_once 'includes/db.php';

aprireSessione();
esigeAdmin();

$errore = '';
$successo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    if (strlen($nome) < 1 || strlen($nome) > 50)
        $errori[] = 'Nome: 1-50 caratteri.';
    if (strlen($descrizione) < 10)
        $errori[] = 'Descrizione non valida.';
    if ($peso === false || $peso < 0.1 || $peso > 20)
        $errori[] = 'Peso: tra 0.1 e 20 kg.';
    if (!in_array($sesso, ['M', 'F'], true))
        $errori[] = 'Seleziona il sesso del gatto.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_arrivo) || !strtotime($data_arrivo))
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
                error_log('[inserisci_gatto] prepare: ' . mysqli_error($conn));
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
                    error_log('[inserisci_gatto] execute: ' . mysqli_stmt_error($stm));
                    $errore = "Errore del database durante l'inserimento. Riprova tra qualche minuto.";
                } else {
                    $successo = 'Gatto «' . ripulisci($nome) . '» inserito con successo (immagine placeholder assegnata).';
                }
                mysqli_stmt_close($stm);
            }
            mysqli_close($conn);
        }
    } else {
        $errore = implode(' ', $errori);
    }
}

// Intestazione della pagina (titolo + descrizione per SEO).
$titolo_pagina = 'Inserisci nuovo gatto';
$descrizione_pagina = 'Area riservata: inserisci un nuovo gatto nella struttura.';

// Header di sicurezza HTTP: difesa in profondità contro XSS, clickjacking e
// MIME-sniffing. Vanno emessi prima di qualsiasi output.
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    // CSP: tutto dal proprio dominio, React/ReactDOM solo da unpkg. Niente
    // 'unsafe-inline' perché nel sito non uso script o stili inline.
    header(
        "Content-Security-Policy: "
        . "default-src 'self'; "
        . "script-src 'self' https://unpkg.com; "
        . "style-src 'self'; "
        . "img-src 'self' data:; "
        . "connect-src 'self'; "
        . "base-uri 'self'; "
        . "form-action 'self'; "
        . "frame-ancestors 'none'; "
        . "object-src 'none'"
    );
}

?>
<!DOCTYPE html>
<html lang="it">

<?php require 'includes/head.php'; ?>
<?php require 'includes/header.php'; ?>
<main id="contenuto-principale" tabindex="-1">


    <section aria-labelledby="titolo-inserisci">
        <h1 id="titolo-inserisci">Inserisci un nuovo ospite</h1>
        <p>
            <strong>Area amministrativa.</strong>
            Il sistema assegna automaticamente un'immagine
            <abbr title="Immagine di segnaposto">placeholder</abbr>.
            Le foto reali saranno disponibili in una futura versione.
        </p>
    </section>
    <section>

        <?php if ($errore):
            echo avvisoUtente($errore, 'errore');
        endif; ?>
        <?php if ($successo):
            echo avvisoUtente($successo, 'successo'); ?>
            <p><a href="gatti.php" class="btn btn-primario">Vedi tutti i gatti</a></p>
        <?php endif; ?>

        <form id="form-inserisci-gatto" method="post" action="inserisci_gatto.php" novalidate
            aria-label="Modulo inserimento nuovo gatto">

            <fieldset>
                <legend>Identità del gatto</legend>
                <label for="gatto-nome" class="campo-obbligatorio">Nome</label>
                <input type="text" id="gatto-nome" name="nome" required maxlength="50" placeholder="Es. Fuffi">
                <output class="errore-campo" id="err-gatto-nome" role="alert" aria-live="polite" hidden></output>
                <label for="gatto-razza" class="campo-obbligatorio">Razza</label>
                <input type="text" id="gatto-razza" name="razza" required maxlength="50"
                    placeholder="Es. Europeo, Persiano">
                <output class="errore-campo" id="err-gatto-razza" role="alert" aria-live="polite" hidden></output>
                <label for="gatto-sesso" class="campo-obbligatorio">Sesso</label>
                <select id="gatto-sesso" name="sesso" required>
                    <option value="">— Seleziona —</option>
                    <option value="M">Maschio</option>
                    <option value="F">Femmina</option>
                </select>
                <output class="errore-campo" id="err-gatto-sesso" role="alert" aria-live="polite" hidden></output>
                <label for="gatto-eta" class="campo-obbligatorio">Età (mesi)</label>
                <input type="number" id="gatto-eta" name="eta" required min="0" max="300" placeholder="Es. 24">
                <output class="errore-campo" id="err-gatto-eta" role="alert" aria-live="polite" hidden></output>
            </fieldset>

            <fieldset>
                <legend>Caratteristiche fisiche</legend>
                <label for="gatto-peso" class="campo-obbligatorio">Peso (kg)</label>
                <input type="number" id="gatto-peso" name="peso" required min="0.1" max="20" step="0.01"
                    placeholder="Es. 4.20">
                <output class="errore-campo" id="err-gatto-peso" role="alert" aria-live="polite" hidden></output>
                <label for="gatto-colore-mantello" class="campo-obbligatorio">Colore del mantello</label>
                <input type="text" id="gatto-colore-mantello" name="colore_mantello" required maxlength="30"
                    placeholder="Es. Tigrato, Bianco">
                <output class="errore-campo" id="err-gatto-colore-mantello" role="alert" aria-live="polite"
                    hidden></output>
                <label for="gatto-lunghezza-pelo" class="campo-obbligatorio">Lunghezza del pelo </label>
                <select id="gatto-lunghezza-pelo" name="lunghezza_pelo" required>
                    <option value="">— Seleziona —</option>
                    <option value="Corto">Corto</option>
                    <option value="Medio">Medio</option>
                    <option value="Lungo">Lungo</option>
                </select>
                <output class="errore-campo" id="err-gatto-lunghezza-pelo" role="alert" aria-live="polite"
                    hidden></output>
                <label for="gatto-colore-occhi" class="campo-obbligatorio">Colore degli occhi </label>
                <input type="text" id="gatto-colore-occhi" name="colore_occhi" required maxlength="30"
                    placeholder="Es. Verdi, Azzurri">
                <output class="errore-campo" id="err-gatto-colore-occhi" role="alert" aria-live="polite"
                    hidden></output>
            </fieldset>

            <fieldset>
                <legend>Arrivo in struttura</legend>
                <label for="gatto-data-arrivo" class="campo-obbligatorio">Data di arrivo</label>
                <input type="date" id="gatto-data-arrivo" name="data_arrivo" required max="<?= date('Y-m-d') ?>">
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

            <button type="submit" id="btn-inserisci" class="btn btn-primario">
                Salva scheda gatto
            </button>
        </form>
    </section>

    <script src="js/inserisci_gatto.js" defer></script>
</main>
<?php require 'includes/footer.php'; ?>