<?php
// Prenota una visita, inserendola nel db

declare(strict_types=1);

require_once '../includes/sessione.php';
require_once '../includes/connessione_db.php';

// Sicurezza
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

aprireSessione();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['errore' => 'Metodo non consentito', 'codice' => 'METHOD_NOT_ALLOWED']);
    exit;
}

$profilo = profiloAttivo();
if (!$profilo) {
    http_response_code(401);
    echo json_encode(['errore' => "Devi effettuare l'accesso per prenotare una visita.", 'codice' => 'UNAUTHORIZED']);
    exit;
}

if ((bool) $profilo['is_admin']) {
    http_response_code(403);
    echo json_encode(['errore' => 'Gli amministratori non possono prenotare visite.', 'codice' => 'FORBIDDEN']);
    exit;
}

$data_ora = filter_input(INPUT_POST, 'data_ora', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$gatti_ids = filter_input(INPUT_POST, 'gatti_ids', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

if (empty($data_ora)) {
    http_response_code(400);
    echo json_encode(['errore' => 'Devi specificare data e ora della visita.', 'codice' => 'MISSING_DATE']);
    exit;
}

$timestamp = strtotime($data_ora);
$dt = $timestamp ? (new DateTime())->setTimestamp($timestamp) : false;

if (!$dt || $dt < new DateTime()) {
    http_response_code(400);
    echo json_encode(['errore' => 'La data e ora della visita non è valida o è nel passato.', 'codice' => 'INVALID_DATE']);
    exit;
}

// La struttura riceve visite dalle 9:00 alle 18:00.
$ora = (int) $dt->format('H');
if ($ora < 9 || $ora >= 18) {
    http_response_code(400);
    echo json_encode(['errore' => 'Le visite sono possibili solo dalle 9:00 alle 18:00.', 'codice' => 'OUT_OF_HOURS']);
    exit;
}

$lista_id = [];
if (!empty($gatti_ids)) {
    foreach (explode(',', $gatti_ids) as $grezzo) {
        $id = filter_var(trim($grezzo), FILTER_VALIDATE_INT);
        if ($id && $id > 0)
            $lista_id[] = $id;
    }
}

if (empty($lista_id)) {
    http_response_code(400);
    echo json_encode(['errore' => 'Seleziona almeno un gatto per la visita.', 'codice' => 'NO_CATS']);
    exit;
}

$conn = connessioneDb('modifier');
if (!$conn) {
    http_response_code(500);
    echo json_encode(['errore' => 'Errore del database durante la prenotazione. Riprova tra qualche minuto.', 'codice' => 'DB_ERROR']);
    exit;
}

mysqli_begin_transaction($conn);

// Inserisce la prenotazione.
$inserimento = mysqli_prepare($conn, 'INSERT INTO prenotazioni_visite (utente_id, data_ora) VALUES (?, ?)');
if (!$inserimento) {
    error_log('[prenota_visita] prepare: ' . mysqli_error($conn));
    // Annulla modifiche già apportate al db
    mysqli_rollback($conn);
    mysqli_close($conn);
    http_response_code(500);
    echo json_encode(['errore' => 'Errore del database durante la prenotazione. Riprova tra qualche minuto.', 'codice' => 'DB_ERROR']);
    exit;
}

$utente_id = (int) $profilo['id'];
$data_ora_str = $dt->format('Y-m-d H:i:s');
mysqli_stmt_bind_param($inserimento, 'is', $utente_id, $data_ora_str);

if (!mysqli_stmt_execute($inserimento)) {
    error_log('[prenota_visita] execute insert: ' . mysqli_stmt_error($inserimento));
    mysqli_stmt_close($inserimento);
    mysqli_rollback($conn);
    mysqli_close($conn);
    http_response_code(500);
    echo json_encode(['errore' => 'Errore del database durante la prenotazione. Riprova tra qualche minuto.', 'codice' => 'DB_ERROR']);
    exit;
}

mysqli_stmt_close($inserimento);
$prenotazione_id = (int) mysqli_insert_id($conn);

// Verifica i gatti e collega ciascuno alla prenotazione.
foreach ($lista_id as $gatto_id) {
    $controllo = mysqli_prepare($conn, 'SELECT id FROM gatti WHERE id = ? LIMIT 1');
    if (!$controllo) {
        mysqli_rollback($conn);
        mysqli_close($conn);
        http_response_code(500);
        echo json_encode(['errore' => 'Errore del database durante la prenotazione. Riprova tra qualche minuto.', 'codice' => 'DB_ERROR']);
        exit;
    }
    mysqli_stmt_bind_param($controllo, 'i', $gatto_id);
    mysqli_stmt_execute($controllo);
    $risultato = mysqli_stmt_get_result($controllo);
    $esiste = mysqli_fetch_assoc($risultato);
    mysqli_stmt_close($controllo);

    if (!$esiste) {
        mysqli_rollback($conn);
        mysqli_close($conn);
        http_response_code(400);
        echo json_encode(['errore' => "Il gatto con ID {$gatto_id} non esiste.", 'codice' => 'CAT_NOT_FOUND']);
        exit;
    }

    $collega = mysqli_prepare($conn, 'INSERT INTO visita_gatti (prenotazione_id, gatto_id) VALUES (?, ?)');
    if (!$collega) {
        mysqli_rollback($conn);
        mysqli_close($conn);
        http_response_code(500);
        echo json_encode(['errore' => 'Errore del database durante la prenotazione. Riprova tra qualche minuto.', 'codice' => 'DB_ERROR']);
        exit;
    }
    mysqli_stmt_bind_param($collega, 'ii', $prenotazione_id, $gatto_id);
    if (!mysqli_stmt_execute($collega)) {
        mysqli_stmt_close($collega);
        mysqli_rollback($conn);
        mysqli_close($conn);
        http_response_code(500);
        echo json_encode(['errore' => 'Errore del database durante la prenotazione. Riprova tra qualche minuto.', 'codice' => 'DB_ERROR']);
        exit;
    }
    mysqli_stmt_close($collega);
}

mysqli_commit($conn);
mysqli_close($conn);

echo json_encode([
    'successo' => true,
    'messaggio' => 'Visita prenotata con successo per il ' . $dt->format('d/m/Y') . ' alle ' . $dt->format('H:i') . '!',
    'prenotazione_id' => $prenotazione_id,
], JSON_UNESCAPED_UNICODE);
