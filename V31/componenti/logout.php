<?php
// Distrugge la sessione e torna alla home.

require_once 'gestione_sessione.php';

aprireSessione();
chiudiProfilo();

header('Location: ../home.php?azione=logout');

