<?php
/**
 * auth.php — Sessione, autenticazione e cookie "ricordami".
 * Il cookie contiene un token opaco (non credenziali in chiaro).
 * Il login supporta sia password hashate (bcrypt) sia testo piano
 * per compatibilità con il DB di esempio; in produzione solo bcrypt.
 */
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/* ── Sessione ─────────────────────────────────────────────────── */

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

function utenteLoggato(): ?array
{
    avviaSessione();
    return $_SESSION['utente'] ?? null;
}

function isAdmin(): bool
{
    $u = utenteLoggato();
    return $u !== null && (bool)$u['is_admin'];
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
        $db  = getDB('reader');
        $stm = $db->prepare(
            'SELECT id, nome, cognome, username, password, is_admin
             FROM utenti WHERE username = ? LIMIT 1'
        );
        $stm->execute([$username]);
        $utente = $stm->fetch();

        if (!$utente) {
            return null;
        }

        $hash = $utente['password'];
        $ok   = false;

        if (str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2b$')) {
            // Password già hashata con bcrypt
            $ok = password_verify($password, $hash);
        } else {
            // Password in chiaro (DB demo) — confronto diretto, poi ri-hasha
            $ok = ($password === $hash);
            if ($ok) {
                // Aggiorna la riga con hash bcrypt per i login futuri
                try {
                    $dbMod = getDB('modifier');
                    $upd   = $dbMod->prepare('UPDATE utenti SET password = ? WHERE id = ?');
                    $upd->execute([password_hash($password, PASSWORD_BCRYPT), $utente['id']]);
                    error_log('[auth] Password re-hashata per utente id=' . $utente['id']);
                } catch (PDOException $ex) {
                    error_log('[auth] Re-hash fallito: ' . $ex->getMessage());
                }
            }
        }

        if ($ok) {
            unset($utente['password']); // mai in sessione
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
    session_regenerate_id(true); // prevenzione session fixation
    $_SESSION['utente'] = $utente;
}

/* ── Cookie "ricordami" ───────────────────────────────────────── */

const COOKIE_RICORDAMI = 'gattile_remember';
const COOKIE_DURATA    = 72 * 3600; // 72 ore

/**
 * Crea token opaco e lo memorizza in sessione.
 * In produzione: inserire in tabella remember_tokens(token_hash, utente_id, scadenza).
 */
function impostaRicordami(string $username): void
{
    $token = bin2hex(random_bytes(32));
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

function logout(): void
{
    avviaSessione();
    eliminaRicordami();
    $_SESSION = [];
    session_destroy();
}
