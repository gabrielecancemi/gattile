<?php
/*
 * header.php — Header comune.
 * Incluso da layout.php: stampaHeader(). 
 */
require_once __DIR__ . '/auth.php';

$utente = utenteLoggato();
$paginaCorrente = basename($_SERVER['PHP_SELF']);

$navLinks = [
    'index.php' => 'Home',
    'gatti.php' => 'Adotta un gatto',
    'volontariato.php' => 'Volontariato',
];
if ($utente && (bool) $utente['is_admin']) {
    $navLinks['inserisci_gatto.php'] = 'Inserisci gatto';
}
?>

<body>

    <header class="header">
        <!-- Logo -->
        <a href="index.php" class="brand-logo" aria-label="Torna alla Home Page">
            <img src="img/logo.png" alt="Logo Gattile San Paolo" class="logo-img" width="48" height="48">
            <strong>Gattile San Paolo</strong>
        </a>

        <!-- Menu — visibile solo su mobile -->
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="menu-principale"
            aria-label="Apri menu di navigazione">
            <span class="menu-toggle-icona" aria-hidden="true"></span>
            Menu
        </button>

        <!-- Navigazione  -->
        <nav id="menu-principale" aria-label="Navigazione principale">
            <ul>
                <?php foreach ($navLinks as $href => $etichetta):
                    $attivo = ($href === $paginaCorrente);
                    ?>
                    <li>
                        <a href="<?= $href ?>" class="btn <?= $attivo ? 'active' : '' ?>" <?= $attivo ? 'aria-current="page"' : '' ?>>
                            <?= $etichetta ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <!-- Area account -->
        <section class="stato-autenticazione" id="autenticazione" aria-label="Stato autenticazione">
            <h2 class="sr-solo">Account</h2>

            <!-- Selettore tema a 3 stati: Sistema / Chiaro / Scuro -->
            <button type="button" id="toggle-tema" class="toggle-tema btn"
                aria-label="Cambia tema (attuale: sistema)" title="Cambia tema: Sistema / Chiaro / Scuro">
                <span class="icona-tema" aria-hidden="true"></span>
                <span class="testo-tema">Tema: sistema</span>
            </button>

            <div class="account-box">
                <?php if ($utente): ?>
                    <p class="utente-info">
                        <em class="nome-utente"><?= esc($utente['username']) ?></em>
                        <?php if ((bool) $utente['is_admin']): ?>
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
                        <a href="login.php" class="btn btn-login<?= $paginaCorrente === 'login.php' ? ' active' : '' ?>">
                            Accedi
                        </a>
                        <a href="registrazione.php"
                            class="btn btn-signin<?= $paginaCorrente === 'registrazione.php' ? ' active' : '' ?>">
                            Registrati
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        </section>

    </header>

    <script src="js/menu.js" defer></script>