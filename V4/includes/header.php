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

    <header class="header" role="banner">

        <!-- Logo -->
        <a href="index.php" class="brand-logo" aria-label="Torna alla Home Page">
            <img src="img/logo.png" alt="Logo Gattile San Paolo" class="logo-img" width="48" height="48">
            <strong>Gattile San Paolo</strong>
        </a>

        <!-- Menu — visibile solo su mobile -->
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="menu-principale"
            aria-label="Apri menu di navigazione">
            ☰
        </button>

        <!-- Navigazione  -->
        <nav id="menu-principale" aria-label="Navigazione principale">
            <ul role="list">
                <?php foreach ($navLinks as $href => $etichetta):
                    $attivo = ($href === $paginaCorrente);
                    ?>
                    <li>
                        <a href="<?= $href ?>" <?= $attivo ? 'class="active" aria-current="page"' : '' ?>>
                            <?= $etichetta ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <!-- Area account -->
        <section class="stato-autenticazione" id="autenticazione" aria-label="Stato autenticazione">
            <article class="account-box">
                <?php if ($utente): ?>
                    <p class="utente-info">
                        <abbr title="Utente" aria-hidden="true">👤</abbr>
                        <em class="nome-utente"><?= $utente['username'] ?></em>
                        <?php if ((bool) $utente['is_admin']): ?>
                            <em class="badge-gatto">Amministratore</em>
                        <?php else: ?>
                            <em class="badge-gatto">Utente</em>
                        <?php endif; ?>
                    </p>
                    <nav aria-label="Azioni account">
                        <a href="logout.php" class="btn-account btn-logout">Esci</a>
                    </nav>
                <?php else: ?>
                    <p class="utente-info">
                        Stato: <em>non loggato</em>
                    </p>
                    <nav aria-label="Accesso e registrazione">
                        <a href="login.php"
                            class="btn-account btn-login<?= $paginaCorrente === 'login.php' ? ' active' : '' ?>">
                            Accedi
                        </a>
                        <a href="registrazione.php"
                            class="btn-account btn-login<?= $paginaCorrente === 'registrazione.php' ? ' active' : '' ?>">
                            Registrati
                        </a>
                    </nav>
                <?php endif; ?>
            </article>
        </section>

    </header>

    <script src="js/menu.js" defer></script>