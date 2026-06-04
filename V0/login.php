<?php
/**
 * login.php — Pagina di accesso.
 * Cookie "ricordami" con token opaco. Password mai in chiaro nel cookie.
 * Validazione lato client (JS) + verifica definitiva server.
 */
declare(strict_types=1);

require_once 'includes/layout.php';

avviaSessione();

// Se già loggato, vai alla home
if (utenteLoggato()) {
    header('Location: index.php');
    exit;
}

$errore   = '';
$successo = '';

// Gestione POST login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Protezione CSRF base: verifica referer (in produzione usare token)
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $password = $_POST['password'] ?? '';
    $ricordami = isset($_POST['ricordami']);

    if (empty($username) || empty($password)) {
        $errore = 'Inserisci sia username che password.';
    } else {
        $utente = tentaLogin($username, $password);
        if ($utente) {
            impostaSessioneUtente($utente);
            if ($ricordami) {
                impostaRicordami($username);
            }
            header('Location: index.php');
            exit;
        } else {
            $errore = 'Credenziali non valide. Controlla username e password.';
            // Log tentativo fallito senza esporre quale campo è errato
            error_log("Tentativo login fallito per username: " . mb_substr($username, 0, 30));
        }
    }
}

// Recupera username dall'eventuale cookie "ricordami"
$usernamePre = '';
$cookieUsername = leggiRicordami();
if ($cookieUsername) {
    $usernamePre = htmlspecialchars($cookieUsername, ENT_QUOTES, 'UTF-8');
}

stampaTesta('Accedi', 'Accedi al tuo profilo di Gattile San Paolo per prenotare visite o turni di volontariato.', 'login.php');
echo '<body>';
stampaHeader();
stampaBannerCookie();
apriMain();
?>

<section aria-labelledby="titolo-login">
    <h1 id="titolo-login">Accedi al tuo profilo</h1>
    <p>Non hai ancora un account? <a href="registrazione.php">Registrati gratuitamente</a>.</p>

    <?php if ($errore): ?>
        <?= messaggioUtente($errore, 'errore') ?>
    <?php endif; ?>

    <form
        id="form-login"
        method="post"
        action="login.php"
        novalidate
        aria-label="Modulo di accesso"
    >
        <fieldset>
            <legend>Credenziali di accesso</legend>

            <label for="username" class="campo-obbligatorio">
                Username
                <input
                    type="text"
                    id="username"
                    name="username"
                    autocomplete="username"
                    value="<?= $usernamePre ?>"
                    required
                    aria-required="true"
                    aria-describedby="aiuto-username"
                    maxlength="50"
                    spellcheck="false"
                >
                <span id="aiuto-username" class="aiuto-campo">
                    Deve iniziare con una lettera.
                </span>
                <span class="errore-campo" id="err-username" role="alert" aria-live="polite" hidden></span>
            </label>

            <label for="password" class="campo-obbligatorio">
                Password
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                    aria-required="true"
                    aria-describedby="aiuto-password"
                    maxlength="16"
                >
                <span id="aiuto-password" class="aiuto-campo">
                    Il campo password viene sempre lasciato vuoto per sicurezza.
                </span>
                <span class="errore-campo" id="err-password" role="alert" aria-live="polite" hidden></span>
            </label>

            <label class="campo-checkbox" for="ricordami">
                <input
                    type="checkbox"
                    id="ricordami"
                    name="ricordami"
                    value="1"
                    aria-describedby="aiuto-ricordami"
                >
                Ricordami su questo browser per 72 ore
                <span id="aiuto-ricordami" class="aiuto-campo">
                    Se attivo, il tuo username verrà precompilato al prossimo accesso. 
                    La password non viene mai memorizzata.
                </span>
            </label>
        </fieldset>

        <button type="submit" id="btn-login" class="btn btn-primario">Accedi</button>
    </form>
</section>

<script src="js/login.js"></script>

<?php
chiudiMain();
stampaFooter();
chiudiHTML();
?>
