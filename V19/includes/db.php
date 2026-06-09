<?php
// Connessioni al database, una per ruolo (lettura / modifica / inserimento).
// Niente eccezioni MySQLi: controllo sempre il valore di ritorno.
// strict_types lo dichiarano i file che includono questo.

define('DB_HOST', 'localhost');
define('DB_NAME', 'gattile_db');
define('DB_CHARSET', 'utf8mb4');

const ELENCO_UTENTI_DB = [
    'reader' => ['user' => 'lecture', 'pass' => 'P@ssw0rd!'],
    'modifier' => ['user' => 'modifier', 'pass' => 'Str0ng#Admin9'],
    'registrator' => ['user' => 'registrator', 'pass' => 'ToB31nsert?'],
];

// Ritorna una connessione col ruolo richiesto, oppure null se qualcosa va storto.
function connessioneDb(string $ruolo = 'reader'): ?mysqli
{
    if (!isset(ELENCO_UTENTI_DB[$ruolo])) {
        error_log('[db] ruolo inesistente: ' . $ruolo);
        return null;
    }

    $credenziali = ELENCO_UTENTI_DB[$ruolo];

    mysqli_report(MYSQLI_REPORT_OFF);

    // La @ silenzia il warning di connessione fallita; l'errore vero lo
    // intercetto subito sotto e finisce solo nel log, mai a video.
    $conn = @mysqli_connect(DB_HOST, $credenziali['user'], $credenziali['pass'], DB_NAME);

    if (!$conn || mysqli_connect_errno()) {
        error_log('[db] connessione fallita (ruolo=' . $ruolo . '): ' . mysqli_connect_error());
        return null;
    }

    if (!mysqli_set_charset($conn, DB_CHARSET)) {
        error_log('[db] charset non impostato: ' . DB_CHARSET);
    }

    return $conn;
}
