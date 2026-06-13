<?php
// Prende i dati della home (nuovi arrivi e statistiche) dal db

declare(strict_types=1);

require_once '../includes/connessione_db.php';

// Sicurezza
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['errore' => 'Metodo non consentito', 'codice' => 'METHOD_NOT_ALLOWED']);
    exit;
}

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
        error_log('[recupera_home] prepare arrivi fallita: ' . mysqli_error($conn));
        $errore_arrivi = 'Impossibile caricare i nuovi arrivi. Riprova tra qualche minuto.';
    } else {
        if (!mysqli_stmt_execute($stm)) {
            error_log('[recupera_home] execute arrivi fallita: ' . mysqli_stmt_error($stm));
            $errore_arrivi = 'Impossibile caricare i nuovi arrivi. Riprova tra qualche minuto.';
        } else {
            $risultato = mysqli_stmt_get_result($stm);
            while ($g = mysqli_fetch_assoc($risultato)) {
                $nuovi_arrivi[] = [
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
                    'foto' => isset($g['foto']) && trim((string) $g['foto']) !== ''
                        ? $g['foto']
                        : 'img/placeholder-gatto.svg',
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
        error_log('[recupera_home] prepare statistiche fallita: ' . mysqli_error($conn));
        $errore_statistiche = 'Statistiche non disponibili al momento. Riprova tra qualche minuto.';
    } else {
        if (!mysqli_stmt_execute($stm)) {
            error_log('[recupera_home] execute statistiche fallita: ' . mysqli_stmt_error($stm));
            $errore_statistiche = 'Statistiche non disponibili al momento. Riprova tra qualche minuto.';
        } else {
            $risultato = mysqli_stmt_get_result($stm);
            if ($risultato !== false) {
                $riga = mysqli_fetch_assoc($risultato);
                if ($riga) {
                    $statistiche['gatti'] = (int) $riga['totale_gatti'];
                    $statistiche['visite'] = (int) $riga['totale_visite'];
                    $statistiche['volontari'] = (int) $riga['totale_volontari'];
                    $statistiche['arrivi'] = (int) $riga['nuovi_arrivi'];
                } else {
                    $errore_statistiche = 'Statistiche non disponibili al momento. Riprova tra qualche minuto.';
                }
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
