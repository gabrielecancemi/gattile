<?php
declare(strict_types=1);

require_once 'includes/layout.php';
require_once 'includes/db.php';

aprireSessione();
if (profiloAttivo()) {
    header('Location: index.php');
    exit;
}

$errore = '';
$successo = '';
$campi = ['nome' => '', 'cognome' => '', 'indirizzo' => '', 'username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $cognome = trim(filter_input(INPUT_POST, 'cognome', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $indirizzo = trim(filter_input(INPUT_POST, 'indirizzo', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $password = $_POST['password'] ?? '';
    $conferma = $_POST['conferma_password'] ?? '';
    $campi = compact('nome', 'cognome', 'indirizzo', 'username');

    $errori = [];
    if (strlen($nome) < 2)
        $errori[] = 'Il nome deve avere almeno 2 caratteri.';
    if (strlen($cognome) < 2)
        $errori[] = 'Il cognome deve avere almeno 2 caratteri.';
    if (strlen($indirizzo) < 5)
        $errori[] = 'Inserisci un indirizzo valido (min. 5 caratteri).';
    if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]{2,49}$/', $username))
        $errori[] = 'Username non valido: inizia con lettera, 3-50 caratteri alfanumerici o _.';
    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,16}$/', $password))
        $errori[] = 'Password: 8-16 caratteri con almeno una maiuscola, minuscola, numero e carattere speciale.';
    if ($password !== $conferma)
        $errori[] = 'Le due password non coincidono.';

    if (empty($errori)) {

        // Unicità username.
        $conn_lettura = connessioneDb('reader');
        if (!$conn_lettura) {
            $errore = 'Errore del database. Riprova tra qualche minuto.';
        } else {
            $controllo = mysqli_prepare($conn_lettura, 'SELECT id FROM utenti WHERE username = ? LIMIT 1');
            if (!$controllo) {
                error_log('[registrazione] prepare controllo: ' . mysqli_error($conn_lettura));
                $errore = 'Errore del database. Riprova tra qualche minuto.';
                mysqli_close($conn_lettura);
            } else {
                mysqli_stmt_bind_param($controllo, 's', $username);
                mysqli_stmt_execute($controllo);
                $risultato = mysqli_stmt_get_result($controllo);
                $esiste = mysqli_fetch_assoc($risultato);
                mysqli_stmt_close($controllo);
                mysqli_close($conn_lettura);

                if ($esiste) {
                    $errore = 'Username già in uso. Scegline un altro.';
                } else {
                    $conn_inserimento = connessioneDb('registrator');
                    if (!$conn_inserimento) {
                        $errore = 'Errore del database. Riprova tra qualche minuto.';
                    } else {
                        // Password salvata in chiaro nel DB (vedi note-progetto.txt).
                        $password_salvataggio = preparaPasswordSalvataggio($password);
                        $inserimento = mysqli_prepare(
                            $conn_inserimento,
                            'INSERT INTO utenti (nome, cognome, indirizzo, username, password, is_admin)
                             VALUES (?, ?, ?, ?, ?, FALSE)'
                        );
                        if (!$inserimento) {
                            error_log('[registrazione] prepare insert: ' . mysqli_error($conn_inserimento));
                            $errore = 'Errore del database. Riprova tra qualche minuto.';
                        } else {
                            mysqli_stmt_bind_param($inserimento, 'sssss', $nome, $cognome, $indirizzo, $username, $password_salvataggio);
                            if (!mysqli_stmt_execute($inserimento)) {
                                error_log('[registrazione] execute: ' . mysqli_stmt_error($inserimento));
                                $errore = 'Errore del database. Riprova tra qualche minuto.';
                            } else {
                                $successo = "Registrazione avvenuta! Ora puoi effettuare l'accesso.";
                                $campi = ['nome' => '', 'cognome' => '', 'indirizzo' => '', 'username' => ''];
                            }
                            mysqli_stmt_close($inserimento);
                        }
                        mysqli_close($conn_inserimento);
                    }
                }
            }
        }
    } else {
        $errore = implode(' ', $errori);
    }
}

