<?php
// funzioni condivise per l'output

require_once 'sessione.php';

// HTML sicuro
function ripulisci(mixed $valore): string
{
    return htmlspecialchars((string) $valore, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Messaggio per l'utente: 'errore' - 'successo' - 'avviso'
function avvisoUtente(string $testo, string $tipo = 'errore'): string
{
    $ruolo = ($tipo === 'errore') ? 'alert' : 'status';
    $cls = "messaggio messaggio-{$tipo}";
    $testo_pulito = ripulisci($testo);
    return "<output class=\"{$cls}\" role=\"{$ruolo}\" aria-live=\"assertive\">{$testo_pulito}</output>";
}

// Memorizza un messaggio (errore o successo) per la pagina nuova
function impostaMessaggioFlash(string $tipo, string $testo): void
{
    if (session_status() === PHP_SESSION_NONE) {
        aprireSessione();
    }
    $_SESSION['messaggio_flash'] = ['tipo' => $tipo, 'testo' => $testo];
}

// Cancella il messaggio flash eventualmente presente in sessione
function leggiMessaggioFlash(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        aprireSessione();
    }
    if (empty($_SESSION['messaggio_flash'])) {
        return null;
    }
    $flash = $_SESSION['messaggio_flash'];
    unset($_SESSION['messaggio_flash']);
    // Il flash è un array con chiavi 'tipo' e 'testo'.
    if (isset($flash['tipo'], $flash['testo'])) {
        return $flash;
    }
    return null;
}
