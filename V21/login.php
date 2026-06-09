<?php
// Pagina di accesso. Il cookie "ricordami" salva un gettone opaco, mai le
// credenziali. Validazione lato client (js/login.js) + controllo definitivo
// lato server.
declare(strict_types=1);

require_once 'includes/layout.php';

aprireSessione();
if (profiloAttivo()) {
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
        $esito = verificaCredenziali($username, $password);
        switch ($esito['stato']) {
            case ESITO_OK:
                registraProfiloInSessione($esito['utente']);
                if ($ricordami) {
                    attivaPromemoria($username);
                }
                header('Location: index.php');
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

// Intestazione della pagina (titolo + descrizione per SEO).
$titolo_pagina = 'Accedi';
$descrizione_pagina = 'Accedi al tuo profilo Gattile San Paolo per prenotare visite o turni di volontariato.';

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

// Tema da cookie: solo 'chiaro'/'scuro' sono validi, altrimenti tema di sistema.
$tema_cookie = $_COOKIE['tema'] ?? '';
$attributo_tema = in_array($tema_cookie, ['chiaro', 'scuro'], true)
    ? ' data-tema="' . $tema_cookie . '"'
    : '';
?>
<!DOCTYPE html>
<html lang="it" <?= $attributo_tema ?>>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?= ripulisci($titolo_pagina) ?></title>
    <meta name="description" content="<?= ripulisci($descrizione_pagina) ?>">
    <meta name="keywords" content="gattile, adozione gatti, volontariato, felini, Torino">
    <meta name="author" content="Gabriele Cancemi">
    <meta name="robots" content="index, follow">
    <meta name="color-scheme" content="light dark">
    <link rel="icon" href="img/logo.png" type="image/png">
    <link rel="stylesheet" href="css/stile.css?v=10">
    <link rel="stylesheet" href="css/stampa.css?v=10" media="print">
    <script src="js/tema-iniziale.js"></script>
</head>
<?php require 'includes/header.php'; ?>
<main id="contenuto-principale" tabindex="-1">


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
                <input type="text" id="username" name="username" autocomplete="username"
                    value="<?= $username_precompilato ?>" required aria-describedby="aiuto-username" maxlength="50"
                    spellcheck="false">
                <output class="errore-campo" id="err-username" role="alert" aria-live="polite" hidden></output>


                <label for="password" class="campo-obbligatorio">
                    Password</label>
                <input type="password" id="password" name="password" autocomplete="current-password" required
                    maxlength="16">
                <output class="errore-campo" id="err-password" role="alert" aria-live="polite" hidden></output>
            </fieldset>

            <?php $consenso_cookie = isset($_COOKIE['consenso_cookie']); ?>

            <fieldset class="blocco-ricordami">
                <legend>Ricordami</legend>

                <?php if (!$consenso_cookie): ?>
                    <p class="messaggio messaggio-avviso" role="note">
                        Per usare <strong>&ldquo;Ricordami&rdquo;</strong> devi prima
                        <strong>accettare i cookie</strong> dal banner in basso.
                    </p>
                <?php endif; ?>

                <label class="campo-checkbox" for="ricordami">
                    <input type="checkbox" id="ricordami" name="ricordami" value="1" aria-describedby="aiuto-ricordami"
                        <?= $consenso_cookie ? '' : 'disabled' ?>>
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

</main>
<?php require 'includes/footer.php'; ?>