// Intestazione della pagina (titolo + descrizione per SEO).
$titolo_pagina = 'Registrazione';
$descrizione_pagina = 'Crea il tuo profilo Gattile San Paolo.';

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


    <section aria-labelledby="titolo-registrazione">
        <h1 id="titolo-registrazione">Crea il tuo profilo</h1>
        <p>Già registrato? <a href="login.php">Accedi qui</a>.</p>
    </section>
    <section>
        <?php if ($errore):
            echo avvisoUtente($errore, 'errore');
        endif; ?>
        <?php if ($successo):
            echo avvisoUtente($successo, 'successo'); ?>
            <p><a href="login.php" class="btn btn-primario">Vai al login</a></p>
        <?php endif; ?>

        <?php if (!$successo): ?>
            <form id="form-registrazione" method="post" action="registrazione.php" novalidate
                aria-label="Modulo di registrazione">

                <fieldset>
                    <legend>Dati anagrafici</legend>
                    <label for="reg-nome" class="campo-obbligatorio">
                        Nome</label>
                    <input type="text" id="reg-nome" name="nome" value="<?= ripulisci($campi['nome']) ?>"
                        autocomplete="given-name" required maxlength="50">
                    <output class="errore-campo" id="err-nome" role="alert" aria-live="polite" hidden></output>

                    <label for="reg-cognome" class="campo-obbligatorio">
                        Cognome</label>
                    <input type="text" id="reg-cognome" name="cognome" value="<?= ripulisci($campi['cognome']) ?>"
                        autocomplete="family-name" required maxlength="50">
                    <output class="errore-campo" id="err-cognome" role="alert" aria-live="polite" hidden></output>

                    <label for="reg-indirizzo" class="campo-obbligatorio">
                        Indirizzo</label>
                    <input type="text" id="reg-indirizzo" name="indirizzo" value="<?= ripulisci($campi['indirizzo']) ?>"
                        required maxlength="100" placeholder="Via/Corso, numero, città">
                    <output class="errore-campo" id="err-indirizzo" role="alert" aria-live="polite" hidden></output>

                </fieldset>

                <fieldset>
                    <legend>Credenziali di accesso</legend>
                    <label for="reg-username" class="campo-obbligatorio">
                        Username</label>
                    <input type="text" id="reg-username" name="username" value="<?= ripulisci($campi['username']) ?>"
                        autocomplete="username" required aria-describedby="aiuto-reg-username" maxlength="50"
                        spellcheck="false" pattern="[a-zA-Z][a-zA-Z0-9_]{2,49}">
                    <em id="aiuto-reg-username" class="aiuto-campo">
                        Inizia con una lettera; solo lettere, numeri e underscore; 3-50 caratteri.
                    </em>
                    <output class="errore-campo" id="err-reg-username" role="alert" aria-live="polite" hidden></output>

                    <label for="reg-password" class="campo-obbligatorio">
                        Password</label>
                    <input type="password" id="reg-password" name="password" autocomplete="new-password" required
                        aria-describedby="aiuto-reg-password" minlength="8" maxlength="16">
                    <em id="aiuto-reg-password" class="aiuto-campo">
                        8-16 caratteri: almeno una maiuscola, una minuscola, un numero e un carattere speciale.
                    </em>
                    <output class="errore-campo" id="err-reg-password" role="alert" aria-live="polite" hidden></output>
                    <label for="forza-password" class="sr-solo">Forza della password</label>
                    <meter id="forza-password" min="0" max="4" low="2" high="3" optimum="4" value="0"
                        aria-label="Forza della password" title="Forza password: 0 debole, 4 ottima"></meter>
                    <em id="forza-password-testo" class="aiuto-campo" aria-live="polite"></em>

                    <label for="reg-conferma" class="campo-obbligatorio">
                        Conferma password</label>
                    <input type="password" id="reg-conferma" name="conferma_password" autocomplete="new-password"
                        required minlength="8" maxlength="16">
                    <output class="errore-campo" id="err-reg-conferma" role="alert" aria-live="polite" hidden></output>

                </fieldset>

                <p>
                    <label for="progresso-form" class="sr-solo">Completamento modulo</label>
                    <progress id="progresso-form" max="6" value="0"
                        aria-label="Completamento modulo di registrazione"></progress>
                    <em id="progresso-testo" class="aiuto-campo" aria-live="polite">Compila tutti i campi per
                        procedere.</em>
                </p>
                <p class="campo-obbligatorio nota-obbligatori">Campi obbligatori</p>

                <button type="submit" id="btn-registra" class="btn btn-primario" disabled>
                    Crea profilo
                </button>
            </form>
        <?php endif; ?>
    </section>

    <script src="js/registrazione.js" defer></script>
</main>
<?php require 'includes/footer.php'; ?>