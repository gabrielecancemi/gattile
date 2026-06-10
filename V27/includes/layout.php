<?php
// funzioni condivise per l'output

require_once 'sessione.php';

// HTML sicuro per ogni valore stampato a video.
function ripulisci(mixed $valore): string
{
    return htmlspecialchars((string) $valore, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Messaggio per l'utente: 'errore' | 'successo' | 'avviso'.
// L'etichetta (Errore/OK/Info) viene mostrata davanti al testo tramite CSS
// (regola .messaggio-errore::before ecc.), così appare automaticamente prima
// di ogni messaggio, anche quelli generati via JavaScript.
function avvisoUtente(string $testo, string $tipo = 'errore'): string
{
    $ruolo = ($tipo === 'errore') ? 'alert' : 'status';
    $cls = "messaggio messaggio-{$tipo}";
    $testo_pulito = ripulisci($testo);
    return "<output class=\"{$cls}\" role=\"{$ruolo}\" aria-live=\"assertive\">{$testo_pulito}</output>";
}

// Memorizza un messaggio (errore o successo) in sessione per mostrarlo dopo un
// redirect (schema Post/Redirect/Get): evita il reinvio del form premendo F5.
function impostaMessaggioFlash(string $tipo, string $testo): void
{
    if (session_status() === PHP_SESSION_NONE) {
        aprireSessione();
    }
    $_SESSION['messaggio_flash'] = ['tipo' => $tipo, 'testo' => $testo];
}

// Recupera e cancella il messaggio flash eventualmente presente in sessione.
// Restituisce ['tipo' => ..., 'testo' => ...] oppure null.
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
    return is_array($flash) ? $flash : null;
}
