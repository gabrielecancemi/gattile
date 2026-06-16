<?php
// Pagina di accesso


require_once 'componenti/layout.php';
require_once 'componenti/gestione_log.php';

aprireSessione();

$errore = '';
// Se già autenticato, redirect alla home.
$reindirizzato = false;
if (profiloAttivo()) {
    header('Location: home.php');
    $reindirizzato = true;
}

if (!$reindirizzato && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $password = $_POST['password'] ?? '';
    $ricordami = isset($_POST['ricordami']);

    if (empty($username) || empty($password)) {
        $errore = 'Inserisci sia username che password.';
    } else {
        $esito = verificaCredenziali($username, $password);
        switch ($esito['stato']) {
            case 'ok':
                registraProfiloInSessione($esito['utente']);
                if ($ricordami) {
                    attivaPromemoria($username);
                }
                scriviLog('info', 'login: accesso riuscito - ' . $username);
                header('Location: home.php');
                $reindirizzato = true;
                break;

            case 'password_errata':
                $errore = 'Password errata. Riprova.';
                scriviLog('avviso', 'login: password errata per username - ' . $username);
                break;

            case 'utente_assente':
                $errore = 'Nessun utente trovato con questo username. Controlla lo username o registrati.';
                scriviLog('avviso', 'login: username inesistente - ' . $username);
                break;

            case 'errore_db':
            default:
                $errore = 'Servizio momentaneamente non disponibile. Riprova tra qualche minuto.';
                scriviLog('errore', 'login: errore database durante la verifica credenziali');
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

// Intestazione della pagina
$titolo_pagina = 'Accedi';
$descrizione_pagina = 'Accedi al tuo profilo Gattile San Paolo per prenotare visite o turni di volontariato.';


// Se è avvenuto un redirect, non si produce alcun output HTML.
if (!$reindirizzato):
    ?>
    <!DOCTYPE html>
    <html lang="it">

    <?php require 'componenti/head.php'; ?>
    <?php require 'componenti/header.php'; ?>
    <main id="contenuto-principale">

        <!-- intestazione -->
        <section aria-labelledby="titolo-login">
            <h1 id="titolo-login">Accedi al tuo profilo</h1>
            <p>Non hai ancora un account? <a href="registrazione.php">Registrati gratuitamente</a>.</p>
        </section>

        <!-- form accedi -->
        <section>
            <h2 class="sr-solo">Accedi</h2>
            <?php if ($errore):
                echo avvisoUtente($errore, 'errore');
            endif; ?>

            <form id="form-login" method="post" action="login.php" novalidate aria-label="Modulo di accesso">
                <fieldset>
                    <legend>Credenziali di accesso</legend>

                    <label for="in-login-username" class="campo-obbligatorio">
                        Username</label>
                    <input type="text" id="in-login-username" name="username" autocomplete="username"
                        value="<?= $username_precompilato ?>" required maxlength="50" spellcheck="false">
                    <output class="errore-campo" id="err-username" role="alert" aria-live="polite" hidden></output>


                    <label for="in-login-password" class="campo-obbligatorio">
                        Password</label>
                    <input type="password" id="in-login-password" name="password" autocomplete="off" required maxlength="16">
                    <output class="errore-campo" id="err-password" role="alert" aria-live="polite" hidden></output>
                </fieldset>

                <?php $consenso = isset($_COOKIE['consenso']); ?>

                <fieldset class="blocco-ricordami">
                    <legend>Ricordami</legend>

                    <?php if (!$consenso): ?>
                        <p class="messaggio messaggio-avviso" role="note">
                            Per usare <strong>&ldquo;Ricordami&rdquo;</strong> devi prima
                            <strong>accettare i cookie</strong> dal banner in basso.
                        </p>
                    <?php endif; ?>

                    <label class="campo-checkbox" for="in-login-ricordami">
                        <input type="checkbox" id="in-login-ricordami" name="ricordami" value="1" aria-describedby="aiuto-ricordami"
                            <?= $consenso ? '' : 'disabled' ?>>
                        Ricordami su questo browser per 72 ore
                    </label>
                    <em id="aiuto-ricordami" class="aiuto-campo">
                        Il tuo username verrà precompilato al prossimo accesso.
                        La password non viene mai memorizzata.
                    </em>
                </fieldset>

                <p class="campo-obbligatorio nota-obbligatori">Campi obbligatori</p>
                <button type="reset" id="btn-reset-login" class="btn btn-secondario">
                    Cancella
                </button>
                <button type="submit" id="btn-login" class="btn btn-primario">Accedi</button>
            </form>
        </section>

        <script src="js/login.js" defer></script>

    </main>
    <?php require 'componenti/footer.php'; ?>
<?php endif; ?>