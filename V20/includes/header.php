<?php
// Testata comune, inclusa da generaTestata().
require_once __DIR__ . '/sessione.php';

$profilo = profiloAttivo();
$pagina_corrente = basename($_SERVER['PHP_SELF']);

$voci_nav = [
    'index.php' => 'Home',
    'gatti.php' => 'Adotta un gatto',
    'volontariato.php' => 'Volontariato',
];
if ($profilo && (bool) $profilo['is_admin']) {
    $voci_nav['inserisci_gatto.php'] = 'Inserisci gatto';
}
?>

<body>

    <header class="header">

        <a href="index.php" class="brand-logo" aria-label="Torna alla Home Page">
            <img src="img/logo.png" alt="Logo Gattile San Paolo" class="logo-img" width="48" height="48">
            <strong>Gattile San Paolo</strong>
        </a>

        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="menu-principale"
            aria-label="Apri menu di navigazione">
            ☰ Menu
        </button>

        <nav id="menu-principale" aria-label="Navigazione principale">
            <ul>
                <?php foreach ($voci_nav as $href => $etichetta):
                    $attivo = ($href === $pagina_corrente);
                    ?>
                    <li>
                        <a href="<?= $href ?>" class="btn <?= $attivo ? 'active' : '' ?>" <?= $attivo ? 'aria-current="page"' : '' ?>>
                            <?= $etichetta ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <h2 class="sr-solo">Account</h2>

        <button type="button" id="toggle-tema" class="toggle-tema btn" aria-label="Cambia tema (attuale: sistema)"
            title="Cambia tema: Sistema / Chiaro / Scuro">
            <svg class="icona-tema" viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false">
                <circle class="icona-tema-bordo" cx="8" cy="8" r="6" fill="none" stroke="currentColor"
                    stroke-width="2" />
                <path class="icona-tema-meta" d="M8 2a6 6 0 0 1 0 12z" fill="currentColor" />
                <circle class="icona-tema-pieno" cx="8" cy="8" r="6" fill="currentColor" />
            </svg>
            <span class="testo-tema">Tema: sistema</span>
        </button>

        <section class="stato-autenticazione" id="autenticazione" aria-label="Stato autenticazione">
            <?php if ($profilo): ?>
                <p class="utente-info">
                    <svg class="icona-utente" viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"
                        focusable="false">
                        <circle cx="8" cy="5" r="3" fill="currentColor" />
                        <path d="M2 15a6 6 0 0 1 12 0z" fill="currentColor" />
                    </svg>
                    <em class="nome-utente"><?= ripulisci($profilo['username']) ?></em>
                    <?php if ((bool) $profilo['is_admin']): ?>
                        <em class="badge-gatto">Amministratore</em>
                    <?php else: ?>
                        <em class="badge-gatto">Utente</em>
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