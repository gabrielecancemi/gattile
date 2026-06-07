<?php
/**
 * db.php — Gestione connessioni al database con principio del minimo privilegio.
 * Tre utenti distinti: lettore, modificatore, registratore.
 * Usa MySQLi senza eccezioni: ogni errore viene verificato sul valore di ritorno.
 *
 * NOTA: declare(strict_types=1) è gestito dai file chiamanti.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'gattile_db');
define('DB_CHARSET', 'utf8mb4');

const DB_USERS = [
    'reader' => ['user' => 'lecture', 'pass' => 'P@ssw0rd!'],
    'modifier' => ['user' => 'modifier', 'pass' => 'Str0ng#Admin9'],
    'registrator' => ['user' => 'registrator', 'pass' => 'ToB31nsert?'],
];

/** 
 * Restituisce una connessione MySQLi con il ruolo richiesto,
 * oppure NULL in caso di errore (il chiamante deve verificare).
 *
 * @param  string $role  'reader' | 'modifier' | 'registrator'
 * @return mysqli|null
 */
function getDB(string $role = 'reader'): ?mysqli
{
    if (!isset(DB_USERS[$role])) {
        error_log('[db] Ruolo DB non valido: ' . $role);
        return null;
    }

    $creds = DB_USERS[$role];

    // Disattiva le eccezioni native di MySQLi: gestiamo noi l'errore
    // sul valore di ritorno.
    mysqli_report(MYSQLI_REPORT_OFF);

    // L'operatore @ sopprime il warning E_WARNING emesso da mysqli_connect()
    // in caso di credenziali/permessi non validi: l'errore viene comunque
    // intercettato qui sotto (mysqli_connect_errno) e registrato solo nel
    // log lato server, mai mostrato all'utente.
    $conn = @mysqli_connect(DB_HOST, $creds['user'], $creds['pass'], DB_NAME);

    if (!$conn || mysqli_connect_errno()) {
        error_log('[db] Connessione fallita (ruolo=' . $role . '): ' . mysqli_connect_error());
        return null;
    }

    // Imposta charset della connessione
    if (!mysqli_set_charset($conn, DB_CHARSET)) {
        error_log('[db] Impossibile impostare charset ' . DB_CHARSET);
    }

    return $conn;
}
