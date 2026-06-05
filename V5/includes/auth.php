<?php
/**
 * auth.php — Sessione, autenticazione e cookie "ricordami".
 *
 * Il cookie contiene un token opaco (64 hex char), mai le credenziali.
 * Il mapping token → username è salvato in un file JSON nella directory
 * includes/gattile_tokens/ per persistenza tra sessioni PHP diverse.
 *
 * NOTA: declare(strict_types=1) è gestito dai file chiamanti.
 */
require_once 'db.php';

/* ── Directory token ──────────────────────────────────────────── */

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

/* ── Sessione ─────────────────────────────────────────────────── */

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

/* ── Login ────────────────────────────────────────────────────── */

/**
 * Verifica le credenziali con MySQLi + prepared statement.
 * Supporta password hashate (bcrypt) e in chiaro (DB demo, ri-hasha automaticamente).
 */
function tentaLogin(string $username, string $password): ?array
{
    if (empty($username) || empty($password)) {
        return null;
    }

    $conn = null;
    try {
        $conn = getDB('reader');

        // Prepared statement: previene SQL injection
        $stm = mysqli_prepare(
            $conn,
            'SELECT id, nome, cognome, username, password, is_admin
             FROM utenti WHERE username = ? LIMIT 1'
        );
        if (!$stm) {
            error_log('[auth] Prepare fallita: ' . mysqli_error($conn));
            return null;
        }

        mysqli_stmt_bind_param($stm, 's', $username);
        mysqli_stmt_execute($stm);

        $result = mysqli_stmt_get_result($stm);
        $utente = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stm);

        if (!$utente) {
            mysqli_close($conn);
            return null;
        }

        $hash = $utente['password'];
        $ok = false;

        if (str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2b$')) {
            // Password hashata con bcrypt
            $ok = password_verify($password, $hash);
        } else {
            // Password in chiaro (DB demo): confronto diretto poi ri-hasha
            $ok = ($password === $hash);
            if ($ok) {
                try {
                    $connMod = getDB('modifier');
                    $upd = mysqli_prepare($connMod, 'UPDATE utenti SET password = ? WHERE id = ?');
                    if ($upd) {
                        $nuovoHash = password_hash($password, PASSWORD_BCRYPT);
                        $uid = (int) $utente['id'];
                        mysqli_stmt_bind_param($upd, 'si', $nuovoHash, $uid);
                        mysqli_stmt_execute($upd);
                        mysqli_stmt_close($upd);
                        error_log('[auth] Password re-hashata per utente id=' . $uid);
                    }
                    mysqli_close($connMod);
                } catch (RuntimeException $ex) {
                    error_log('[auth] Re-hash fallito: ' . $ex->getMessage());
                }
            }
        }

        mysqli_close($conn);

        if ($ok) {
            unset($utente['password']); // mai in sessione
            return $utente;
        }
    } catch (RuntimeException $e) {
        if ($conn)
            mysqli_close($conn);
        error_log('[auth] Errore DB login: ' . $e->getMessage());
    }

    return null;
}

function impostaSessioneUtente(array $utente): void
{
    avviaSessione();
    session_regenerate_id(true); // prevenzione session fixation
    $_SESSION['utente'] = $utente;
}

/* ── Cookie "ricordami" ───────────────────────────────────────── */

const COOKIE_RICORDAMI = 'remember_username';
const COOKIE_DURATA = 72 * 3600; // 72 ore

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
    #eliminaRicordami();
    $_SESSION = [];
    session_destroy();
}
