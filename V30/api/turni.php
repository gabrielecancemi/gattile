<?php
// Prenota un turno di volontariato, inserendola nel db

declare(strict_types=1);

require_once '../includes/sessione.php';
require_once '../includes/connessione_db.php';

// Sicurezza
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

aprireSessione();

const MAX_VOLONTARI = 2;

// Ricava le fasce orarie disponibili dal db (fase prima della prenotazione)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $conn = connessioneDb('reader');
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['errore' => 'Errore del database: impossibile caricare i turni.', 'codice' => 'DB_ERROR']);
        exit;
    }

    $profilo = profiloAttivo();
    $utente_id = $profilo ? (int) $profilo['id'] : 0;

    // controllo delle fasce disponibili
    $fasce = [];
    $inizio = new DateTime('today 09:00');
    $fine = new DateTime('+3 months 17:00');
    $adesso = new DateTime();

    while ($inizio <= $fine) {
        if ((int) $inizio->format('w') !== 0 && $inizio > $adesso) {
            $iso = $inizio->format('Y-m-d H:i:s');

            $stm = mysqli_prepare(
                $conn,
                'SELECT COUNT(*) AS conteggio FROM turni_volontariato WHERE fascia_oraria = ?'
            );

            if (!$stm) {
                error_log('[turni GET] prepare: ' . mysqli_error($conn));
                mysqli_close($conn);
                http_response_code(500);
                echo json_encode(['errore' => 'Errore del database: impossibile caricare i turni.', 'codice' => 'DB_ERROR']);
                exit;
            }

            mysqli_stmt_bind_param($stm, 's', $iso);
            mysqli_stmt_execute($stm);
            $risultato = mysqli_stmt_get_result($stm);
            $riga = mysqli_fetch_assoc($risultato);
            $conteggio = (int) ($riga['conteggio'] ?? 0);
            mysqli_stmt_close($stm);

            // L'utente è già iscritto a questa fascia?
            $gia_iscritto = false;
            if ($utente_id > 0) {
                $controllo = mysqli_prepare(
                    $conn,
                    'SELECT 1 FROM turni_volontariato WHERE utente_id = ? AND fascia_oraria = ? LIMIT 1'
                );
                if ($controllo) {
                    mysqli_stmt_bind_param($controllo, 'is', $utente_id, $iso);
                    mysqli_stmt_execute($controllo);
                    $risultato_controllo = mysqli_stmt_get_result($controllo);
                    $gia_iscritto = (mysqli_fetch_row($risultato_controllo) !== null);
                    mysqli_stmt_close($controllo);
                }
            }

            $fasce[] = [
                'fascia_oraria' => $iso,
                'etichetta' => $inizio->format('D d/m/Y H:i'),
                'iscritti' => $conteggio,
                'max' => MAX_VOLONTARI,
                'piena' => ($conteggio >= MAX_VOLONTARI),
                'gia_iscritto' => $gia_iscritto,
            ];
        }

        // Generazione dei turni da 2 ore
        $inizio->modify('+1 hour');
        if ((int) $inizio->format('H') >= 18) {
            $inizio->modify('+1 day 00:00:00');
            $inizio->setTime(9, 0);
        }
    }

    mysqli_close($conn);
    echo json_encode(['successo' => true, 'fasce' => $fasce], JSON_UNESCAPED_UNICODE);
    exit;
}

