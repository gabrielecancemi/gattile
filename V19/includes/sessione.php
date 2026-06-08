<?php
// Sessione, accesso e cookie "ricordami".
// Il cookie ricordami contiene solo un gettone casuale (32 byte -> 64 hex),
// mai le credenziali. L'abbinamento gettone -> username sta lato server in
// un file JSON dedicato.

require_once 'db.php';

/* Archivio gettoni ------------------------------------------------- */

function percorsoArchivioGettoni(): string
{
    $cartella = __DIR__ . '/gattile_tokens';
    if (!is_dir($cartella)) {
        mkdir($cartella, 0700, true);
    }
    return $cartella . '/remember.json';
}

function caricaGettoni(): array
{
    $file = percorsoArchivioGettoni();
    if (!file_exists($file)) {
        return [];
    }
    $grezzo = file_get_contents($file);
    $dati = json_decode($grezzo, true);
    if (!is_array($dati)) {
        return [];
    }
    // Alla prima lettura scarto i gettoni scaduti e riscrivo il file:
    // così non si accumulano per sempre.
    $adesso = time();
    $validi = array_filter($dati, fn($v) => isset($v['scadenza']) && $v['scadenza'] > $adesso);
    if (count($validi) !== count($dati)) {
        salvaGettoni($validi);
    }
    return $validi;
}

function salvaGettoni(array $gettoni): void
{
    file_put_contents(percorsoArchivioGettoni(), json_encode($gettoni), LOCK_EX);
}

/* Sessione --------------------------------------------------------- */

function aprireSessione(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

function profiloAttivo(): ?array
{
    aprireSessione();
    return $_SESSION['utente'] ?? null;
}

function haRuoloAdmin(): bool
{
    $profilo = profiloAttivo();
    return $profilo !== null && (bool) $profilo['is_admin'];
}

function esigeAccesso(string $destinazione = 'login.php'): void
{
    if (!profiloAttivo()) {
        header('Location: ' . $destinazione);
        exit;
    }
}

function esigeAdmin(): void
{
    esigeAccesso();
    if (!haRuoloAdmin()) {
        header('Location: index.php');
        exit;
    }
}

/* Accesso ---------------------------------------------------------- */

const ESITO_OK              = 'ok';
const ESITO_UTENTE_ASSENTE  = 'utente_assente';
const ESITO_PASSWORD_ERRATA = 'password_errata';
const ESITO_ERRORE_DB       = 'errore_db';

/* Memorizzazione password IN CHIARO.
 *
 * Scelta voluta: la password finisce sul database così com'è, senza hash né
 * salt. Non è l'opzione sicura — chiunque legga la tabella "utenti" vede le
 * password. In produzione andrebbe usato un digest dedicato (es. password_hash
 * con bcrypt/Argon2). I vincoli di robustezza in fase di registrazione restano
 * comunque attivi: riguardano la validazione, non come la password è salvata.
 * Vedi note-progetto.txt. */

// Ritorna la password pronta per il salvataggio: in chiaro, immutata.
function preparaPasswordSalvataggio(string $password): string
{
    return $password;
}

// Confronto a tempo costante (evita timing attack), pur restando in chiaro.
function confrontaPassword(string $password, string $salvata): bool
{
    return hash_equals($salvata, $password);
}

// Controlla le credenziali con prepared statement.
// Ritorna ['stato' => ESITO_*, 'utente' => array|null].
// Distinguo "utente assente" da "password errata" per dare un messaggio mirato.
function verificaCredenziali(string $username, string $password): array
{
    if ($username === '' || $password === '') {
        return ['stato' => ESITO_UTENTE_ASSENTE, 'utente' => null];
    }

    $conn = connessioneDb('reader');
    if (!$conn) {
        error_log('[accesso] DB non disponibile');
        return ['stato' => ESITO_ERRORE_DB, 'utente' => null];
    }

    $stm = mysqli_prepare(
        $conn,
        'SELECT id, nome, cognome, username, password, is_admin
         FROM utenti WHERE username = ? LIMIT 1'
    );

    if (!$stm) {
        error_log('[accesso] prepare fallita: ' . mysqli_error($conn));
        mysqli_close($conn);
        return ['stato' => ESITO_ERRORE_DB, 'utente' => null];
    }

    mysqli_stmt_bind_param($stm, 's', $username);

    if (!mysqli_stmt_execute($stm)) {
        error_log('[accesso] execute fallita: ' . mysqli_stmt_error($stm));
        mysqli_stmt_close($stm);
        mysqli_close($conn);
        return ['stato' => ESITO_ERRORE_DB, 'utente' => null];
    }

    $risultato = mysqli_stmt_get_result($stm);
    $utente = mysqli_fetch_assoc($risultato);
    mysqli_stmt_close($stm);

    if (!$utente) {
        mysqli_close($conn);
        return ['stato' => ESITO_UTENTE_ASSENTE, 'utente' => null];
    }

    $password_salvata = $utente['password'];
    $combacia = confrontaPassword($password, $password_salvata);
    mysqli_close($conn);

    if ($combacia) {
        unset($utente['password']); // non finisce mai in sessione
        return ['stato' => ESITO_OK, 'utente' => $utente];
    }

    return ['stato' => ESITO_PASSWORD_ERRATA, 'utente' => null];
}

function registraProfiloInSessione(array $utente): void
{
    aprireSessione();
    session_regenerate_id(true); // contro la session fixation
    $_SESSION['utente'] = $utente;
}

/* Cookie "ricordami" ----------------------------------------------- */

const NOME_COOKIE_PROMEMORIA = 'ricorda_username';
const DURATA_PROMEMORIA = 72 * 3600;

function attivaPromemoria(string $username): void
{
    $gettone = bin2hex(random_bytes(32));
    $gettoni = caricaGettoni();
    $gettoni[$gettone] = [
        'username' => $username,
        'scadenza' => time() + DURATA_PROMEMORIA,
    ];
    salvaGettoni($gettoni);
    setcookie(NOME_COOKIE_PROMEMORIA, $gettone, [
        'expires'  => time() + DURATA_PROMEMORIA,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

function recuperaPromemoria(): ?string
{
    $gettone = $_COOKIE[NOME_COOKIE_PROMEMORIA] ?? null;
    if (!$gettone) {
        return null;
    }
    $gettoni = caricaGettoni();
    $dati = $gettoni[$gettone] ?? null;
    if (!$dati) {
        cancellaPromemoria();
        return null;
    }
    return $dati['username'];
}

function cancellaPromemoria(): void
{
    $gettone = $_COOKIE[NOME_COOKIE_PROMEMORIA] ?? null;
    if ($gettone !== null) {
        $gettoni = caricaGettoni();
        if (isset($gettoni[$gettone])) {
            unset($gettoni[$gettone]);
            salvaGettoni($gettoni);
        }
    }
    setcookie(NOME_COOKIE_PROMEMORIA, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    unset($_COOKIE[NOME_COOKIE_PROMEMORIA]);
}

function chiudiProfilo(): void
{
    aprireSessione();
    $_SESSION = [];
    session_destroy();
}
