<?php
// Distrugge la sessione e torna alla home.

require_once 'sessione.php';

aprireSessione();
chiudiProfilo();

header('Location: ../index.php?azione=logout');

