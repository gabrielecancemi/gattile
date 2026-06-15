<?php
// Prende i gatti disponibili nella tabella 'gatti' del db

require_once '../componenti/connessione_db.php';
require_once '../componenti/gestione_log.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['errore' => 'Metodo non consentito']);
} else {
    $conn = connessioneDb('reader');
    if (!$conn) {
        scriviLog('errore', 'recupera_gatti: connessione al database non riuscita');
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['errore' => "Errore del database: impossibile recuperare l'elenco dei gatti.", 'codice' => 'DB_ERROR']);
    } else {
        $stm = mysqli_prepare(
            $conn,
            'SELECT id, nome, descrizione, peso, colore_mantello, lunghezza_pelo,
                    razza, colore_occhi, eta, sesso, data_arrivo
             FROM gatti
             ORDER BY data_arrivo DESC'
        );

        if (!$stm) {
            scriviLog('errore', 'recupera_gatti: prepare fallita - ' . mysqli_error($conn));
            mysqli_close($conn);
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['errore' => "Errore del database: impossibile recuperare l'elenco dei gatti.", 'codice' => 'DB_ERROR']);
        } else if (!mysqli_stmt_execute($stm)) {
            scriviLog('errore', 'recupera_gatti: execute fallita - ' . mysqli_stmt_error($stm));
            mysqli_stmt_close($stm);
            mysqli_close($conn);
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['errore' => "Errore del database: impossibile recuperare l'elenco dei gatti.", 'codice' => 'DB_ERROR']);
        } else {
            // Lettura
            mysqli_stmt_bind_result(
                $stm,
                $id,
                $nome,
                $descrizione,
                $peso,
                $colore_mantello,
                $lunghezza_pelo,
                $razza,
                $colore_occhi,
                $eta,
                $sesso,
                $data_arrivo
            );

            $elenco_gatti = [];

            while (mysqli_stmt_fetch($stm)) {
                $elenco_gatti[] = [
                    'id' => (int) $id,
                    'nome' => $nome,
                    'descrizione' => $descrizione,
                    'peso' => (float) $peso,
                    'colore_mantello' => $colore_mantello,
                    'lunghezza_pelo' => $lunghezza_pelo,
                    'razza' => $razza,
                    'colore_occhi' => $colore_occhi,
                    'eta' => (int) $eta,
                    'sesso' => $sesso,
                    'data_arrivo' => $data_arrivo,
                    // Placeholder fisso (foto reali in una versione futura).
                    'foto' => 'img/placeholder_gatto.png',
                ];
            }

            mysqli_stmt_close($stm);
            mysqli_close($conn);

            echo json_encode([
                'successo' => true,
                'gatti' => $elenco_gatti,
                'totale' => count($elenco_gatti),
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}