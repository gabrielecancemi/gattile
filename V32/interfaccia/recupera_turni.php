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

        $adesso_ts = time();
        // Inizio: oggi alle 09:00.
        $inizio_ts = mktime(9, 0, 0, (int) date('n'), (int) date('j'), (int) date('Y'));
        // Fine: tra 3 mesi alle 17:00.
        $fine_ts = mktime(17, 0, 0, (int) date('n') + 3, (int) date('j'), (int) date('Y'));
        $inizio_iso = date('Y-m-d H:i:s', $inizio_ts);
        $fine_iso = date('Y-m-d H:i:s', $fine_ts);

        $errore_db = false;

        // conteggi per fascia nel range
        $conteggi = [];
        $stm = mysqli_prepare(
            $conn,
            'SELECT fascia_oraria, COUNT(*) AS iscritti
             FROM turni_volontariato
             WHERE fascia_oraria BETWEEN ? AND ?
             GROUP BY fascia_oraria'
        );
        if (!$stm) {
            scriviLog('errore', 'turni GET: prepare conteggi fallita - ' . mysqli_error($conn));
            $errore_db = true;
        } else {
            mysqli_stmt_bind_param($stm, 'ss', $inizio_iso, $fine_iso);
            mysqli_stmt_execute($stm);
            $col_fascia = '';
            $col_iscritti = 0;
            mysqli_stmt_bind_result($stm, $col_fascia, $col_iscritti);
            while (mysqli_stmt_fetch($stm)) {
                $conteggi[$col_fascia] = (int) $col_iscritti;
            }
            mysqli_stmt_close($stm);
        }

        // fasce già prenotate dall'utente nel range
        $iscrizioni = [];
        if (!$errore_db && $utente_id > 0) {
            $stm2 = mysqli_prepare(
                $conn,
                'SELECT fascia_oraria
                 FROM turni_volontariato
                 WHERE utente_id = ? AND fascia_oraria BETWEEN ? AND ?'
            );
            if (!$stm2) {
                scriviLog('errore', 'turni GET: prepare iscrizioni fallita - ' . mysqli_error($conn));
                $errore_db = true;
            } else {
                mysqli_stmt_bind_param($stm2, 'iss', $utente_id, $inizio_iso, $fine_iso);
                mysqli_stmt_execute($stm2);
                $col_fascia2 = '';
                mysqli_stmt_bind_result($stm2, $col_fascia2);
                while (mysqli_stmt_fetch($stm2)) {
                    $iscrizioni[$col_fascia2] = true;
                }
                mysqli_stmt_close($stm2);
            }
        }

        mysqli_close($conn);

        if ($errore_db) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['errore' => 'Errore del database: impossibile caricare i turni.', 'codice' => 'DB_ERROR']);
        } else {
            // Generazione fasce
            $fasce = [];
            $cur = $inizio_ts;
            while ($cur <= $fine_ts) {
                if ((int) date('w', $cur) !== 0 && $cur > $adesso_ts) {
                    $iso = date('Y-m-d H:i:s', $cur);
                    $conteggio = $conteggi[$iso] ?? 0;

                    $fasce[] = [
                        'fascia_oraria' => $iso,
                        'etichetta' => date('D d/m/Y H:i', $cur),
                        'iscritti' => $conteggio,
                        'max' => 2,
                        'piena' => ($conteggio >= 2),
                        'gia_iscritto' => isset($iscrizioni[$iso]),
                    ];
                }

                // Passo di un'ora: 3600 secondi.
                $cur += 3600;
                // Oltre le 18:00 si salta al giorno successivo alle 09:00.
                if ((int) date('H', $cur) >= 18) {
                    $cur = mktime(
                        9,
                        0,
                        0,
                        (int) date('n', $cur),
                        (int) date('j', $cur) + 1,
                        (int) date('Y', $cur)
                    );
                }
            }

            echo json_encode(['successo' => true, 'fasce' => $fasce], JSON_UNESCAPED_UNICODE);
        }
    }

    // Effettua una prenotazione
} elseif ($metodo === 'POST') {

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
            $parte_data = explode('-', $pezzi[0] ?? '');
            $parte_ora = explode(':', $pezzi[1] ?? '');
            if (count($pezzi) !== 2 || count($parte_data) !== 3 || count($parte_ora) < 2) {
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
                echo json_encode(['errore' => 'Errore del database durante la prenotazione. Riprova tra qualche minuto', 'codice' => 'DB_ERROR']);
            } else {
                $utente_id = (int) $profilo['id'];
                $avvisi_turni = [];
                $errore_db = false;
                $inseriti = 0;

                // conteggi + flag iscrizione
                $n = count($fasce_richieste);
                $placeholders = implode(',', array_fill(0, $n, '?'));
                $tipi = 'i' . str_repeat('s', $n);

                $stm = mysqli_prepare(
                    $conn,
                    "SELECT fascia_oraria,
                            COUNT(*) AS iscritti,
                            SUM(utente_id = ?) AS gia_iscritto
                     FROM turni_volontariato
                     WHERE fascia_oraria IN ($placeholders)
                     GROUP BY fascia_oraria"
                );

                if (!$stm) {
                    scriviLog('errore', 'turni POST: prepare stato fasce fallita - ' . mysqli_error($conn));
                    $errore_db = true;
                } else {
                    mysqli_stmt_bind_param($stm, $tipi, $utente_id, ...$fasce_richieste);
                    mysqli_stmt_execute($stm);
                    $col_fascia = '';
                    $col_iscritti = 0;
                    $col_gia_iscr = 0;
                    mysqli_stmt_bind_result($stm, $col_fascia, $col_iscritti, $col_gia_iscr);
                    $stato = [];
                    while (mysqli_stmt_fetch($stm)) {
                        $stato[$col_fascia] = [
                            'iscritti' => $col_iscritti,
                            'gia_iscritto' => $col_gia_iscr,
                        ];
                    }
                    mysqli_stmt_close($stm);

                    //  Classificazione fasce
                    $da_inserire = [];
                    foreach ($fasce_richieste as $fascia) {
                        $info = $stato[$fascia] ?? ['iscritti' => 0, 'gia_iscritto' => 0];
                        if ((int) $info['iscritti'] >= 2) {
                            $avvisi_turni[] = [
                                'codice' => 'SHIFT_FULL',
                                'fascia' => $fascia,
                                'msg' => 'Fascia del ' . formattaFascia($fascia) . ' già piena (2/2 volontari).',
                            ];
                        } elseif ((int) $info['gia_iscritto'] > 0) {
                            $avvisi_turni[] = [
                                'codice' => 'ALREADY_BOOKED',
                                'fascia' => $fascia,
                                'msg' => 'Sei già iscritto alla fascia del ' . formattaFascia($fascia) . '.',
                            ];
                        } else {
                            $da_inserire[] = $fascia;
                        }
                    }

                    //  Insert multiplo
                    if (!empty($da_inserire)) {
                        mysqli_begin_transaction($conn);
                        $stm_ins = mysqli_prepare(
                            $conn,
                            'INSERT INTO turni_volontariato (utente_id, fascia_oraria) VALUES (?, ?)'
                        );
                        if (!$stm_ins) {
                            scriviLog('errore', 'turni POST: prepare inserimento fallita - ' . mysqli_error($conn));
                            mysqli_rollback($conn);
                            $errore_db = true;
                        } else {
                            foreach ($da_inserire as $fascia) {
                                mysqli_stmt_bind_param($stm_ins, 'is', $utente_id, $fascia);
                                if (!mysqli_stmt_execute($stm_ins)) {
                                    scriviLog('errore', 'turni POST: execute inserimento fallita - ' . mysqli_stmt_error($stm_ins));
                                    $errore_db = true;
                                    break;
                                }
                            }
                            mysqli_stmt_close($stm_ins);

                            if ($errore_db) {
                                mysqli_rollback($conn);
                            } else {
                                mysqli_commit($conn);
                                $inseriti = count($da_inserire);
                            }
                        }
                    }
                }

                mysqli_close($conn);

                if ($errore_db) {
                    header('HTTP/1.1 500 Internal Server Error');
                    echo json_encode(['errore' => 'Errore del database durante la prenotazione. Riprova tra qualche minuto.', 'codice' => 'DB_ERROR']);
                } elseif ($inseriti === 0 && !empty($avvisi_turni)) {
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