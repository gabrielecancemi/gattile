<?php
/**
 * registrazione.php — Registrazione nuovo utente.
 * Usa utente DB "registratore" (solo INSERT su utenti).
 */
declare(strict_types=1);

require_once 'includes/layout.php';
require_once 'includes/db.php';

avviaSessione();
if (utenteLoggato()) { header('Location: index.php'); exit; }

$errore   = '';
$successo = '';
$campi    = ['nome' => '', 'cognome' => '', 'indirizzo' => '', 'username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = trim(filter_input(INPUT_POST, 'nome',      FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $cognome   = trim(filter_input(INPUT_POST, 'cognome',   FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $indirizzo = trim(filter_input(INPUT_POST, 'indirizzo', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $username  = trim(filter_input(INPUT_POST, 'username',  FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $password  = $_POST['password'] ?? '';
    $conferma  = $_POST['conferma_password'] ?? '';
    $campi     = compact('nome', 'cognome', 'indirizzo', 'username');

    $errori = [];
    if (strlen($nome) < 2)       $errori[] = 'Il nome deve avere almeno 2 caratteri.';
    if (strlen($cognome) < 2)    $errori[] = 'Il cognome deve avere almeno 2 caratteri.';
    if (strlen($indirizzo) < 5)  $errori[] = 'Inserisci un indirizzo valido.';
    if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]{2,49}$/', $username))
        $errori[] = 'Username non valido: deve iniziare con una lettera, 3-50 caratteri alfanumerici o _.';
    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,16}$/', $password))
        $errori[] = 'Password non valida: 8-16 caratteri, almeno una maiuscola, minuscola, numero e carattere speciale.';
    if ($password !== $conferma) $errori[] = 'Le due password non coincidono.';

    if (empty($errori)) {
        try {
            $dbReader = getDB('reader');
            $check    = $dbReader->prepare('SELECT id FROM utenti WHERE username = ? LIMIT 1');
            $check->execute([$username]);
            if ($check->fetch()) {
                $errore = 'Username già in uso. Scegline un altro.';
            } else {
                $db   = getDB('registrator');
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $ins  = $db->prepare(
                    'INSERT INTO utenti (nome, cognome, indirizzo, username, password, is_admin) VALUES (?,?,?,?,?,FALSE)'
                );
                $ins->execute([$nome, $cognome, $indirizzo, $username, $hash]);
                $successo = 'Registrazione avvenuta con successo! Ora puoi effettuare l\'accesso.';
                $campi    = ['nome' => '', 'cognome' => '', 'indirizzo' => '', 'username' => ''];
            }
        } catch (PDOException $e) {
            error_log('Errore DB registrazione: ' . $e->getMessage());
            $errore = 'Errore del database durante la registrazione. Riprova tra qualche minuto.';
        }
    } else {
        $errore = implode(' ', $errori);
    }
}

stampaTesta('Registrazione', 'Registrati a Gattile San Paolo per prenotare visite o turni di volontariato.', 'registrazione.php');
stampaHeader();
apriMain();
?>

<section aria-labelledby="titolo-registrazione">
    <h1 id="titolo-registrazione">Crea il tuo profilo</h1>
    <p>Già registrato? <a href="login.php">Accedi qui</a>.</p>

    <?php if ($errore):   echo messaggioUtente($errore, 'errore');    endif; ?>
    <?php if ($successo): echo messaggioUtente($successo, 'successo'); ?>
        <p><a href="login.php" class="btn btn-primario">Vai al login</a></p>
    <?php endif; ?>

    <?php if (!$successo): ?>
    <form id="form-registrazione" method="post" action="registrazione.php" novalidate aria-label="Modulo di registrazione">
        <fieldset>
            <legend>Dati anagrafici</legend>

            <label for="reg-nome" class="campo-obbligatorio">
                Nome
                <input type="text" id="reg-nome" name="nome" value="<?= esc($campi['nome']) ?>"
                       autocomplete="given-name" required aria-required="true" maxlength="50">
                <span class="errore-campo" id="err-nome" role="alert" aria-live="polite" hidden></span>
            </label>

            <label for="reg-cognome" class="campo-obbligatorio">
                Cognome
                <input type="text" id="reg-cognome" name="cognome" value="<?= esc($campi['cognome']) ?>"
                       autocomplete="family-name" required aria-required="true" maxlength="50">
                <span class="errore-campo" id="err-cognome" role="alert" aria-live="polite" hidden></span>
            </label>

            <label for="reg-indirizzo" class="campo-obbligatorio">
                Indirizzo
                <input type="text" id="reg-indirizzo" name="indirizzo" value="<?= esc($campi['indirizzo']) ?>"
                       autocomplete="street-address" required aria-required="true" maxlength="100"
                       placeholder="Via/Corso, numero, città">
                <span class="errore-campo" id="err-indirizzo" role="alert" aria-live="polite" hidden></span>
            </label>
        </fieldset>

        <fieldset>
            <legend>Credenziali di accesso</legend>

            <label for="reg-username" class="campo-obbligatorio">
                Username
                <input type="text" id="reg-username" name="username" value="<?= esc($campi['username']) ?>"
                       autocomplete="username" required aria-required="true"
                       aria-describedby="aiuto-reg-username" maxlength="50" spellcheck="false"
                       pattern="[a-zA-Z][a-zA-Z0-9_]{2,49}">
                <span id="aiuto-reg-username" class="aiuto-campo">
                    Inizia con una lettera; solo lettere, numeri e underscore; 3-50 caratteri.
                </span>
                <span class="errore-campo" id="err-reg-username" role="alert" aria-live="polite" hidden></span>
            </label>

            <label for="reg-password" class="campo-obbligatorio">
                Password
                <input type="password" id="reg-password" name="password"
                       autocomplete="new-password" required aria-required="true"
                       aria-describedby="aiuto-reg-password" minlength="8" maxlength="16">
                <span id="aiuto-reg-password" class="aiuto-campo">
                    8-16 caratteri: almeno una maiuscola, una minuscola, un numero e un carattere speciale.
                </span>
                <span class="errore-campo" id="err-reg-password" role="alert" aria-live="polite" hidden></span>
                <label for="forza-password" class="sr-solo">Forza della password</label>
                <meter id="forza-password" min="0" max="4" low="2" high="3" optimum="4" value="0"
                       aria-label="Forza della password" title="Forza password"></meter>
                <span id="forza-password-testo" class="aiuto-campo" aria-live="polite"></span>
            </label>

            <label for="reg-conferma" class="campo-obbligatorio">
                Conferma password
                <input type="password" id="reg-conferma" name="conferma_password"
                       autocomplete="new-password" required aria-required="true" minlength="8" maxlength="16">
                <span class="errore-campo" id="err-reg-conferma" role="alert" aria-live="polite" hidden></span>
            </label>
        </fieldset>

        <p>
            <label for="progresso-form" class="sr-solo">Completamento modulo</label>
            <progress id="progresso-form" max="6" value="0" aria-label="Completamento modulo di registrazione"></progress>
            <span id="progresso-testo" class="aiuto-campo" aria-live="polite">Compila tutti i campi per procedere.</span>
        </p>

        <button type="submit" id="btn-registra" class="btn btn-primario" disabled aria-disabled="true">
            Crea profilo
        </button>
    </form>
    <?php endif; ?>
</section>

<script src="js/registrazione.js"></script>
<?php chiudiMain(); stampaFooter(); ?>
