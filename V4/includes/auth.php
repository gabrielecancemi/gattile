<?php
/**
 * auth.php — Sessione, autenticazione e cookie "ricordami".
 *
 * Il cookie contiene un token opaco (64 hex char), mai le credenziali.
 * Il mapping token → username è salvato in un file JSON nella directory
 * /tmp/gattile_tokens/ (fuori dalla webroot, non accessibile via HTTP).
 * Questo garantisce persistenza tra sessioni PHP diverse, a differenza
 * di $_SESSION che viene distrutta alla chiusura del browser.
 *
 * NOTA: declare(strict_types=1) è gestito dai file chiamanti.
 */
require_once 'db.php';

/* ── Directory token ──────────────────────────────────────────── */

/**
 * Percorso del file JSON che mappa token → {username, scadenza}.
 * In produzione usare una directory fuori dalla webroot con permessi 700.
 */
function tokenFilePath(): string
{
    $dir = __DIR__ . '/gattile_tokens';
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }
    return $dir . '/remember.json';
}

/** Legge tutti i token dal file, eliminando quelli scaduti. */
function leggiTokens(): array
{
    $file = tokenFilePath();
    if (!file_exists($file))
        return [];

    $raw = file_get_contents($file);
    $dati = json_decode($raw, true);
    if (!is_array($dati))
        return [];

    // Rimuovi token scaduti
    $ora = time();
    $puliti = array_filter($dati, fn($v) => isset($v['scadenza']) && $v['scadenza'] > $ora);

    // Se abbiamo eliminato qualcosa, riscrivi
    if (count($puliti) !== count($dati)) {
        scriviTokens($puliti);
    }

    return $puliti;
}

/** Scrive l'array token nel file JSON. */
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
 * Verifica le credenziali.
 * Supporta:
 *   - password hashate con password_hash() — produzione
 *   - password in chiaro  — solo per il DB di esempio iniziale;
 *     se corretta, ri-hasha automaticamente la riga nel DB.
 */
function tentaLogin(string $username, string $password): ?array
{
    if (empty($username) || empty($password)) {
        return null;
    }

    try {
        $db = getDB('reader');
        $stm = $db->prepare(
            'SELECT id, nome, cognome, username, password, is_admin
             FROM utenti WHERE username = ? LIMIT 1'
        );
        $stm->execute([$username]);
        $utente = $stm->fetch();

        if (!$utente)
            return null;

        $hash = $utente['password'];
        $ok = false;

        if (str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2b$')) {
            $ok = password_verify($password, $hash);
        } else {
            // Password in chiaro (DB demo): confronto diretto poi ri-hasha
            $ok = ($password === $hash);
            if ($ok) {
                try {
                    $dbMod = getDB('modifier');
                    $upd = $dbMod->prepare('UPDATE utenti SET password = ? WHERE id = ?');
                    $upd->execute([password_hash($password, PASSWORD_BCRYPT), $utente['id']]);
                    error_log('[auth] Password re-hashata per utente id=' . $utente['id']);
                } catch (PDOException $ex) {
                    error_log('[auth] Re-hash fallito: ' . $ex->getMessage());
                }
            }
        }

        if ($ok) {
            unset($utente['password']); // mai in sessione o cookie
            return $utente;
        }
    } catch (PDOException $e) {
        error_log('[auth] Errore DB login: ' . $e->getMessage());
    }

    return null;
}

function impostaSessioneUtente(array $utente): void
{
    avviaSessione();
    session_regenerate_id(true);
    $_SESSION['utente'] = $utente;
}

/* ── Cookie "ricordami" ───────────────────────────────────────── */

const COOKIE_RICORDAMI = 'remember_username';
const COOKIE_DURATA = 72 * 3600; // 72 ore

/**
 * Crea token opaco, lo salva nel file JSON lato server e nel cookie.
 * Il cookie contiene solo il token, mai username o password.
 */
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

/**
 * Legge il cookie, verifica il token nel file JSON e restituisce
 * lo username se valido. Funziona anche dopo la chiusura del browser.
 */
function leggiRicordami(): ?string
{
    $token = $_COOKIE[COOKIE_RICORDAMI] ?? null;
    if (!$token)
        return null;

    $tokens = leggiTokens();
    $dati = $tokens[$token] ?? null;

    if (!$dati) {
        // Token non trovato o scaduto: pulisce il cookie
        eliminaRicordami();
        return null;
    }

    return $dati['username'];
}

/** Rimuove il token dal file JSON e cancella il cookie. */
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