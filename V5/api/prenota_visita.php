<?php
/**
 * api/prenota_visita.php — Endpoint prenotazione visita conoscitiva.
 * Usa utente "modifier" (INSERT + SELECT).
 */
declare(strict_types=1);

require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

avviaSessione();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['errore' => 'Metodo non consentito', 'codice' => 'METHOD_NOT_ALLOWED']);
    exit;
}

$utente = utenteLoggato();
if (!$utente) {
    http_response_code(401);
    echo json_encode(['errore' => "Devi effettuare l'accesso per prenotare una visita.", 'codice' => 'UNAUTHORIZED']);
    exit;
}

if ((bool)$utente['is_admin']) {
    http_response_code(403);
    echo json_encode(['errore' => 'Gli amministratori non possono prenotare visite.', 'codice' => 'FORBIDDEN']);
    exit;
}

$dataOra  = filter_input(INPUT_POST, 'data_ora',  FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$gattiIds = filter_input(INPUT_POST, 'gatti_ids', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

if (empty($dataOra)) {
    http_response_code(400);
    echo json_encode(['errore' => 'Devi specificare data e ora della visita.', 'codice' => 'MISSING_DATE']);
    exit;
}

$dt = DateTime::createFromFormat('Y-m-d\TH:i', $dataOra)
   ?: DateTime::createFromFormat('Y-m-d H:i:s', $dataOra)
   ?: DateTime::createFromFormat('Y-m-d H:i',   $dataOra);

if (!$dt || $dt < new DateTime()) {
    http_response_code(400);
    echo json_encode(['errore' => 'La data e ora della visita non è valida o è nel passato.', 'codice' => 'INVALID_DATE']);
    exit;
}

$idsArray = [];
if (!empty($gattiIds)) {
    foreach (explode(',', $gattiIds) as $raw) {
        $id = filter_var(trim($raw), FILTER_VALIDATE_INT);
        if ($id && $id > 0) $idsArray[] = $id;
    }
}

if (empty($idsArray)) {
    http_response_code(400);
    echo json_encode(['errore' => 'Seleziona almeno un gatto per la visita.', 'codice' => 'NO_CATS']);
    exit;
}

$conn = null;
try {
    $conn = getDB('modifier');

    // Avvia transazione
    mysqli_begin_transaction($conn);

    // Inserisce prenotazione
    $ins = mysqli_prepare($conn, 'INSERT INTO prenotazioni_visite (utente_id, data_ora) VALUES (?, ?)');
    if (!$ins) throw new RuntimeException(mysqli_error($conn));

    $utenteId   = (int)$utente['id'];
    $dataOraStr = $dt->format('Y-m-d H:i:s');
    mysqli_stmt_bind_param($ins, 'is', $utenteId, $dataOraStr);
    mysqli_stmt_execute($ins);
    mysqli_stmt_close($ins);

    $prenotazioneId = (int)mysqli_insert_id($conn);

    // Verifica gatti e inserisce collegamenti
    foreach ($idsArray as $gattoId) {
        $check = mysqli_prepare($conn, 'SELECT id FROM gatti WHERE id = ? LIMIT 1');
        if (!$check) throw new RuntimeException(mysqli_error($conn));
        mysqli_stmt_bind_param($check, 'i', $gattoId);
        mysqli_stmt_execute($check);
        $res    = mysqli_stmt_get_result($check);
        $exists = mysqli_fetch_assoc($res);
        mysqli_stmt_close($check);

        if (!$exists) {
            mysqli_rollback($conn);
            mysqli_close($conn);
            http_response_code(400);
            echo json_encode(['errore' => "Il gatto con ID {$gattoId} non esiste.", 'codice' => 'CAT_NOT_FOUND']);
            exit;
        }

        $insG = mysqli_prepare($conn, 'INSERT INTO visita_gatti (prenotazione_id, gatto_id) VALUES (?, ?)');
        if (!$insG) throw new RuntimeException(mysqli_error($conn));
        mysqli_stmt_bind_param($insG, 'ii', $prenotazioneId, $gattoId);
        mysqli_stmt_execute($insG);
        mysqli_stmt_close($insG);
    }

    mysqli_commit($conn);
    mysqli_close($conn);

    echo json_encode([
        'successo'        => true,
        'messaggio'       => 'Visita prenotata con successo per il ' . $dt->format('d/m/Y') . ' alle ' . $dt->format('H:i') . '!',
        'prenotazione_id' => $prenotazioneId,
    ], JSON_UNESCAPED_UNICODE);

} catch (RuntimeException $e) {
    if ($conn) { mysqli_rollback($conn); mysqli_close($conn); }
    error_log('Errore DB prenota_visita.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['errore' => 'Errore del database durante la prenotazione. Riprova tra qualche minuto.', 'codice' => 'DB_ERROR']);
}
