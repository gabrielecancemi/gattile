<?php
// Sessione, accesso e cookie "ricordami"

require_once 'connessione_db.php';
require_once 'gestione_log.php';

function connessioneSicura(): bool
{
    // HTTPS
    $https = (string) ($_SERVER['HTTPS'] ?? '');
    if (!empty($https) && $https !== 'off' && $https !== 'OFF' && $https !== 'Off') {
        return true;
    }
    if (($_SERVER['SERVER_PORT'] ?? '') === '443') {
        return true;
    }
    $proto = (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
    if ($proto === 'https' || $proto === 'HTTPS') {
        return true;
    }
    return false;
}

// Archivio token

function percorsoToken(): string
{
    $cartella = __DIR__ . '/tokens';
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
    
    // Rilegge i token dal file JSON come array associativo.
    $dati = json_decode($grezzo, true);
    // File vuoto o corrotto, riparte da un elenco vuoto.
    if ($dati === null) {
        return [];
    }

    // Tiene solo i token non ancora scaduti.
    $adesso = time();
    $validi = [];
    $cambiato = false;
    foreach ($dati as $chiave => $valore) {
        if (isset($valore['scadenza']) && $valore['scadenza'] > $adesso) {
            $validi[$chiave] = $valore;
        } else {
            $cambiato = true;
        }
    }
    if ($cambiato) {
        salvaToken($validi);
    }
    return $validi;
}

function salvaToken(array $gettoni): void
{
    // Salva l'elenco dei token su file in formato JSON.
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

// Se non autenticato imposta il redirect e torna false, altrimenti true.
function richiedeAccesso(string $destinazione = 'login.php'): bool
{
    if (!profiloAttivo()) {
        header('Location: ' . $destinazione);
        return false;
    }
    return true;
}

// Torna true se l'utente è admin, altrimenti imposta il redirect e torna false.
function esigeAdmin(): bool
{
    if (!richiedeAccesso()) {
        return false;
    }
    if (!haRuoloAdmin()) {
        header('Location: home.php');
        return false;
    }
    return true;
}

// Memorizzazione password IN CHIARO - da modificare per più sicurezza (salt)
function preparaPasswordSalvataggio(string $password): string
{
    return $password;
}

// Password in chiaro nel DB - NON OPZIONE CONSIGLIATA
function confrontaPassword(string $password, string $salvata): bool
{
    return $salvata === $password;
}

// Controlla le credenziali con prepared statement
function verificaCredenziali(string $username, string $password): array
{
    if ($username === '' || $password === '') {
        return ['stato' => 'utente_assente', 'utente' => null];
    }

    $conn = connessioneDb('reader');
    if (!$conn) {
        scriviLog('errore', 'verificaCredenziali: connessione al database non riuscita');
        return ['stato' => 'errore_db', 'utente' => null];
    }

    $stm = mysqli_prepare(
        $conn,
        'SELECT id, nome, cognome, username, password, is_admin
         FROM utenti WHERE username = ? LIMIT 1'
    );

    if (!$stm) {
        scriviLog('errore', 'verificaCredenziali: prepare fallita - ' . mysqli_error($conn));
        mysqli_close($conn);
        return ['stato' => 'errore_db', 'utente' => null];
    }

    mysqli_stmt_bind_param($stm, 's', $username);

    if (!mysqli_stmt_execute($stm)) {
        scriviLog('errore', 'verificaCredenziali: execute fallita - ' . mysqli_stmt_error($stm));
        mysqli_stmt_close($stm);
        mysqli_close($conn);
        return ['stato' => 'errore_db', 'utente' => null];
    }

    // Associa le colonne a variabili.
    mysqli_stmt_bind_result($stm, $id, $nome, $cognome, $uname, $password_salvata, $is_admin);
    $trovato = mysqli_stmt_fetch($stm);
    mysqli_stmt_close($stm);

    if (!$trovato) {
        mysqli_close($conn);
        return ['stato' => 'utente_assente', 'utente' => null];
    }

    $combacia = confrontaPassword($password, (string) $password_salvata);
    mysqli_close($conn);

    if ($combacia) {
        // La password non viene mai inserita in sessione.
        $utente = [
            'id' => $id,
            'nome' => $nome,
            'cognome' => $cognome,
            'username' => $uname,
            'is_admin' => $is_admin,
        ];
        return ['stato' => 'ok', 'utente' => $utente];
    }

    return ['stato' => 'password_errata', 'utente' => null];
}

function registraProfiloInSessione(array $utente): void
{
    aprireSessione();
    // contro la session fixation
    session_regenerate_id(true);
    $_SESSION['utente'] = $utente;
}

// Cookie "ricordami"


function attivaPromemoria(string $username): void
{
    // Token opaco (SHA-256 su sessione, ora e valore casuale)
    $gettone = hash('sha256', session_id() . time() . rand() . $username);
    $gettoni = scriviToken();
    $gettoni[$gettone] = [
        'username' => $username,
        'scadenza' => time() + (72 * 3600),
    ];
    salvaToken($gettoni);
    setcookie('ricorda_username', $gettone, [
        'expires' => time() + (72 * 3600),
        'path' => '/',
        'secure' => connessioneSicura(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

function recuperaPromemoria(): ?string
{
    $gettone = $_COOKIE['ricorda_username'] ?? null;
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
    $gettone = $_COOKIE['ricorda_username'] ?? null;
    if ($gettone !== null) {
        $gettoni = scriviToken();
        if (isset($gettoni[$gettone])) {
            unset($gettoni[$gettone]);
            salvaToken($gettoni);
        }
    }
    setcookie('ricorda_username', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => connessioneSicura(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    unset($_COOKIE['ricorda_username']);
}

function chiudiProfilo(): void
{
    aprireSessione();
    $_SESSION = [];
    session_destroy();
}