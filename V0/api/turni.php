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

// Limite volontari per fascia
const MAX_VOLONTARI = 2;

// ── GET: lista fasce orarie ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    /*
     * Genera fasce orarie per le prossime 2 settimane:
     * - dalle 9:00 alle 17:00, ogni 2 ore
     * - lunedì-sabato (il gattile è chiuso la domenica)
     */
    $fasce = [];
    $inizio = new DateTime('today 09:00');
    $fine   = new DateTime('+14 days 17:00');

    try {
        $db = getDB('reader');

        $now = new DateTime();
        $data = clone $inizio;

        while ($data <= $fine) {
            // Salta domenica (0) e fasce già passate
            if ((int)$data->format('w') !== 0 && $data > $now) {
                $iso = $data->format('Y-m-d H:i:s');

                // Conta quanti volontari già iscritti
                $stm = $db->prepare(
                    'SELECT COUNT(*) AS conteggio FROM turni_volontariato WHERE fascia_oraria = ?'
                );
                $stm->execute([$iso]);
                $conteggio = (int)$stm->fetchColumn();

                $fasce[] = [
                    'fascia_oraria' => $iso,
                    'etichetta'     => $data->format('D d/m/Y H:i'),
                    'iscritti'      => $conteggio,
                    'max'           => MAX_VOLONTARI,
                    'piena'         => ($conteggio >= MAX_VOLONTARI),
                ];
            }

            // Prossima fascia: +2 ore, ma se raggiungiamo le 17 saltiamo al giorno dopo alle 9
            $data->modify('+2 hours');
            if ((int)$data->format('H') >= 19) {
                $data->modify('+1 day 00:00:00');
                $data->setTime(9, 0);
            }
        }

        echo json_encode([
            'successo' => true,
            'fasce'    => $fasce,
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        error_log('Errore DB api/turni.php GET: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['errore' => 'Errore del database: impossibile caricare i turni.', 'codice' => 'DB_ERROR']);
    }
    exit;
}

// ── POST: prenota turni ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $utente = utenteLoggato();
    if (!$utente) {
        http_response_code(401);
        echo json_encode(['errore' => 'Devi effettuare l\'accesso per prenotare turni.', 'codice' => 'UNAUTHORIZED']);
        exit;
    }

    $rawFasce = $_POST['fasce'] ?? '';
    $fasceRichieste = [];

    if (is_string($rawFasce)) {
        foreach (explode(',', $rawFasce) as $f) {
            $f = trim($f);
            $dt = DateTime::createFromFormat('Y-m-d H:i:s', $f)
               ?: DateTime::createFromFormat('Y-m-d\TH:i', $f);
            if ($dt) {
                $fasceRichieste[] = $dt->format('Y-m-d H:i:s');
            }
        }
    }

    if (empty($fasceRichieste)) {
        http_response_code(400);
        echo json_encode(['errore' => 'Nessuna fascia oraria valida ricevuta.', 'codice' => 'NO_SHIFTS']);
        exit;
    }

    try {
        $db = getDB('modifier');
        $erroriTurni  = [];
        $inseriti     = 0;

        foreach ($fasceRichieste as $fascia) {
            // Controllo integrità server: massimo 2 volontari (indipendente dal JS)
            $stm = $db->prepare(
                'SELECT COUNT(*) FROM turni_volontariato WHERE fascia_oraria = ?'
            );
            $stm->execute([$fascia]);
            $conteggio = (int)$stm->fetchColumn();

            if ($conteggio >= MAX_VOLONTARI) {
                // Errore specifico con codice strutturato (il JS lo intercetta)
                $dt = new DateTime($fascia);
                $erroriTurni[] = [
                    'codice'  => 'SHIFT_FULL',
                    'fascia'  => $fascia,
                    'msg'     => 'Fascia del ' . $dt->format('d/m/Y H:i') . ' già piena (2/2 volontari).',
                ];
                continue;
            }

            // Verifica che l'utente non sia già iscritto
            $chk = $db->prepare(
                'SELECT id FROM turni_volontariato WHERE utente_id = ? AND fascia_oraria = ? LIMIT 1'
            );
            $chk->execute([(int)$utente['id'], $fascia]);
            if ($chk->fetch()) {
                $dt = new DateTime($fascia);
                $erroriTurni[] = [
                    'codice' => 'ALREADY_BOOKED',
                    'fascia' => $fascia,
                    'msg'    => 'Sei già iscritto alla fascia del ' . $dt->format('d/m/Y H:i') . '.',
                ];
                continue;
            }

            // Inserisce il turno
            $ins = $db->prepare(
                'INSERT INTO turni_volontariato (utente_id, fascia_oraria) VALUES (?, ?)'
            );
            $ins->execute([(int)$utente['id'], $fascia]);
            $inseriti++;
        }

        if ($inseriti === 0 && !empty($erroriTurni)) {
            http_response_code(409);
            echo json_encode([
                'errore'  => 'Nessun turno è stato prenotato.',
                'codice'  => 'ALL_FAILED',
                'dettagli'=> $erroriTurni,
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(200);
            echo json_encode([
                'successo'  => true,
                'inseriti'  => $inseriti,
                'avvisi'    => $erroriTurni,
                'messaggio' => "Prenotati {$inseriti} turno/i con successo!" . (empty($erroriTurni) ? '' : ' Alcuni turni non sono stati prenotati (vedi avvisi).'),
            ], JSON_UNESCAPED_UNICODE);
        }

    } catch (PDOException $e) {
        error_log('Errore DB api/turni.php POST: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['errore' => 'Errore del database durante la prenotazione.', 'codice' => 'DB_ERROR']);
    }
    exit;
}

// Metodo non supportato
http_response_code(405);
echo json_encode(['errore' => 'Metodo non consentito', 'codice' => 'METHOD_NOT_ALLOWED']);
