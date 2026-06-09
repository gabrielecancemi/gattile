<?php
// Utility condivise per l'output. La generazione di head, testata, apertura e
// chiusura del main e piè di pagina è scritta direttamente nelle pagine.

require_once 'sessione.php';

// Escape HTML sicuro per ogni valore stampato a video.
function ripulisci(mixed $valore): string
{
    return htmlspecialchars((string) $valore, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Messaggio per l'utente: 'errore' | 'successo' | 'avviso'.
function avvisoUtente(string $testo, string $tipo = 'errore'): string
{
    $etichette = ['errore' => 'Errore', 'successo' => 'OK', 'avviso' => 'Info'];
    $etichetta = $etichette[$tipo] ?? 'Nota';
    $ruolo = ($tipo === 'errore') ? 'alert' : 'status';
    $cls = "messaggio messaggio-{$tipo}";
    $testo_pulito = ripulisci($testo);
    return "<output class=\"{$cls}\" role=\"{$ruolo}\" aria-live=\"assertive\">"
        . "<strong class=\"messaggio-tag\" aria-hidden=\"true\">{$etichetta}</strong> {$testo_pulito}</output>";
}
