<?php
// Sessione, accesso e cookie "ricordami"

require_once 'connessione_db.php';

function connessioneSicura(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    if (($_SERVER['SERVER_PORT'] ?? '') === '443') {
        return true;
    }
    if (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https') {
        return true;
    }
    return false;
}

// Archivio token

function percorsoToken(): string
{
    $cartella = __DIR__ . '/gattile_tokens';
    // crea cartella se non esiste
    if (!is_dir($cartella)) {
        mkdir($cartella, 0700, true);
    }
    return $cartella . '/remember.json';
}

function scriviToken(): array
{
    $file = percorsoToken();
    if (!file_exists($file)) {
        return [];
    }
    $grezzo = file_get_contents($file);
    $dati = json_decode($grezzo, true);
    if (!is_array($dati)) {
        return [];
    }
    // Alla prima lettura scarto i token scaduti
    $adesso = time();
    $validi = array_filter($dati, fn($v) => isset($v['scadenza']) && $v['scadenza'] > $adesso);
    if (count($validi) !== count($dati)) {
        salvaToken($validi);
    }
    return $validi;
}

function salvaToken(array $gettoni): void
{
    file_put_contents(percorsoToken(), json_encode($gettoni), LOCK_EX);
}

// Sessione

function aprireSessione(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => connessioneSicura(),
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

function richiedeAccesso(string $destinazione = 'login.php'): void
{
    if (!profiloAttivo()) {
        header('Location: ' . $destinazione);
        exit;
    }
}

function esigeAdmin(): void
{
    richiedeAccesso();
    if (!haRuoloAdmin()) {
        header('Location: index.php');
        exit;
    }
}

// Accesso

const ESITO_OK = 'ok';
const ESITO_UTENTE_ASSENTE = 'utente_assente';
const ESITO_PASSWORD_ERRATA = 'password_errata';
const ESITO_ERRORE_DB = 'errore_db';

// Memorizzazione password IN CHIARO
function preparaPasswordSalvataggio(string $password): string
{
    return $password;
}

// Confronto a tempo costante (evita timing attack), pur restando in chiaro.
function confrontaPassword(string $password, string $salvata): bool
{
    return hash_equals($salvata, $password);
}

// Controlla le credenziali con prepared statement
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
        // non finisce mai in sessione
        unset($utente['password']);
        return ['stato' => ESITO_OK, 'utente' => $utente];
    }

    return ['stato' => ESITO_PASSWORD_ERRATA, 'utente' => null];
}

function registraProfiloInSessione(array $utente): void
{
    aprireSessione();
    // contro la session fixation
    session_regenerate_id(true);
    $_SESSION['utente'] = $utente;
}

// Cookie "ricordami"

const NOME_COOKIE_PROMEMORIA = 'ricorda_username';
const DURATA_PROMEMORIA = 72 * 3600;

function attivaPromemoria(string $username): void
{
    $gettone = bin2hex(random_bytes(32));
    $gettoni = scriviToken();
    $gettoni[$gettone] = [
        'username' => $username,
        'scadenza' => time() + DURATA_PROMEMORIA,
    ];
    salvaToken($gettoni);
    setcookie(NOME_COOKIE_PROMEMORIA, $gettone, [
        'expires' => time() + DURATA_PROMEMORIA,
        'path' => '/',
        'secure' => connessioneSicura(),
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
    $gettoni = scriviToken();
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
        $gettoni = scriviToken();
        if (isset($gettoni[$gettone])) {
            unset($gettoni[$gettone]);
            salvaToken($gettoni);
        }
    }
    setcookie(NOME_COOKIE_PROMEMORIA, '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => connessioneSicura(),
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
