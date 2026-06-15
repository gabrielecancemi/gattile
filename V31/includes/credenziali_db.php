<?php
// Credenziali di accesso al database, questo file va protetto

return [
    'host' => 'localhost',
    'db' => 'gattile_db',
    'charset' => 'utf8mb4',
    'utenti' => [
        'reader' => ['user' => 'lecture', 'pass' => 'P@ssw0rd!'],
        'modifier' => ['user' => 'modifier', 'pass' => 'Str0ng#Admin9'],
        'registrator' => ['user' => 'registrator', 'pass' => 'ToB31nsert?'],
    ],
];
