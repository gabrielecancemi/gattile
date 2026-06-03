<?php
/**
 * Gestione sessione, autenticazione e cookie "ricordami".
 * Il cookie contiene un token opaco (non le credenziali in chiaro).
 */
declare(strict_types=1);

require_once __DIR__ . '/db.php';

// Avvia la sessione in modo sicuro se non già attiva
function avviaSessione(): void
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

// Restituisce l'utente loggato o null
function utenteLoggato(): ?array
{
    avviaSessione();
    return $_SESSION['utente'] ?? null;
}

// Verifica se l'utente è admin
function isAdmin(): bool
{
    $u = utenteLoggato();
    return $u && (bool)$u['is_admin'];
}

// Reindirizza se non loggato
function richiedeLogin(string $redirect = 'login.php'): void
{
    if (!utenteLoggato()) {
        header('Location: ' . $redirect);
        exit;
    }
}

// Reindirizza se non admin
function richiedeAdmin(): void
{
    richiedeLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Tenta il login con username/password.
 * Restituisce l'array utente o null se le credenziali sono errate.
 */
function tentaLogin(string $username, string $password): ?array
{
    try {
        $db  = getDB('reader');
        $stm = $db->prepare('SELECT id, nome, cognome, username, password, is_admin FROM utenti WHERE username = ? LIMIT 1');
        $stm->execute([$username]);
        $utente = $stm->fetch();

        if ($utente && password_verify($password, $utente['password'])) {
            unset($utente['password']); // mai in sessione
            return $utente;
        }
    } catch (PDOException $e) {
        error_log('Errore DB login: ' . $e->getMessage());
    }
    return null;
}

// Imposta la sessione utente dopo login riuscito
function impostaSessioneUtente(array $utente): void
{
    avviaSessione();
    session_regenerate_id(true); // prevenzione session fixation
    $_SESSION['utente'] = $utente;
}

// Nome del cookie "ricordami"
const COOKIE_RICORDAMI = 'gattile_remember';
const COOKIE_DURATA    = 72 * 3600; // 72 ore

/**
 * Salva token opaco nel cookie e nella sessione (simulazione storage server).
 * In produzione il token andrebbe in una tabella DB dedicata.
 */
function impostaRicordami(string $username): void
{
    $token = bin2hex(random_bytes(32));
    // Salviamo il mapping token→username in sessione (semplificazione accettabile)
    // In produzione: INSERT INTO remember_tokens (token_hash, utente_id, scadenza)
    $_SESSION['remember_tokens'][$token] = [
        'username' => $username,
        'scadenza' => time() + COOKIE_DURATA,
    ];

    setcookie(COOKIE_RICORDAMI, $token, [
        'expires'  => time() + COOKIE_DURATA,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

/**
 * Legge il cookie "ricordami" e restituisce lo username salvato se valido.
 */
function leggiRicordami(): ?string
{
    avviaSessione();
    $token = $_COOKIE[COOKIE_RICORDAMI] ?? null;
    if (!$token) return null;

    $dati = $_SESSION['remember_tokens'][$token] ?? null;
    if (!$dati || $dati['scadenza'] < time()) {
        eliminaRicordami();
        return null;
    }
    return $dati['username'];
}

// Rimuove il cookie "ricordami"
function eliminaRicordami(): void
{
    avviaSessione();
    $token = $_COOKIE[COOKIE_RICORDAMI] ?? null;
    if ($token) {
        unset($_SESSION['remember_tokens'][$token]);
    }
    setcookie(COOKIE_RICORDAMI, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

// Logout completo
function logout(): void
{
    avviaSessione();
    eliminaRicordami();
    $_SESSION = [];
    session_destroy();
}
