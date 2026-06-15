<?php
// Prenota un turno di volontariato, inserendolo nel db

require_once '../componenti/gestione_sessione.php';
require_once '../componenti/connessione_db.php';
require_once '../componenti/gestione_log.php';

header('Content-Type: application/json; charset=utf-8');

aprireSessione();

// Converte una fascia "AAAA-MM-GG HH:MM:SS" in "GG/MM/AAAA HH:MM".
function formattaFascia(string $iso): string
{
    $pezzi = explode(' ', trim($iso));
    if (count($pezzi) !== 2) {
        return $iso;
    }
    $d = explode('-', $pezzi[0]);
    $o = explode(':', $pezzi[1]);
    if (count($d) !== 3 || count($o) < 2) {
        return $iso;
    }
    $ts = mktime((int) $o[0], (int) $o[1], 0, (int) $d[1], (int) $d[2], (int) $d[0]);
    return date('d/m/Y H:i', $ts);
}

$metodo = $_SERVER['REQUEST_METHOD'];

// Ricava le fasce orarie disponibili dal db (fase prima della prenotazione)
if ($metodo === 'GET') {

    $conn = connessioneDb('reader');
    if (!$conn) {
        scriviLog('errore', 'turni GET: connessione al database non riuscita');
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['errore' => 'Errore del database: impossibile caricare i turni.', 'codice' => 'DB_ERROR']);
    } else {
        $profilo = profiloAttivo();
        $utente_id = $profilo ? (int) $profilo['id'] : 0;

        $fasce = [];
        $errore_db = false;

        $adesso_ts = time();
        // Inizio: oggi alle 09:00.
        $inizio_ts = mktime(9, 0, 0, (int) date('n'), (int) date('j'), (int) date('Y'));
        // Fine: tra 3 mesi alle 17:00 (mktime normalizza eventuali sconfini).
        $fine_ts = mktime(17, 0, 0, (int) date('n') + 3, (int) date('j'), (int) date('Y'));

        while ($inizio_ts <= $fine_ts && !$errore_db) {
            // Salta domeniche (w=0) e fasce già passate.
            if ((int) date('w', $inizio_ts) !== 0 && $inizio_ts > $adesso_ts) {
                $iso = date('Y-m-d H:i:s', $inizio_ts);

                $stm = mysqli_prepare(
                    $conn,
                    'SELECT COUNT(*) AS conteggio FROM turni_volontariato WHERE fascia_oraria = ?'
                );

                if (!$stm) {
                    scriviLog('errore', 'turni GET: prepare conteggio fallita - ' . mysqli_error($conn));
                    $errore_db = true;
                } else {
                    mysqli_stmt_bind_param($stm, 's', $iso);
                    mysqli_stmt_execute($stm);
                    mysqli_stmt_bind_result($stm, $conteggio_letto);
                    $conteggio = mysqli_stmt_fetch($stm) ? (int) $conteggio_letto : 0;
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
                            mysqli_stmt_bind_result($controllo, $uno);
                            $gia_iscritto = (mysqli_stmt_fetch($controllo) === true);
                            mysqli_stmt_close($controllo);
                        }
                    }

                    $fasce[] = [
                        'fascia_oraria' => $iso,
                        'etichetta' => date('D d/m/Y H:i', $inizio_ts),
                        'iscritti' => $conteggio,
                        'max' => 2,
                        'piena' => ($conteggio >= 2),
                        'gia_iscritto' => $gia_iscritto,
                    ];
                }
            }

            // Passo di un'ora: 3600 secondi.
            $inizio_ts += 3600;
            // Oltre le 18:00 si salta al giorno successivo alle 09:00.
            if ((int) date('H', $inizio_ts) >= 18) {
                $inizio_ts = mktime(
                    9,
                    0,
                    0,
                    (int) date('n', $inizio_ts),
                    (int) date('j', $inizio_ts) + 1,
                    (int) date('Y', $inizio_ts)
                );
            }
        }

        mysqli_close($conn);

        if ($errore_db) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['errore' => 'Errore del database: impossibile caricare i turni.', 'codice' => 'DB_ERROR']);
        } else {
            echo json_encode(['successo' => true, 'fasce' => $fasce], JSON_UNESCAPED_UNICODE);
        }
    }
    // Effettua una prenotazione
} else if ($metodo === 'POST') {

    $profilo = profiloAttivo();
    if (!$profilo) {
        header('HTTP/1.1 401 Unauthorized');
        echo json_encode(['errore' => "Devi effettuare l'accesso per prenotare turni.", 'codice' => 'UNAUTHORIZED']);
    } else {
        $fasce_grezze = $_POST['fasce'] ?? '';
        $fasce_richieste = [];

        foreach (explode(',', $fasce_grezze) as $f) {
            $f = trim($f);
            if ($f === '') {
                continue;
            }
            $pezzi = explode(' ', $f);
            if (count($pezzi) !== 2) {
                continue;
            }
            $parte_data = explode('-', $pezzi[0]);
            $parte_ora = explode(':', $pezzi[1]);
            if (count($parte_data) !== 3 || count($parte_ora) < 2) {
                continue;
            }
            $ts = mktime(
                (int) $parte_ora[0],
                (int) $parte_ora[1],
                0,
                (int) $parte_data[1],
                (int) $parte_data[2],
                (int) $parte_data[0]
            );
            if ($ts !== false) {
                $fasce_richieste[] = date('Y-m-d H:i:s', $ts);
            }
        }

        if (empty($fasce_richieste)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['errore' => 'Nessuna fascia oraria valida ricevuta.', 'codice' => 'NO_SHIFTS']);
        } else {
            $conn = connessioneDb('modifier');
            if (!$conn) {
                scriviLog('errore', 'turni POST: connessione al database non riuscita');
                header('HTTP/1.1 500 Internal Server Error');
                echo json_encode(['errore' => 'Errore del database durante la prenotazione.', 'codice' => 'DB_ERROR']);
            } else {
                $avvisi_turni = [];
                $inseriti = 0;
                $utente_id = (int) $profilo['id'];
                $errore_db = false;

                foreach ($fasce_richieste as $fascia) {

                    // Limite volontari (massimo 2 per fascia).
                    $stm = mysqli_prepare($conn, 'SELECT COUNT(*) AS c FROM turni_volontariato WHERE fascia_oraria = ?');
                    if (!$stm) {
                        scriviLog('errore', 'turni POST: prepare conteggio fallita - ' . mysqli_error($conn));
                        $errore_db = true;
                        break;
                    }
                    mysqli_stmt_bind_param($stm, 's', $fascia);
                    mysqli_stmt_execute($stm);
                    mysqli_stmt_bind_result($stm, $conteggio_letto);
                    $conteggio = mysqli_stmt_fetch($stm) ? (int) $conteggio_letto : 0;
                    mysqli_stmt_close($stm);

                    if ($conteggio >= 2) {
                        $avvisi_turni[] = [
                            'codice' => 'SHIFT_FULL',
                            'fascia' => $fascia,
                            'msg' => 'Fascia del ' . formattaFascia($fascia) . ' già piena (2/2 volontari).',
                        ];
                        continue;
                    }

                    // L'utente non deve essere già iscritto.
                    $controllo = mysqli_prepare($conn, 'SELECT id FROM turni_volontariato WHERE utente_id = ? AND fascia_oraria = ? LIMIT 1');
                    if (!$controllo) {
                        scriviLog('errore', 'turni POST: prepare controllo iscrizione fallita - ' . mysqli_error($conn));
                        $errore_db = true;
                        break;
                    }
                    mysqli_stmt_bind_param($controllo, 'is', $utente_id, $fascia);
                    mysqli_stmt_execute($controllo);
                    mysqli_stmt_bind_result($controllo, $id_turno_trovato);
                    $gia_presente = (mysqli_stmt_fetch($controllo) === true);
                    mysqli_stmt_close($controllo);

                    if ($gia_presente) {
                        $avvisi_turni[] = [
                            'codice' => 'ALREADY_BOOKED',
                            'fascia' => $fascia,
                            'msg' => 'Sei già iscritto alla fascia del ' . formattaFascia($fascia) . '.',
                        ];
                        continue;
                    }

                    // Inserisce il turno.
                    $inserimento = mysqli_prepare($conn, 'INSERT INTO turni_volontariato (utente_id, fascia_oraria) VALUES (?, ?)');
                    if (!$inserimento) {
                        scriviLog('errore', 'turni POST: prepare inserimento fallita - ' . mysqli_error($conn));
                        $errore_db = true;
                        break;
                    }
                    mysqli_stmt_bind_param($inserimento, 'is', $utente_id, $fascia);
                    if (!mysqli_stmt_execute($inserimento)) {
                        scriviLog('errore', 'turni POST: execute inserimento fallita - ' . mysqli_stmt_error($inserimento));
                        mysqli_stmt_close($inserimento);
                        $errore_db = true;
                        break;
                    }
                    mysqli_stmt_close($inserimento);
                    $inseriti++;
                }

                mysqli_close($conn);

                if ($errore_db) {
                    header('HTTP/1.1 500 Internal Server Error');
                    echo json_encode(['errore' => 'Errore del database durante la prenotazione.', 'codice' => 'DB_ERROR']);
                } else if ($inseriti === 0 && !empty($avvisi_turni)) {
                    header('HTTP/1.1 409 Conflict');
                    echo json_encode([
                        'errore' => 'Nessun turno è stato prenotato.',
                        'codice' => 'ALL_FAILED',
                        'dettagli' => $avvisi_turni,
                    ], JSON_UNESCAPED_UNICODE);
                } else {
                    scriviLog('info', 'turni POST: ' . $inseriti . ' turno/i prenotati da utente ' . $utente_id);
                    echo json_encode([
                        'successo' => true,
                        'inseriti' => $inseriti,
                        'avvisi' => $avvisi_turni,
                        'messaggio' => "Prenotati {$inseriti} turno/i con successo!"
                            . (empty($avvisi_turni) ? '' : ' Alcuni turni non sono stati prenotati (vedi avvisi).'),
                    ], JSON_UNESCAPED_UNICODE);
                }
            }
        }
    }

} else {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['errore' => 'Metodo non consentito', 'codice' => 'METHOD_NOT_ALLOWED']);
}