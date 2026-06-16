<?php
// Prenota una visita, inserendola nel db

require_once '../componenti/gestione_sessione.php';
require_once '../componenti/connessione_db.php';
require_once '../componenti/gestione_log.php';

header('Content-Type: application/json; charset=utf-8');

aprireSessione();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['errore' => 'Metodo non consentito', 'codice' => 'METHOD_NOT_ALLOWED']);
} else {
    $profilo = profiloAttivo();
    if (!$profilo) {
        header('HTTP/1.1 401 Unauthorized');
        echo json_encode(['errore' => "Devi effettuare l'accesso per prenotare una visita.", 'codice' => 'UNAUTHORIZED']);
    } else if ((bool) $profilo['is_admin']) {
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['errore' => 'Gli amministratori non possono prenotare visite.', 'codice' => 'FORBIDDEN']);
    } else {
        $data_ora = filter_input(INPUT_POST, 'data_ora', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
        $gatti_ids = filter_input(INPUT_POST, 'gatti_ids', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

        $timestamp = false;
        if (!empty($data_ora)) {
            $pezzi = explode('T', trim($data_ora));
            if (count($pezzi) === 2) {
                $parte_data = explode('-', $pezzi[0]);
                $parte_ora = explode(':', $pezzi[1]);
                if (count($parte_data) === 3 && count($parte_ora) >= 2) {
                    $anno = (int) $parte_data[0];
                    $mese = (int) $parte_data[1];
                    $giorno = (int) $parte_data[2];
                    $ora_v = (int) $parte_ora[0];
                    $minuto = (int) $parte_ora[1];
                    $timestamp = mktime($ora_v, $minuto, 0, $mese, $giorno, $anno);
                }
            }
        }

        // Raccoglie e valida gli id dei gatti selezionati.
        $lista_id = [];
        if (!empty($gatti_ids)) {
            foreach (explode(',', $gatti_ids) as $grezzo) {
                $id = filter_var(trim($grezzo), FILTER_VALIDATE_INT);
                if ($id && $id > 0) {
                    $lista_id[] = $id;
                }
            }
        }

        $ora = $timestamp !== false ? (int) date('H', $timestamp) : 0;

        if (empty($data_ora)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['errore' => 'Devi specificare data e ora della visita.', 'codice' => 'MISSING_DATE']);
        } else if ($timestamp === false || $timestamp < time()) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['errore' => 'La data e ora della visita non è valida o è nel passato.', 'codice' => 'INVALID_DATE']);
        } else if ($ora < 9 || $ora >= 18) {
            // La struttura riceve visite dalle 9:00 alle 18:00.
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['errore' => 'Le visite sono possibili solo dalle 9:00 alle 18:00.', 'codice' => 'OUT_OF_HOURS']);
        } else if (empty($lista_id)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['errore' => 'Seleziona almeno un gatto per la visita.', 'codice' => 'NO_CATS']);
        } else {
            $conn = connessioneDb('modifier');
            if (!$conn) {
                scriviLog('errore', 'prenota_visita: connessione al database non riuscita');
                header('HTTP/1.1 500 Internal Server Error');
                echo json_encode(['errore' => 'Errore del database durante la prenotazione. Riprova tra qualche minuto.', 'codice' => 'DB_ERROR']);
            } else {
                // Per garantire la coerenza dei dati si verifica PRIMA l'esistenza di TUTTI i gatti scelti
                $errore_db = false;
                $gatto_mancante = 0;

                foreach ($lista_id as $gatto_id) {
                    $controllo = mysqli_prepare($conn, 'SELECT id FROM gatti WHERE id = ? LIMIT 1');
                    if (!$controllo) {
                        scriviLog('errore', 'prenota_visita: prepare controllo gatto fallita - ' . mysqli_error($conn));
                        $errore_db = true;
                        break;
                    }
                    mysqli_stmt_bind_param($controllo, 'i', $gatto_id);
                    mysqli_stmt_execute($controllo);
                    mysqli_stmt_bind_result($controllo, $id_gatto_trovato);
                    $esiste = mysqli_stmt_fetch($controllo);
                    mysqli_stmt_close($controllo);
                    if (!$esiste) {
                        $gatto_mancante = $gatto_id;
                        break;
                    }
                }

                if ($errore_db) {
                    mysqli_close($conn);
                    header('HTTP/1.1 500 Internal Server Error');
                    echo json_encode(['errore' => 'Errore del database durante la prenotazione. Riprova tra qualche minuto.', 'codice' => 'DB_ERROR']);
                } else if ($gatto_mancante > 0) {
                    mysqli_close($conn);
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode(['errore' => "Il gatto con ID {$gatto_mancante} non esiste.", 'codice' => 'CAT_NOT_FOUND']);
                } else {
                    // Inserisce la prenotazione.
                    $inserimento = mysqli_prepare($conn, 'INSERT INTO prenotazioni_visite (utente_id, data_ora) VALUES (?, ?)');
                    if (!$inserimento) {
                        scriviLog('errore', 'prenota_visita: prepare inserimento fallita - ' . mysqli_error($conn));
                        mysqli_close($conn);
                        header('HTTP/1.1 500 Internal Server Error');
                        echo json_encode(['errore' => 'Errore del database durante la prenotazione. Riprova tra qualche minuto.', 'codice' => 'DB_ERROR']);
                    } else {
                        $utente_id = (int) $profilo['id'];
                        $data_ora_str = date('Y-m-d H:i:s', $timestamp);
                        mysqli_stmt_bind_param($inserimento, 'is', $utente_id, $data_ora_str);

                        if (!mysqli_stmt_execute($inserimento)) {
                            scriviLog('errore', 'prenota_visita: execute inserimento fallita - ' . mysqli_stmt_error($inserimento));
                            mysqli_stmt_close($inserimento);
                            mysqli_close($conn);
                            header('HTTP/1.1 500 Internal Server Error');
                            echo json_encode(['errore' => 'Errore del database durante la prenotazione. Riprova tra qualche minuto.', 'codice' => 'DB_ERROR']);
                        } else {
                            mysqli_stmt_close($inserimento);
                            $prenotazione_id = (int) mysqli_insert_id($conn);

                            // Collega ciascun gatto (già verificato) alla prenotazione.
                            $errore_collegamento = false;
                            foreach ($lista_id as $gatto_id) {
                                $collega = mysqli_prepare($conn, 'INSERT INTO visita_gatti (prenotazione_id, gatto_id) VALUES (?, ?)');
                                if (!$collega) {
                                    scriviLog('errore', 'prenota_visita: prepare collegamento fallita - ' . mysqli_error($conn));
                                    $errore_collegamento = true;
                                    break;
                                }
                                mysqli_stmt_bind_param($collega, 'ii', $prenotazione_id, $gatto_id);
                                if (!mysqli_stmt_execute($collega)) {
                                    scriviLog('errore', 'prenota_visita: execute collegamento fallita - ' . mysqli_stmt_error($collega));
                                    mysqli_stmt_close($collega);
                                    $errore_collegamento = true;
                                    break;
                                }
                                mysqli_stmt_close($collega);
                            }

                            mysqli_close($conn);

                            if ($errore_collegamento) {
                                header('HTTP/1.1 500 Internal Server Error');
                                echo json_encode(['errore' => 'Errore del database durante la prenotazione. Riprova tra qualche minuto.', 'codice' => 'DB_ERROR']);
                            } else {
                                scriviLog('info', 'prenota_visita: visita ' . $prenotazione_id . ' prenotata da utente ' . $utente_id);
                                echo json_encode([
                                    'successo' => true,
                                    'messaggio' => 'Visita prenotata con successo per il ' . date('d/m/Y', $timestamp) . ' alle ' . date('H:i', $timestamp) . '!',
                                    'prenotazione_id' => $prenotazione_id,
                                ], JSON_UNESCAPED_UNICODE);
                            }
                        }
                    }
                }
            }
        }
    }
}