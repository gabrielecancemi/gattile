<?php
// Prende i dati della home (nuovi arrivi e statistiche) dal db

require_once '../includes/connessione_db.php';
require_once '../includes/log.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['errore' => 'Metodo non consentito', 'codice' => 'METHOD_NOT_ALLOWED']);
} else {

    $errore_arrivi = null;
    $errore_statistiche = null;
    $nuovi_arrivi = [];
    $statistiche = [
        'gatti' => 0,
        'visite' => 0,
        'volontari' => 0,
        'arrivi' => 0,
    ];

    $conn = connessioneDb('reader');
    if (!$conn) {
        scriviLog('errore', 'recupera_home: connessione al database non riuscita');
        $errore_arrivi = 'Impossibile caricare i nuovi arrivi. Riprova tra qualche minuto.';
        $errore_statistiche = 'Statistiche non disponibili al momento. Riprova tra qualche minuto.';
    } else {
        $stm = mysqli_prepare(
            $conn,
            'SELECT id, nome, descrizione, peso, eta, sesso,
                    colore_mantello, lunghezza_pelo, razza, colore_occhi, data_arrivo
             FROM gatti
             ORDER BY data_arrivo DESC
             LIMIT 2'
        );

        // richieste al db
        if (!$stm) {
            scriviLog('errore', 'recupera_home: prepare nuovi arrivi fallita - ' . mysqli_error($conn));
            $errore_arrivi = 'Impossibile caricare i nuovi arrivi. Riprova tra qualche minuto.';
        } else {
            if (!mysqli_stmt_execute($stm)) {
                scriviLog('errore', 'recupera_home: execute nuovi arrivi fallita - ' . mysqli_stmt_error($stm));
                $errore_arrivi = 'Impossibile caricare i nuovi arrivi. Riprova tra qualche minuto.';
            } else {
                mysqli_stmt_bind_result(
                    $stm,
                    $id,
                    $nome,
                    $descrizione,
                    $peso,
                    $eta,
                    $sesso,
                    $colore_mantello,
                    $lunghezza_pelo,
                    $razza,
                    $colore_occhi,
                    $data_arrivo
                );
                while (mysqli_stmt_fetch($stm)) {
                    $nuovi_arrivi[] = [
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
                        'foto' => 'img/placeholder-gatto.jpg',
                    ];
                }
            }
            mysqli_stmt_close($stm);
        }

        $sql = '
            SELECT
                (SELECT COUNT(*) FROM gatti) AS totale_gatti,
                (SELECT COUNT(*) FROM visita_gatti) AS totale_visite,
                (SELECT COUNT(DISTINCT utente_id) FROM turni_volontariato) AS totale_volontari,
                (SELECT COUNT(*) FROM gatti WHERE YEAR(data_arrivo) = YEAR(CURDATE())) AS nuovi_arrivi
        ';

        $stm = mysqli_prepare($conn, $sql);

        if (!$stm) {
            scriviLog('errore', 'recupera_home: prepare statistiche fallita - ' . mysqli_error($conn));
            $errore_statistiche = 'Statistiche non disponibili al momento. Riprova tra qualche minuto.';
        } else {
            if (!mysqli_stmt_execute($stm)) {
                scriviLog('errore', 'recupera_home: execute statistiche fallita - ' . mysqli_stmt_error($stm));
                $errore_statistiche = 'Statistiche non disponibili al momento. Riprova tra qualche minuto.';
            } else {
                mysqli_stmt_bind_result($stm, $totale_gatti, $totale_visite, $totale_volontari, $nuovi_arrivi_anno);
                if (mysqli_stmt_fetch($stm)) {
                    $statistiche['gatti'] = (int) $totale_gatti;
                    $statistiche['visite'] = (int) $totale_visite;
                    $statistiche['volontari'] = (int) $totale_volontari;
                    $statistiche['arrivi'] = (int) $nuovi_arrivi_anno;
                } else {
                    $errore_statistiche = 'Statistiche non disponibili al momento. Riprova tra qualche minuto.';
                }
            }
            mysqli_stmt_close($stm);
        }
        mysqli_close($conn);
    }

    echo json_encode([
        'successo' => true,
        'nuovi_arrivi' => $nuovi_arrivi,
        'statistiche' => $statistiche,
        'errore_arrivi' => $errore_arrivi,
        'errore_statistiche' => $errore_statistiche,
    ], JSON_UNESCAPED_UNICODE);
}
