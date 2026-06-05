<?php
/**
 * api/turni.php — Endpoint JSON per le fasce orarie volontariato.
 * GET: restituisce fasce orarie con conteggio iscritti.
 * POST: prenota uno o più turni per l'utente loggato.
 */
declare(strict_types=1);

require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

avviaSessione();

const MAX_VOLONTARI = 2;

/* ── GET: lista fasce orarie ─────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $fasce  = [];
    $inizio = new DateTime('today 09:00');
    $fine   = new DateTime('+14 days 17:00');

    $conn = null;
    try {
        $conn = getDB('reader');
        $now  = new DateTime();
        $data = clone $inizio;

        while ($data <= $fine) {
            if ((int)$data->format('w') !== 0 && $data > $now) {
                $iso = $data->format('Y-m-d H:i:s');

                $stm = mysqli_prepare(
                    $conn,
                    'SELECT COUNT(*) AS conteggio FROM turni_volontariato WHERE fascia_oraria = ?'
                );
                if (!$stm) throw new RuntimeException(mysqli_error($conn));
                mysqli_stmt_bind_param($stm, 's', $iso);
                mysqli_stmt_execute($stm);
                $res       = mysqli_stmt_get_result($stm);
                $row       = mysqli_fetch_assoc($res);
                $conteggio = (int)($row['conteggio'] ?? 0);
                mysqli_stmt_close($stm);

                $fasce[] = [
                    'fascia_oraria' => $iso,
                    'etichetta'     => $data->format('D d/m/Y H:i'),
                    'iscritti'      => $conteggio,
                    'max'           => MAX_VOLONTARI,
                    'piena'         => ($conteggio >= MAX_VOLONTARI),
                ];
            }

            $data->modify('+2 hours');
            if ((int)$data->format('H') >= 19) {
                $data->modify('+1 day 00:00:00');
                $data->setTime(9, 0);
            }
        }

        mysqli_close($conn);

        echo json_encode([
            'successo' => true,
            'fasce'    => $fasce,
        ], JSON_UNESCAPED_UNICODE);

    } catch (RuntimeException $e) {
        if ($conn) mysqli_close($conn);
        error_log('Errore DB api/turni.php GET: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['errore' => 'Errore del database: impossibile caricare i turni.', 'codice' => 'DB_ERROR']);
    }
    exit;
}

/* ── POST: prenota turni ─────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $utente = utenteLoggato();
    if (!$utente) {
        http_response_code(401);
        echo json_encode(['errore' => "Devi effettuare l'accesso per prenotare turni.", 'codice' => 'UNAUTHORIZED']);
        exit;
    }

    $rawFasce       = $_POST['fasce'] ?? '';
    $fasceRichieste = [];

    if (is_string($rawFasce)) {
        foreach (explode(',', $rawFasce) as $f) {
            $f  = trim($f);
            $dt = DateTime::createFromFormat('Y-m-d H:i:s', $f)
               ?: DateTime::createFromFormat('Y-m-d\TH:i', $f);
            if ($dt) $fasceRichieste[] = $dt->format('Y-m-d H:i:s');
        }
    }

    if (empty($fasceRichieste)) {
        http_response_code(400);
        echo json_encode(['errore' => 'Nessuna fascia oraria valida ricevuta.', 'codice' => 'NO_SHIFTS']);
        exit;
    }

    $conn = null;
    try {
        $conn        = getDB('modifier');
        $erroriTurni = [];
        $inseriti    = 0;
        $utenteId    = (int)$utente['id'];

        foreach ($fasceRichieste as $fascia) {

            // Controllo limite volontari (indipendente dal JS)
            $stm = mysqli_prepare($conn, 'SELECT COUNT(*) AS c FROM turni_volontariato WHERE fascia_oraria = ?');
            if (!$stm) throw new RuntimeException(mysqli_error($conn));
            mysqli_stmt_bind_param($stm, 's', $fascia);
            mysqli_stmt_execute($stm);
            $res       = mysqli_stmt_get_result($stm);
            $row       = mysqli_fetch_assoc($res);
            $conteggio = (int)($row['c'] ?? 0);
            mysqli_stmt_close($stm);

            if ($conteggio >= MAX_VOLONTARI) {
                $dt = new DateTime($fascia);
                $erroriTurni[] = [
                    'codice' => 'SHIFT_FULL',
                    'fascia' => $fascia,
                    'msg'    => 'Fascia del ' . $dt->format('d/m/Y H:i') . ' già piena (2/2 volontari).',
                ];
                continue;
            }

            // Verifica che l'utente non sia già iscritto
            $chk = mysqli_prepare($conn, 'SELECT id FROM turni_volontariato WHERE utente_id = ? AND fascia_oraria = ? LIMIT 1');
            if (!$chk) throw new RuntimeException(mysqli_error($conn));
            mysqli_stmt_bind_param($chk, 'is', $utenteId, $fascia);
            mysqli_stmt_execute($chk);
            $res2    = mysqli_stmt_get_result($chk);
            $already = mysqli_fetch_assoc($res2);
            mysqli_stmt_close($chk);

            if ($already) {
                $dt = new DateTime($fascia);
                $erroriTurni[] = [
                    'codice' => 'ALREADY_BOOKED',
                    'fascia' => $fascia,
                    'msg'    => 'Sei già iscritto alla fascia del ' . $dt->format('d/m/Y H:i') . '.',
                ];
                continue;
            }

            // Inserisce il turno
            $ins = mysqli_prepare($conn, 'INSERT INTO turni_volontariato (utente_id, fascia_oraria) VALUES (?, ?)');
            if (!$ins) throw new RuntimeException(mysqli_error($conn));
            mysqli_stmt_bind_param($ins, 'is', $utenteId, $fascia);
            mysqli_stmt_execute($ins);
            mysqli_stmt_close($ins);
            $inseriti++;
        }

        mysqli_close($conn);

        if ($inseriti === 0 && !empty($erroriTurni)) {
            http_response_code(409);
            echo json_encode([
                'errore'   => 'Nessun turno è stato prenotato.',
                'codice'   => 'ALL_FAILED',
                'dettagli' => $erroriTurni,
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'successo'  => true,
                'inseriti'  => $inseriti,
                'avvisi'    => $erroriTurni,
                'messaggio' => "Prenotati {$inseriti} turno/i con successo!"
                             . (empty($erroriTurni) ? '' : ' Alcuni turni non sono stati prenotati (vedi avvisi).'),
            ], JSON_UNESCAPED_UNICODE);
        }

    } catch (RuntimeException $e) {
        if ($conn) mysqli_close($conn);
        error_log('Errore DB api/turni.php POST: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['errore' => 'Errore del database durante la prenotazione.', 'codice' => 'DB_ERROR']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['errore' => 'Metodo non consentito', 'codice' => 'METHOD_NOT_ALLOWED']);