// Effettua una prenotazione
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $profilo = profiloAttivo();
    if (!$profilo) {
        http_response_code(401);
        echo json_encode(['errore' => "Devi effettuare l'accesso per prenotare turni.", 'codice' => 'UNAUTHORIZED']);
        exit;
    }

    $fasce_grezze = $_POST['fasce'] ?? '';
    $fasce_richieste = [];

    if (is_string($fasce_grezze)) {
        foreach (explode(',', $fasce_grezze) as $f) {
            $f = trim($f);
            $timestamp = strtotime($f);
            if ($timestamp)
                $fasce_richieste[] = date('Y-m-d H:i:s', $timestamp);
        }
    }

    if (empty($fasce_richieste)) {
        http_response_code(400);
        echo json_encode(['errore' => 'Nessuna fascia oraria valida ricevuta.', 'codice' => 'NO_SHIFTS']);
        exit;
    }

    $conn = connessioneDb('modifier');
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['errore' => 'Errore del database durante la prenotazione.', 'codice' => 'DB_ERROR']);
        exit;
    }

    $avvisi_turni = [];
    $inseriti = 0;
    $utente_id = (int) $profilo['id'];
    $errore_db = false;

    foreach ($fasce_richieste as $fascia) {

        // Limite volontari.
        $stm = mysqli_prepare($conn, 'SELECT COUNT(*) AS c FROM turni_volontariato WHERE fascia_oraria = ?');
        if (!$stm) {
            error_log('[turni POST] prepare count: ' . mysqli_error($conn));
            $errore_db = true;
            break;
        }
        mysqli_stmt_bind_param($stm, 's', $fascia);
        mysqli_stmt_execute($stm);
        $risultato = mysqli_stmt_get_result($stm);
        $riga = mysqli_fetch_assoc($risultato);
        $conteggio = (int) ($riga['c'] ?? 0);
        mysqli_stmt_close($stm);

        if ($conteggio >= MAX_VOLONTARI) {
            $dt = new DateTime($fascia);
            $avvisi_turni[] = [
                'codice' => 'SHIFT_FULL',
                'fascia' => $fascia,
                'msg' => 'Fascia del ' . $dt->format('d/m/Y H:i') . ' già piena (2/2 volontari).',
            ];
            continue;
        }

        // L'utente non deve essere già iscritto.
        $controllo = mysqli_prepare($conn, 'SELECT id FROM turni_volontariato WHERE utente_id = ? AND fascia_oraria = ? LIMIT 1');
        if (!$controllo) {
            error_log('[turni POST] prepare check: ' . mysqli_error($conn));
            $errore_db = true;
            break;
        }
        mysqli_stmt_bind_param($controllo, 'is', $utente_id, $fascia);
        mysqli_stmt_execute($controllo);
        $risultato_controllo = mysqli_stmt_get_result($controllo);
        $gia_presente = mysqli_fetch_assoc($risultato_controllo);
        mysqli_stmt_close($controllo);

        if ($gia_presente) {
            $dt = new DateTime($fascia);
            $avvisi_turni[] = [
                'codice' => 'ALREADY_BOOKED',
                'fascia' => $fascia,
                'msg' => 'Sei già iscritto alla fascia del ' . $dt->format('d/m/Y H:i') . '.',
            ];
            continue;
        }

        // Inserisce il turno.
        $inserimento = mysqli_prepare($conn, 'INSERT INTO turni_volontariato (utente_id, fascia_oraria) VALUES (?, ?)');
        if (!$inserimento) {
            error_log('[turni POST] prepare insert: ' . mysqli_error($conn));
            $errore_db = true;
            break;
        }
        mysqli_stmt_bind_param($inserimento, 'is', $utente_id, $fascia);
        if (!mysqli_stmt_execute($inserimento)) {
            error_log('[turni POST] execute insert: ' . mysqli_stmt_error($inserimento));
            mysqli_stmt_close($inserimento);
            $errore_db = true;
            break;
        }
        mysqli_stmt_close($inserimento);
        $inseriti++;
    }

    mysqli_close($conn);

    if ($errore_db) {
        http_response_code(500);
        echo json_encode(['errore' => 'Errore del database durante la prenotazione.', 'codice' => 'DB_ERROR']);
        exit;
    }

    if ($inseriti === 0 && !empty($avvisi_turni)) {
        http_response_code(409);
        echo json_encode([
            'errore' => 'Nessun turno è stato prenotato.',
            'codice' => 'ALL_FAILED',
            'dettagli' => $avvisi_turni,
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'successo' => true,
            'inseriti' => $inseriti,
            'avvisi' => $avvisi_turni,
            'messaggio' => "Prenotati {$inseriti} turno/i con successo!"
                . (empty($avvisi_turni) ? '' : ' Alcuni turni non sono stati prenotati (vedi avvisi).'),
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

http_response_code(405);
echo json_encode(['errore' => 'Metodo non consentito', 'codice' => 'METHOD_NOT_ALLOWED']);
