<?php
// Registro (log) degli eventi del sito su file lato server.
// Ogni riga riporta data e ora, livello di gravita' e messaggio.

function percorsoLog(): string
{
    $cartella = __DIR__ . '/log';
    if (!is_dir($cartella)) {
        mkdir($cartella, 0700, true);
    }
    return $cartella . '/eventi.log';
}

// Aggiunge una riga al registro ($livello: info, avviso o errore).
function scriviLog(string $livello, string $messaggio): void
{
    $riga = date('Y-m-d H:i:s') . ' [' . $livello . '] ' . $messaggio . "\n";
    file_put_contents(percorsoLog(), $riga, FILE_APPEND | LOCK_EX);
}
