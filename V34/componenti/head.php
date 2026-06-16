<?php
// Head comune a tutte le pagine
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
    <link rel="icon" href="img/logo_piccolo.png" type="image/png">
    <link rel="stylesheet" href="css/stile.css">
    <link rel="stylesheet" href="css/tema-scuro.css">
    <link rel="stylesheet" href="css/movimento-ridotto.css" media="(prefers-reduced-motion: reduce)">
    <link rel="stylesheet" href="css/stampa.css" media="print">
    <script src="js/gestione_tema.js"></script>
    <script src="js/funzioni_comuni.js" defer></script>
    <script src="js/lingua.js" defer></script>
</head>