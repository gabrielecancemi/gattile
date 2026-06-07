<?php
/**
 * auth.php — Sessione, autenticazione e cookie "ricordami".
 * Gestione errori senza eccezioni: ogni funzione MySQLi
 * viene verificata sul suo valore di ritorno.
 *
 * Il cookie "ricordami" contiene SOLO un token opaco (64 hex = 32 byte
 * casuali), mai le credenziali. L'associazione token -> username e'
 * mantenuta lato server in un file JSON dedicato (gattile_tokens/remember.json).
 *
 * NOTA: declare(strict_types=1) e' gestito dai file chiamanti.
 */
require_once 'db.php';

/* -- Directory token ------------------------------------------------ */

function tokenFilePath(): string
{
    $dir = __DIR__ . '/gattile_tokens';
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }
    return $dir . '/remember.json';
}

function leggiTokens(): array
{
    $file = tokenFilePath();
    if (!file_exists($file))
        return [];
    $raw = file_get_contents($file);
    $dati = json_decode($raw, true);
    if (!is_array($dati))
        return [];
    // Pulizia automatica: alla PRIMA lettura del file i token scaduti
    // (o privi di scadenza valida) vengono scartati e il file riscritto,
    // così non si accumulano token vecchi per sempre.
    $ora = time();
    $puliti = array_filter($dati, fn($v) => isset($v['scadenza']) && $v['scadenza'] > $ora);
    if (count($puliti) !== count($dati)) {
        scriviTokens($puliti);
    }
    return $puliti;
}

function scriviTokens(array $tokens): void
{
    file_put_contents(tokenFilePath(), json_encode($tokens), LOCK_EX);
}

/* -- Sessione ------------------------------------------------------- */

