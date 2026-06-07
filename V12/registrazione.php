<?php
declare(strict_types=1);

require_once 'includes/layout.php';
require_once 'includes/db.php';

avviaSessione();
if (utenteLoggato()) {
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

        // Verifica unicità username
        $connR = getDB('reader');
        if (!$connR) {
            $errore = 'Errore del database. Riprova tra qualche minuto.';
        } else {
            $check = mysqli_prepare($connR, 'SELECT id FROM utenti WHERE username = ? LIMIT 1');
            if (!$check) {
                error_log('[registrazione] Prepare check: ' . mysqli_error($connR));
                $errore = 'Errore del database. Riprova tra qualche minuto.';
                mysqli_close($connR);
            } else {
                mysqli_stmt_bind_param($check, 's', $username);
                mysqli_stmt_execute($check);
                $res = mysqli_stmt_get_result($check);
                $exists = mysqli_fetch_assoc($res);
                mysqli_stmt_close($check);
                mysqli_close($connR);

                if ($exists) {
                    $errore = 'Username già in uso. Scegline un altro.';
                } else {
                    // Inserimento con utente registratore (solo INSERT)
                    $connI = getDB('registrator');
                    if (!$connI) {
                        $errore = 'Errore del database. Riprova tra qualche minuto.';
                    } else {
                        // Password salvata IN CHIARO (scelta non sicura, vedi progettoSito.txt)
                        $hash = creaHashPassword($password);
                        $ins = mysqli_prepare(
                            $connI,
                            'INSERT INTO utenti (nome, cognome, indirizzo, username, password, is_admin)
                             VALUES (?, ?, ?, ?, ?, FALSE)'
                        );
                        if (!$ins) {
                            error_log('[registrazione] Prepare insert: ' . mysqli_error($connI));
                            $errore = 'Errore del database. Riprova tra qualche minuto.';
                        } else {
                            mysqli_stmt_bind_param($ins, 'sssss', $nome, $cognome, $indirizzo, $username, $hash);
                            if (!mysqli_stmt_execute($ins)) {
                                error_log('[registrazione] Execute: ' . mysqli_stmt_error($ins));
                                $errore = 'Errore del database. Riprova tra qualche minuto.';
                            } else {
                                $successo = "Registrazione avvenuta! Ora puoi effettuare l'accesso.";
                                $campi = ['nome' => '', 'cognome' => '', 'indirizzo' => '', 'username' => ''];
                            }
                            mysqli_stmt_close($ins);
                        }
                        mysqli_close($connI);
                    }
                }
            }
        }
    } else {
        $errore = implode(' ', $errori);
    }
}

stampaTesta('Registrazione', 'Crea il tuo profilo Gattile San Paolo.');
stampaHeader();
apriMain();
?>

<section aria-labelledby="titolo-registrazione">
    <h1 id="titolo-registrazione">Crea il tuo profilo</h1>
    <p>Già registrato? <a href="login.php">Accedi qui</a>.</p>
</section>
<section>
    <?php if ($errore):
        echo messaggioUtente($errore, 'errore');
    endif; ?>
    <?php if ($successo):
        echo messaggioUtente($successo, 'successo'); ?>
        <p><a href="login.php" class="btn btn-primario">Vai al login</a></p>
    <?php endif; ?>

    <?php if (!$successo): ?>
        <form id="form-registrazione" method="post" action="registrazione.php" novalidate
            aria-label="Modulo di registrazione">

            <fieldset>
                <legend>Dati anagrafici</legend>
                <label for="reg-nome" class="campo-obbligatorio">
                    Nome</label>
                <input type="text" id="reg-nome" name="nome" value="<?= esc($campi['nome']) ?>" autocomplete="given-name"
                    required maxlength="50">
                <output class="errore-campo" id="err-nome" role="alert" aria-live="polite" hidden></output>

                <label for="reg-cognome" class="campo-obbligatorio">
                    Cognome</label>
                <input type="text" id="reg-cognome" name="cognome" value="<?= esc($campi['cognome']) ?>"
                    autocomplete="family-name" required maxlength="50">
                <output class="errore-campo" id="err-cognome" role="alert" aria-live="polite" hidden></output>

                <label for="reg-indirizzo" class="campo-obbligatorio">
                    Indirizzo</label>
                <input type="text" id="reg-indirizzo" name="indirizzo" value="<?= esc($campi['indirizzo']) ?>" required
                    maxlength="100" placeholder="Via/Corso, numero, città">
                <output class="errore-campo" id="err-indirizzo" role="alert" aria-live="polite" hidden></output>

            </fieldset>

            <fieldset>
                <legend>Credenziali di accesso</legend>
                <label for="reg-username" class="campo-obbligatorio">
                    Username</label>
                <input type="text" id="reg-username" name="username" value="<?= esc($campi['username']) ?>"
                    autocomplete="username" required aria-describedby="aiuto-reg-username" maxlength="50" spellcheck="false"
                    pattern="[a-zA-Z][a-zA-Z0-9_]{2,49}">
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
                <input type="password" id="reg-conferma" name="conferma_password" autocomplete="new-password" required
                    minlength="8" maxlength="16">
                <output class="errore-campo" id="err-reg-conferma" role="alert" aria-live="polite" hidden></output>

            </fieldset>

            <p>
                <label for="progresso-form" class="sr-solo">Completamento modulo</label>
                <progress id="progresso-form" max="6" value="0"
                    aria-label="Completamento modulo di registrazione"></progress>
                <em id="progresso-testo" class="aiuto-campo" aria-live="polite">Compila tutti i campi per procedere.</em>
            </p>
            <p class="campo-obbligatorio nota-obbligatori">Campi obbligatori</p>

            <button type="submit" id="btn-registra" class="btn btn-primario" disabled>
                Crea profilo
            </button>
        </form>
    <?php endif; ?>
</section>

<script src="js/registrazione.js" defer></script>
<?php chiudiMain();
stampaFooter(); ?>