<?php
// Connessioni al DB per ruolo, con privilegio minimo. Mai l'utente admin.

// Ritorna la connessione per il ruolo, o null in caso di errore. Il report è disabilitato perchè gestito a mano
mysqli_report(MYSQLI_REPORT_OFF);
function connessioneDb(string $ruolo = 'reader'): ?mysqli
{
    $db_host = 'localhost';
    $db_name = 'gattile_db';
    $db_charset = 'utf8mb4';

    // Elenco degli utenti DB predefiniti, uno per ruolo.
    $elenco_utenti = [
        'reader' => ['user' => 'lecture', 'pass' => 'P@ssw0rd!'],
        'modifier' => ['user' => 'modifier', 'pass' => 'Str0ng#Admin9'],
        'registrator' => ['user' => 'registrator', 'pass' => 'ToB31nsert?'],
    ];

    if (!isset($elenco_utenti[$ruolo])) {
        // Ruolo non previsto: nessuna connessione.
        return null;
    }

    $credenziali = $elenco_utenti[$ruolo];

    // Apre la connessione e controlla l'errore. La @ evita segnalazioni incomprensibili
    $conn = @mysqli_connect($db_host, $credenziali['user'], $credenziali['pass'], $db_name);

    if (!$conn || mysqli_connect_errno()) {
        // Collegamento al DB non riuscito.
        return null;
    }

    // Imposta la codifica della connessione.
    mysqli_set_charset($conn, $db_charset);

    return $conn;
}