function avviaSessione(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

function utenteLoggato(): ?array
{
    avviaSessione();
    return $_SESSION['utente'] ?? null;
}

function isAdmin(): bool
{
    $u = utenteLoggato();
    return $u !== null && (bool) $u['is_admin'];
}

function richiedeLogin(string $redirect = 'login.php'): void
{
    if (!utenteLoggato()) {
        header('Location: ' . $redirect);
        exit;
    }
}

function richiedeAdmin(): void
{
    richiedeLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

/* -- Login ---------------------------------------------------------- */

/* Esiti possibili del tentativo di login. */
const LOGIN_OK              = 'ok';
const LOGIN_UTENTE_ASSENTE  = 'utente_assente';
const LOGIN_PASSWORD_ERRATA = 'password_errata';
const LOGIN_ERRORE_DB       = 'errore_db';

/* ── Memorizzazione password IN CHIARO ────────────────────────────
 *
 * ATTENZIONE — SCELTA NON SICURA (richiesta esplicita di modifica):
 * la password viene salvata sul database COSÌ COME È (in chiaro), senza
 * alcun hashing né salt. Questa NON è l'opzione più sicura: chiunque
 * abbia accesso in lettura alla tabella "utenti" (un DBA, un attaccante
 * che riesca a leggere il DB, un backup non protetto) vede direttamente
 * tutte le password degli utenti. In un contesto reale si dovrebbe usare
 * un hash robusto e specifico per password (es. password_hash() con
 * bcrypt/Argon2) e mai conservare la password in chiaro. La spiegazione
 * di questa scelta è riportata anche in progettoSito.txt.
 *
 * I vincoli di robustezza della password (8-16 caratteri con maiuscola,
 * minuscola, numero e carattere speciale) restano comunque applicati in
 * fase di registrazione: riguardano COME la password è gestita/validata,
 * non come è memorizzata.
 */

/** Restituisce la password da memorizzare: in chiaro, senza modifiche. */
function creaHashPassword(string $password): string
{
    // Nessun hashing: la password viene salvata in chiaro (scelta non sicura).
    return $password;
}

/**
 * Verifica una password in chiaro contro quella memorizzata (anch'essa in
 * chiaro). Si usa comunque hash_equals per un confronto a tempo costante
 * (evita timing attack), pur restando la memorizzazione in chiaro.
 */
function verificaHashPassword(string $password, string $memorizzato): bool
{
    return hash_equals($memorizzato, $password);
}

/**
 * Verifica le credenziali con MySQLi + prepared statement.
 *
 * Restituisce un array con:
 *   - 'stato'  : una delle costanti LOGIN_*
 *   - 'utente' : i dati utente (solo se stato === LOGIN_OK), altrimenti null
 *
 * La distinzione fra "utente inesistente" e "password errata" consente di
 * mostrare un messaggio specifico ("password sbagliata"), come richiesto.
 * Nessuna eccezione: ogni errore viene verificato sul valore di ritorno.
 */
function tentaLogin(string $username, string $password): array
{
    if ($username === '' || $password === '') {
        return ['stato' => LOGIN_UTENTE_ASSENTE, 'utente' => null];
    }

    $conn = getDB('reader');
    if (!$conn) {
        error_log('[auth] Connessione DB non disponibile');
        return ['stato' => LOGIN_ERRORE_DB, 'utente' => null];
    }

    $stm = mysqli_prepare(
        $conn,
        'SELECT id, nome, cognome, username, password, is_admin
         FROM utenti WHERE username = ? LIMIT 1'
    );

    if (!$stm) {
        error_log('[auth] Prepare fallita: ' . mysqli_error($conn));
        mysqli_close($conn);
        return ['stato' => LOGIN_ERRORE_DB, 'utente' => null];
    }

    mysqli_stmt_bind_param($stm, 's', $username);

    if (!mysqli_stmt_execute($stm)) {
        error_log('[auth] Execute fallita: ' . mysqli_stmt_error($stm));
        mysqli_stmt_close($stm);
        mysqli_close($conn);
        return ['stato' => LOGIN_ERRORE_DB, 'utente' => null];
    }

    $result = mysqli_stmt_get_result($stm);
    $utente = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stm);

    if (!$utente) {
        mysqli_close($conn);
        return ['stato' => LOGIN_UTENTE_ASSENTE, 'utente' => null];
    }

    // La password è memorizzata in chiaro (scelta non sicura, vedi
    // creaHashPassword/progettoSito.txt): confronto diretto a tempo costante.
    $memorizzata = $utente['password'];
    $ok = verificaHashPassword($password, $memorizzata);
    mysqli_close($conn);

    if ($ok) {
        unset($utente['password']); // mai in sessione
        return ['stato' => LOGIN_OK, 'utente' => $utente];
    }

    return ['stato' => LOGIN_PASSWORD_ERRATA, 'utente' => null];
}

function impostaSessioneUtente(array $utente): void
{
    avviaSessione();
    session_regenerate_id(true); // prevenzione session fixation
    $_SESSION['utente'] = $utente;
}

/* -- Cookie "ricordami" --------------------------------------------- */

const COOKIE_RICORDAMI = 'remember_username';
const COOKIE_DURATA = 72 * 3600;

function impostaRicordami(string $username): void
{
    $token = bin2hex(random_bytes(32));
    $tokens = leggiTokens();
    $tokens[$token] = [
        'username' => $username,
        'scadenza' => time() + COOKIE_DURATA,
    ];
    scriviTokens($tokens);
    setcookie(COOKIE_RICORDAMI, $token, [
        'expires' => time() + COOKIE_DURATA,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

function leggiRicordami(): ?string
{
    $token = $_COOKIE[COOKIE_RICORDAMI] ?? null;
    if (!$token)
        return null;
    $tokens = leggiTokens();
    $dati = $tokens[$token] ?? null;
    if (!$dati) {
        eliminaRicordami();
        return null;
    }
    return $dati['username'];
}

function eliminaRicordami(): void
{
    $token = $_COOKIE[COOKIE_RICORDAMI] ?? null;
    if ($token !== null) {
        $tokens = leggiTokens();
        if (isset($tokens[$token])) {
            unset($tokens[$token]);
            scriviTokens($tokens);
        }
    }
    setcookie(COOKIE_RICORDAMI, '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    unset($_COOKIE[COOKIE_RICORDAMI]);
}

function logout(): void
{
    avviaSessione();
    $_SESSION = [];
    session_destroy();
}
