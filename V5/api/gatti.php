<?php
/**
 * api/gatti.php — Endpoint JSON per il componente React.
 * Restituisce la lista dei gatti.
 * Usa utente "lettore" (solo SELECT).
 */
declare(strict_types=1);

require_once '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['errore' => 'Metodo non consentito']);
    exit;
}

$conn = null;
try {
    $conn = getDB('reader');

    $sql = 'SELECT id, nome, descrizione, peso, colore_mantello, lunghezza_pelo,
                   razza, colore_occhi, eta, sesso, data_arrivo
            FROM gatti
            ORDER BY data_arrivo DESC';

    $stm = mysqli_prepare($conn, $sql);
    if (!$stm) throw new RuntimeException(mysqli_error($conn));

    mysqli_stmt_execute($stm);
    $result = mysqli_stmt_get_result($stm);

    $risultato = [];
    while ($g = mysqli_fetch_assoc($result)) {
        $risultato[] = [
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
            'img'             => '../img/placeholder-gatto.svg',
        ];
    }

    mysqli_stmt_close($stm);
    mysqli_close($conn);

    echo json_encode([
        'successo' => true,
        'gatti'    => $risultato,
        'totale'   => count($risultato),
    ], JSON_UNESCAPED_UNICODE);

} catch (RuntimeException $e) {
    if ($conn) mysqli_close($conn);
    error_log('Errore DB api/gatti.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'errore' => "Errore del database: impossibile recuperare l'elenco dei gatti.",
        'codice' => 'DB_ERROR',
    ]);
}
