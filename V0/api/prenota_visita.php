<?php
/**
 * api/prenota_visita.php — Endpoint per prenotazione visita conoscitiva.
 * Usa utente "modifier" (INSERT + SELECT).
 * Risponde in JSON.
 */
declare(strict_types=1);

require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

avviaSessione();

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['errore' => 'Metodo non consentito', 'codice' => 'METHOD_NOT_ALLOWED']);
    exit;
}

// Richiede autenticazione
$utente = utenteLoggato();
if (!$utente) {
    http_response_code(401);
    echo json_encode(['errore' => 'Devi effettuare l\'accesso per prenotare una visita.', 'codice' => 'UNAUTHORIZED']);
    exit;
}

// Ammessi solo utenti normali (non admin)
if ((bool)$utente['is_admin']) {
    http_response_code(403);
    echo json_encode(['errore' => 'Gli amministratori non possono prenotare visite.', 'codice' => 'FORBIDDEN']);
    exit;
}

// Recupera e valida i dati
$dataOra  = filter_input(INPUT_POST, 'data_ora',   FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$gattiIds = filter_input(INPUT_POST, 'gatti_ids',  FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

if (empty($dataOra)) {
    http_response_code(400);
    echo json_encode(['errore' => 'Devi specificare data e ora della visita.', 'codice' => 'MISSING_DATE']);
    exit;
}

// Valida formato datetime
$dt = DateTime::createFromFormat('Y-m-d\TH:i', $dataOra)
   ?: DateTime::createFromFormat('Y-m-d H:i:s', $dataOra)
   ?: DateTime::createFromFormat('Y-m-d H:i',   $dataOra);

if (!$dt || $dt < new DateTime()) {
    http_response_code(400);
    echo json_encode(['errore' => 'La data e ora della visita non è valida o è nel passato.', 'codice' => 'INVALID_DATE']);
    exit;
}

// Valida ID gatti (array di interi)
$idsArray = [];
if (!empty($gattiIds)) {
    foreach (explode(',', $gattiIds) as $raw) {
        $id = filter_var(trim($raw), FILTER_VALIDATE_INT);
        if ($id && $id > 0) {
            $idsArray[] = $id;
        }
    }
}

if (empty($idsArray)) {
    http_response_code(400);
    echo json_encode(['errore' => 'Seleziona almeno un gatto per la visita.', 'codice' => 'NO_CATS']);
    exit;
}

try {
    $db = getDB('modifier');
    $db->beginTransaction();

    // Inserisce la prenotazione
    $ins = $db->prepare(
        'INSERT INTO prenotazioni_visite (utente_id, data_ora) VALUES (?, ?)'
    );
    $ins->execute([(int)$utente['id'], $dt->format('Y-m-d H:i:s')]);
    $prenotazioneId = (int)$db->lastInsertId();

    // Verifica che i gatti esistano e inserisce i collegamenti
    $dbReader = getDB('reader');
    foreach ($idsArray as $gattoId) {
        $check = $dbReader->prepare('SELECT id FROM gatti WHERE id = ? LIMIT 1');
        $check->execute([$gattoId]);
        if (!$check->fetch()) {
            $db->rollBack();
            http_response_code(400);
            echo json_encode(['errore' => "Il gatto con ID {$gattoId} non esiste.", 'codice' => 'CAT_NOT_FOUND']);
            exit;
        }

        $insG = $db->prepare('INSERT INTO visita_gatti (prenotazione_id, gatto_id) VALUES (?, ?)');
        $insG->execute([$prenotazioneId, $gattoId]);
    }

    $db->commit();

    echo json_encode([
        'successo'       => true,
        'messaggio'      => 'Visita prenotata con successo per il ' . $dt->format('d/m/Y') . ' alle ' . $dt->format('H:i') . '!',
        'prenotazione_id'=> $prenotazioneId,
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Errore DB prenota_visita.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['errore' => 'Errore del database durante la prenotazione. Riprova tra qualche minuto.', 'codice' => 'DB_ERROR']);
}
