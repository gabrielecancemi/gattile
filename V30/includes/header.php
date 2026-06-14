<?php
// Header comune

require_once __DIR__ . '/sessione.php';

$profilo = profiloAttivo();
// Nome del file corrente (ultima parte del percorso).
$percorso_corrente = $_SERVER['PHP_SELF'] ?? '';
$parti_percorso = explode('/', $percorso_corrente);
$pagina_corrente = $parti_percorso[count($parti_percorso) - 1];

$voci_nav = [
    'index.php' => 'Home',
    'gatti.php' => 'Adozioni',
    'volontariato.php' => 'Volontariato',
];
if ($profilo && (bool) $profilo['is_admin']) {
    $voci_nav['inserisci_gatto.php'] = 'Inserisci gatto';
}

// Icone del menu (SVG)
$icone_nav = [
    'index.php' =>
        '<path d="M8 2 2 7v7h4v-4h4v4h4V7z" fill="currentColor"/>',
    'gatti.php' =>
        '<ellipse cx="8" cy="11" rx="3.6" ry="3" fill="currentColor"/>'
        . '<ellipse cx="3.7" cy="6.6" rx="1.5" ry="2" fill="currentColor"/>'
        . '<ellipse cx="6.3" cy="4.2" rx="1.4" ry="1.9" fill="currentColor"/>'
        . '<ellipse cx="9.7" cy="4.2" rx="1.4" ry="1.9" fill="currentColor"/>'
        . '<ellipse cx="12.3" cy="6.6" rx="1.5" ry="2" fill="currentColor"/>',
    'volontariato.php' =>
        '<path d="M8 14S2 10 2 6a3 3 0 0 1 6-1 3 3 0 0 1 6 1c0 4-6 8-6 8z" fill="currentColor"/>',
    'inserisci_gatto.php' =>
        '<rect x="7" y="2" width="2" height="12" fill="currentColor"/>'
        . '<rect x="2" y="7" width="12" height="2" fill="currentColor"/>',
];
?>

<body>

    <header class="header">
        <!-- logo -->
        <a href="index.php" class="brand-logo" aria-label="Torna alla Home Page">
            <picture class="logo-container">
                <source srcset="img/logo_grande.png" media="(min-width: 600px)">
                <img src="img/logo_piccolo.png" alt="Logo Gattile San Paolo" class="logo-img">
            </picture>
            <strong>Gattile San Paolo</strong>
        </a>

        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="menu-principale"
            aria-label="Apri menu di navigazione">
            ☰ Menu
        </button>

        <!-- pagine -->
        <nav id="menu-principale" aria-label="Navigazione principale">
            <ul>
                <?php foreach ($voci_nav as $href => $etichetta):
                    $attivo = ($href === $pagina_corrente);
                    ?>
                    <li>
                        <a href="<?= $href ?>" class="btn <?= $attivo ? 'active' : '' ?>" <?= $attivo ? 'aria-current="page"' : '' ?>>
                            <svg class="icona-menu" viewBox="0 0 16 16" aria-hidden="true"
                                focusable="false"><?= $icone_nav[$href] ?? '' ?></svg>
                            <?= $etichetta ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <!-- tema -->
        <button type="button" id="toggle-tema" class="toggle-tema btn" aria-label="Cambia tema (attuale: sistema)"
            title="Cambia tema: Sistema / Chiaro / Scuro">
            <svg class="icona-tema" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                <circle class="icona-tema-bordo" cx="8" cy="8" r="6" fill="none" stroke="currentColor"
                    stroke-width="2" />
                <path class="icona-tema-meta" d="M8 2a6 6 0 0 1 0 12z" fill="currentColor" />
                <circle class="icona-tema-pieno" cx="8" cy="8" r="6" fill="currentColor" />
            </svg>
            <em class="testo-tema">Tema: sistema</em>
        </button>

        <!-- account -->
        <section class="stato-autenticazione" id="autenticazione" aria-label="Stato autenticazione">
            <h2 class="sr-solo">Account</h2>
            <?php if ($profilo): ?>
                <p class="utente-info">
                    <svg class="icona-utente" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                        <circle cx="8" cy="5" r="3" fill="currentColor" />
                        <path d="M2 15a6 6 0 0 1 12 0z" fill="currentColor" />
                    </svg>
                    <em class="nome-utente"><?= ripulisci($profilo['username']) ?></em>
                    <?php if ((bool) $profilo['is_admin']): ?>
                        <em class="badge-utente">Amministratore</em>
                    <?php else: ?>
                        <em class="badge-utente">Utente</em>
                    <?php endif; ?>
                </p>
                <p class="account-azioni">
                    <a href="logout.php" class="btn btn-logout">Esci</a>
                </p>
            <?php else: ?>
                <p class="utente-info">
                    <em>non loggato</em>
                </p>
                <p class="account-azioni">
                    <a href="login.php" class="btn btn-login<?= $pagina_corrente === 'login.php' ? ' active' : '' ?>">
                        Accedi
                    </a>
                    <a href="registrazione.php"
                        class="btn btn-signin<?= $pagina_corrente === 'registrazione.php' ? ' active' : '' ?>">
                        Registrati
                    </a>
                </p>
            <?php endif; ?>
        </section>

    </header>

    <script src="js/menu.js" defer></script>