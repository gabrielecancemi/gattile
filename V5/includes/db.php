<?php
/**
 * db.php — Gestione connessioni al database con principio del minimo privilegio.
 * Tre utenti distinti: lettore, modificatore, registratore.
 * Usa MySQLi come da specifiche del corso.
 *
 * NOTA: declare(strict_types=1) è gestito dai file chiamanti.
 */

// Configurazione DB — in produzione usare variabili d'ambiente o file fuori webroot
define('DB_HOST',    'localhost');
define('DB_NAME',    'gattile_db');
define('DB_CHARSET', 'utf8mb4');

// Credenziali per ruolo
const DB_USERS = [
    'reader'      => ['user' => 'lecture',     'pass' => 'P@ssw0rd!'],
    'modifier'    => ['user' => 'modifier',    'pass' => 'Str0ng#Admin9'],
    'registrator' => ['user' => 'registrator', 'pass' => 'ToB31nsert?'],
];

/**
 * Restituisce una connessione MySQLi con il ruolo richiesto.
 * In caso di errore registra il problema e lancia una RuntimeException
 * (gestita dai chiamanti che mostreranno un messaggio all'utente).
 *
 * @param  string $role  'reader' | 'modifier' | 'registrator'
 * @return mysqli
 * @throws InvalidArgumentException  se il ruolo non esiste
 * @throws RuntimeException          se la connessione fallisce
 */
function getDB(string $role = 'reader'): mysqli
{
    if (!isset(DB_USERS[$role])) {
        throw new InvalidArgumentException("Ruolo DB non valido: $role");
    }

    $creds = DB_USERS[$role];

    // Sopprime i warning nativi di MySQLi: gestiamo noi l'errore
    mysqli_report(MYSQLI_REPORT_OFF);

    $conn = mysqli_connect(DB_HOST, $creds['user'], $creds['pass'], DB_NAME);

    if (mysqli_connect_errno()) {
        $msg = mysqli_connect_error();
        error_log('[db] Connessione fallita (ruolo=' . $role . '): ' . $msg);
        throw new RuntimeException(
            'Impossibile connettersi al database. Riprova tra qualche minuto.'
        );
    }

    // Imposta charset della connessione (obbligatorio per utf8mb4)
    mysqli_set_charset($conn, DB_CHARSET);

    return $conn;
}
