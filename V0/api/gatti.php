<?php
/**
 * api/gatti.php — Endpoint JSON per il componente React.
 * Restituisce la lista dei gatti con filtro opzionale.
 * Usa utente "lettore" (solo SELECT).
 */
declare(strict_types=1);

require_once '../includes/db.php';

// Intestazioni CORS e content-type (solo stesso dominio in produzione)
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Solo GET accettato
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['errore' => 'Metodo non consentito']);
    exit;
}

try {
    $db = getDB('reader');

    // Query base — tutte le colonne utili al componente React
    $sql = 'SELECT id, nome, descrizione, peso, colore_mantello, lunghezza_pelo,
                   razza, colore_occhi, eta, sesso, data_arrivo
            FROM gatti
            ORDER BY data_arrivo DESC';

    $stm = $db->prepare($sql);
    $stm->execute();
    $gatti = $stm->fetchAll();

    // Formatta i dati per il JSON
    $risultato = array_map(function(array $g): array {
        return [
            'id'              => (int)$g['id'],
            'nome'            => $g['nome'],
            'descrizione'     => $g['descrizione'],
            'peso'            => (float)$g['peso'],
            'colore_mantello' => $g['colore_mantello'],
            'lunghezza_pelo'  => $g['lunghezza_pelo'],
            'razza'           => $g['razza'],
            'colore_occhi'    => $g['colore_occhi'],
            'eta'             => (int)$g['eta'],
            'sesso'           => $g['sesso'],
            'data_arrivo'     => $g['data_arrivo'],
            'img'             => '../img/placeholder-gatto.svg', // futura release: foto reali
        ];
    }, $gatti);

    echo json_encode([
        'successo' => true,
        'gatti'    => $risultato,
        'totale'   => count($risultato),
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log('Errore DB api/gatti.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'errore'  => 'Errore del database: impossibile recuperare l\'elenco dei gatti.',
        'codice'  => 'DB_ERROR',
    ]);
}
