<?php
/**
 * logout.php — Distrugge sessione e cookie "ricordami" e reindirizza.
 */
declare(strict_types=1);

require_once 'includes/auth.php';

avviaSessione();
logout();

// Reindirizza alla home con messaggio tramite parametro (evita dati sensibili in URL)
header('Location: index.php?azione=logout');
exit;
