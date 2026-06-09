<?php
// Head comune a tutte le pagine: il contenuto è identico ovunque, cambia solo
// titolo e descrizione, già forniti dalle variabili $titolo_pagina e
// $descrizione_pagina impostate da ciascuna pagina prima dell'inclusione.
//
// I fogli di stile condizionati a una media query sono collegati qui con
// l'attributo media del tag <link> (non più via @import nel CSS principale):
//   - movimento-ridotto.css -> (prefers-reduced-motion: reduce)
//   - stampa.css            -> print
// tema-scuro.css resta un foglio normale (senza media) perché contiene anche
// il tema manuale via attributo data-tema, che deve valere sempre.
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?= ripulisci($titolo_pagina) ?></title>
    <meta name="description" content="<?= ripulisci($descrizione_pagina) ?>">
    <meta name="keywords" content="gattile, adozione gatti, volontariato, felini, Torino">
    <meta name="author" content="Gabriele Cancemi">
    <meta name="robots" content="index, follow">
    <meta name="color-scheme" content="light dark">
    <link rel="icon" href="img/logo.png" type="image/png">
    <link rel="stylesheet" href="css/stile.css">
    <link rel="stylesheet" href="css/tema-scuro.css">
    <link rel="stylesheet" href="css/movimento-ridotto.css" media="(prefers-reduced-motion: reduce)">
    <link rel="stylesheet" href="css/stampa.css" media="print">
    <script src="js/tema-iniziale.js"></script>
</head>
