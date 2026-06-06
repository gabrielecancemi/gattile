<?php
/**
 * login.php — Pagina di accesso.
 * Cookie "ricordami": token opaco in chiaro nel cookie, mai le credenziali.
 * Validazione lato client (js/login.js) + controllo definitivo lato server.
 */
declare(strict_types=1);

require_once 'includes/layout.php';

avviaSessione();
if (utenteLoggato()) {
    header('Location: index.php');
    exit;
}

$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $password = $_POST['password'] ?? '';
    $ricordami = isset($_POST['ricordami']);

    if (empty($username) || empty($password)) {
        $errore = 'Inserisci sia username che password.';
    } else {
        $esito = tentaLogin($username, $password);
        switch ($esito['stato']) {
            case LOGIN_OK:
                impostaSessioneUtente($esito['utente']);
                if ($ricordami) {
                    impostaRicordami($username);
                }
                header('Location: index.php');
                exit;

            case LOGIN_PASSWORD_ERRATA:
                // Messaggio specifico: lo username esiste, la password è sbagliata.
                $errore = 'Password errata. Riprova.';
                error_log('[login] Password errata per username: ' . mb_substr($username, 0, 30));
                break;

            case LOGIN_UTENTE_ASSENTE:
                $errore = 'Nessun utente trovato con questo username. Controlla lo username o registrati.';
                error_log('[login] Username inesistente: ' . mb_substr($username, 0, 30));
                break;

            case LOGIN_ERRORE_DB:
            default:
                $errore = 'Servizio momentaneamente non disponibile. Riprova tra qualche minuto.';
                break;
        }
    }
}

// Precompila username se presente il cookie "ricordami"
$usernamePre = '';
$cookieUsername = leggiRicordami();
if ($cookieUsername) {
    $usernamePre = esc($cookieUsername);
}

stampaTesta(
    'Accedi',
    'Accedi al tuo profilo Gattile San Paolo per prenotare visite o turni di volontariato.'
);
stampaHeader();
apriMain();
?>

<section aria-labelledby="titolo-login">
    <h1 id="titolo-login">Accedi al tuo profilo</h1>
    <p>Non hai ancora un account? <a href="registrazione.php">Registrati gratuitamente</a>.</p>
</section>
<section>
    <?php if ($errore):
        echo messaggioUtente($errore, 'errore');
    endif; ?>

    <form id="form-login" method="post" action="login.php" novalidate aria-label="Modulo di accesso">
    <fieldset>
            <legend>Credenziali di accesso</legend>

            <label for="username" class="campo-obbligatorio">
                Username</label>
            <input type="text" id="username" name="username" autocomplete="username" value="<?= $usernamePre ?>"
                required aria-describedby="aiuto-username" maxlength="50" spellcheck="false">
            <em id="aiuto-username" class="aiuto-campo">Inizia con una lettera.</em>
            <output class="errore-campo" id="err-username" role="alert" aria-live="polite" hidden></output>


            <label for="password" class="campo-obbligatorio">
                Password</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required
                maxlength="16">
            <output class="errore-campo" id="err-password" role="alert" aria-live="polite" hidden></output>


            <label class="campo-checkbox" for="ricordami">
                <?php $consensoCookie = isset($_COOKIE['cookie_consenso']); ?>

                <input type="checkbox" id="ricordami" name="ricordami" value="1" aria-describedby="aiuto-ricordami"
                    <?= $consensoCookie ? '' : 'disabled' ?>> Ricordami su questo browser per 72 ore
                <?php if (!$consensoCookie): ?>
                    <p class="aiuto-campo">
                        Per usare "Ricordami" devi prima accettare i cookie.
                    </p>
                <?php endif; ?>
                <em id="aiuto-ricordami" class="aiuto-campo">
                    Il tuo username verrà precompilato al prossimo accesso.
                    La password non viene mai memorizzata.
                </em>
            </label>
        </fieldset>
        <label class="campo-obbligatorio">Campi obbligatori</label>    
        <button type="submit" id="btn-login" class="btn btn-primario">Accedi</button>
    </form>
</section>

<script src="js/login.js" defer></script>

<?php chiudiMain();
stampaFooter(); ?>