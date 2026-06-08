<?php
// Pagina di accesso. Il cookie "ricordami" salva un gettone opaco, mai le
// credenziali. Validazione lato client (js/login.js) + controllo definitivo
// lato server.
declare(strict_types=1);

require_once 'includes/layout.php';

aprireSessione();
if (profiloAttivo()) {
    header('Location: home.php');
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
        $esito = verificaCredenziali($username, $password);
        switch ($esito['stato']) {
            case ESITO_OK:
                registraProfiloInSessione($esito['utente']);
                if ($ricordami) {
                    attivaPromemoria($username);
                }
                header('Location: home.php');
                exit;

            case ESITO_PASSWORD_ERRATA:
                $errore = 'Password errata. Riprova.';
                error_log('[login] password errata per username: ' . mb_substr($username, 0, 30));
                break;

            case ESITO_UTENTE_ASSENTE:
                $errore = 'Nessun utente trovato con questo username. Controlla lo username o registrati.';
                error_log('[login] username inesistente: ' . mb_substr($username, 0, 30));
                break;

            case ESITO_ERRORE_DB:
            default:
                $errore = 'Servizio momentaneamente non disponibile. Riprova tra qualche minuto.';
                break;
        }
    }
}

// Precompila lo username se è presente il cookie "ricordami".
$username_precompilato = '';
$username_cookie = recuperaPromemoria();
if ($username_cookie) {
    $username_precompilato = ripulisci($username_cookie);
}

generaIntestazioneHtml(
    'Accedi',
    'Accedi al tuo profilo Gattile San Paolo per prenotare visite o turni di volontariato.'
);
generaTestata();
aprireContenuto();
?>

<section aria-labelledby="titolo-login">
    <h1 id="titolo-login">Accedi al tuo profilo</h1>
    <p>Non hai ancora un account? <a href="registrazione.php">Registrati gratuitamente</a>.</p>
</section>
<section>
    <?php if ($errore):
        echo avvisoUtente($errore, 'errore');
    endif; ?>

    <form id="form-login" method="post" action="login.php" novalidate aria-label="Modulo di accesso">
    <fieldset>
            <legend>Credenziali di accesso</legend>

            <label for="username" class="campo-obbligatorio">
                Username</label>
            <input type="text" id="username" name="username" autocomplete="username" value="<?= $username_precompilato ?>"
                required aria-describedby="aiuto-username" maxlength="50" spellcheck="false">
            <em id="aiuto-username" class="aiuto-campo">Inizia con una lettera.</em>
            <output class="errore-campo" id="err-username" role="alert" aria-live="polite" hidden></output>


            <label for="password" class="campo-obbligatorio">
                Password</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required
                maxlength="16">
            <output class="errore-campo" id="err-password" role="alert" aria-live="polite" hidden></output>
        </fieldset>

        <?php $consenso_cookie = isset($_COOKIE['cookie_consenso']); ?>

        <fieldset class="blocco-ricordami">
            <legend>Ricordami</legend>

            <?php if (!$consenso_cookie): ?>
                <p class="messaggio messaggio-avviso" role="note">
                    Per usare <strong>&ldquo;Ricordami&rdquo;</strong> devi prima
                    <strong>accettare i cookie</strong> dal banner in basso o dalla
                    <a href="privacy.php">pagina privacy</a>.
                </p>
            <?php endif; ?>

            <label class="campo-checkbox" for="ricordami">
                <input type="checkbox" id="ricordami" name="ricordami" value="1"
                    aria-describedby="aiuto-ricordami" <?= $consenso_cookie ? '' : 'disabled' ?>>
                Ricordami su questo browser per 72 ore
            </label>
            <em id="aiuto-ricordami" class="aiuto-campo">
                Il tuo username verrà precompilato al prossimo accesso.
                La password non viene mai memorizzata.
            </em>
        </fieldset>

        <p class="campo-obbligatorio nota-obbligatori">Campi obbligatori</p>
        <button type="submit" id="btn-login" class="btn btn-primario">Accedi</button>
    </form>
</section>

<script src="js/login.js" defer></script>

<?php chiudereContenuto();
generaPiePagina(); ?>
