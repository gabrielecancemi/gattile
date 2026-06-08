<?php
// Distrugge la sessione e torna alla home.
declare(strict_types=1);

require_once 'includes/sessione.php';

aprireSessione();
chiudiProfilo();

// Messaggio passato come parametro per evitare dati sensibili in URL.
header('Location: index.php?azione=logout');
exit;
