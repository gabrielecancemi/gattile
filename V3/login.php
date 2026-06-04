<?php
/**
 * login.php — Pagina di accesso.
 * Cookie "ricordami": token opaco in chiaro nel cookie, mai le credenziali.
 * Validazione lato client (js/login.js) + controllo definitivo lato server.
 */
declare(strict_types=1);

require_once 'includes/layout.php';

avviaSessione();
if (utenteLoggato()) { header('Location: index.php'); exit; }

$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim(filter_input(INPUT_POST, 'username',  FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $password  = $_POST['password'] ?? '';
    $ricordami = isset($_POST['ricordami']);

    if (empty($username) || empty($password)) {
        $errore = 'Inserisci sia username che password.';
    } else {
        $utente = tentaLogin($username, $password);
        if ($utente) {
            impostaSessioneUtente($utente);
            if ($ricordami) impostaRicordami($username);
            header('Location: index.php');
            exit;
        } else {
            $errore = 'Credenziali non valide. Controlla username e password.';
            error_log('[login] Tentativo fallito per username: ' . mb_substr($username, 0, 30));
        }
    }
}

// Precompila username se presente il cookie "ricordami"
$usernamePre    = '';
$cookieUsername = leggiRicordami();
if ($cookieUsername) {
    $usernamePre = esc($cookieUsername);
}

stampaTesta(
    'Accedi',
    'Accedi al tuo profilo Gattile San Paolo per prenotare visite o turni di volontariato.',
    'login.php'
);
stampaHeader();
apriMain();
?>

<section aria-labelledby="titolo-login">
    <h1 id="titolo-login">Accedi al tuo profilo</h1>
    <p>Non hai ancora un account? <a href="registrazione.php">Registrati gratuitamente</a>.</p>

    <?php if ($errore): echo messaggioUtente($errore, 'errore'); endif; ?>

    <form id="form-login" method="post" action="login.php"
          novalidate aria-label="Modulo di accesso">
        <fieldset>
            <legend>Credenziali di accesso</legend>

            <label for="username" class="campo-obbligatorio">
                Username
                <input type="text" id="username" name="username"
                       autocomplete="username"
                       value="<?= $usernamePre ?>"
                       required aria-required="true"
                       aria-describedby="aiuto-username"
                       maxlength="50" spellcheck="false">
                <em id="aiuto-username" class="aiuto-campo">Deve iniziare con una lettera.</em>
                <output class="errore-campo" id="err-username" role="alert" aria-live="polite" hidden></output>
            </label>

            <label for="password" class="campo-obbligatorio">
                Password
                <input type="password" id="password" name="password"
                       autocomplete="current-password"
                       required aria-required="true"
                       maxlength="16">
                <output class="errore-campo" id="err-password" role="alert" aria-live="polite" hidden></output>
            </label>

            <label class="campo-checkbox" for="ricordami">
                <input type="checkbox" id="ricordami" name="ricordami" value="1"
                       aria-describedby="aiuto-ricordami">
                Ricordami su questo browser per 72 ore
                <em id="aiuto-ricordami" class="aiuto-campo">
                    Il tuo username verrà precompilato al prossimo accesso.
                    La password non viene mai memorizzata.
                </em>
            </label>
        </fieldset>

        <button type="submit" id="btn-login" class="btn btn-primario">Accedi</button>
    </form>
</section>

<script src="js/login.js" defer></script>

<?php chiudiMain(); stampaFooter(); ?>
