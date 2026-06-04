<?php
/**
 * Gestione connessioni al database con principio del minimo privilegio.
 * Tre utenti distinti: lettore, modificatore, registratore.
 *
 * NOTA: declare(strict_types=1) è gestito dai file chiamanti.
 */

// Configurazione DB — in produzione usare variabili d'ambiente o file fuori webroot
define('DB_HOST', 'localhost');
define('DB_NAME', 'gattile_db');
define('DB_CHARSET', 'utf8mb4');

// Credenziali per ruolo
const DB_USERS = [
    'reader'      => ['user' => 'lecture',     'pass' => 'P@ssw0rd!'],
    'modifier'    => ['user' => 'modifier',    'pass' => 'Str0ng#Admin9'],
    'registrator' => ['user' => 'registrator', 'pass' => 'ToB31nsert?'],
];

/**
 * Restituisce una connessione PDO con il ruolo richiesto.
 * Lancia PDOException in caso di errore (gestita dai chiamanti).
 *
 * @param string $role 'reader' | 'modifier' | 'registrator'
 * @return PDO
 */
function getDB(string $role = 'reader'): PDO
{
    if (!isset(DB_USERS[$role])) {
        throw new InvalidArgumentException("Ruolo DB non valido: $role");
    }

    $creds = DB_USERS[$role];
    $dsn   = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // prepared statements reali
    ];

    return new PDO($dsn, $creds['user'], $creds['pass'], $options);
}
