<?php
// Prende i gatti disponibili nella tabella 'gatti' del db

declare(strict_types=1);

require_once '../includes/connessione_db.php';

// Sicurezza
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['errore' => 'Metodo non consentito']);
    exit;
}

$conn = connessioneDb('reader');
if (!$conn) {
    http_response_code(500);
    echo json_encode(['errore' => "Errore del database: impossibile recuperare l'elenco dei gatti.", 'codice' => 'DB_ERROR']);
    exit;
}

$stm = mysqli_prepare(
    $conn,
    'SELECT *
     FROM gatti
     ORDER BY data_arrivo DESC'
);

if (!$stm) {
    error_log('Errore DB api/recupera_gatti.php prepare: ' . mysqli_error($conn));
    mysqli_close($conn);
    http_response_code(500);
    echo json_encode(['errore' => "Errore del database: impossibile recuperare l'elenco dei gatti.", 'codice' => 'DB_ERROR']);
    exit;
}

if (!mysqli_stmt_execute($stm)) {
    error_log('Errore DB api/recupera_gatti.php execute: ' . mysqli_stmt_error($stm));
    mysqli_stmt_close($stm);
    mysqli_close($conn);
    http_response_code(500);
    echo json_encode(['errore' => "Errore del database: impossibile recuperare l'elenco dei gatti.", 'codice' => 'DB_ERROR']);
    exit;
}

$risultato_query = mysqli_stmt_get_result($stm);
$elenco_gatti = [];

while ($g = mysqli_fetch_assoc($risultato_query)) {
    $elenco_gatti[] = [
        'id' => (int) $g['id'],
        'nome' => $g['nome'],
        'descrizione' => $g['descrizione'],
        'peso' => (float) $g['peso'],
        'colore_mantello' => $g['colore_mantello'],
        'lunghezza_pelo' => $g['lunghezza_pelo'],
        'razza' => $g['razza'],
        'colore_occhi' => $g['colore_occhi'],
        'eta' => (int) $g['eta'],
        'sesso' => $g['sesso'],
        'data_arrivo' => $g['data_arrivo'],
        // Previsto per futura versione con immagini dei gatti salvati sul db
        'foto' => isset($g['foto']) && trim((string) $g['foto']) !== ''
            ? $g['foto']
            : 'img/placeholder-gatto.svg',
    ];
}

mysqli_stmt_close($stm);
mysqli_close($conn);

echo json_encode([
    'successo' => true,
    'gatti' => $elenco_gatti,
    'totale' => count($elenco_gatti),
], JSON_UNESCAPED_UNICODE);
