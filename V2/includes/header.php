<!-- header.php — Header comune.-->


<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$utente         = utenteLoggato();
$paginaCorrente = basename($_SERVER['PHP_SELF']);

$navLinks = [
    'index.php'        => 'Home',
    'gatti.php'        => 'Adotta un gatto',
    'volontariato.php' => 'Volontariato',
];
if ($utente && (bool)$utente['is_admin']) {
    $navLinks['inserisci_gatto.php'] = 'Inserisci gatto';
}
?>
<body>

<a href="#contenuto-principale" class="skip-link">Vai al contenuto principale</a>

<header class="header" role="banner">

    <a href="index.php" class="brand-logo" aria-label="Torna alla Home Page">
        <img src="img/logo.png" alt="Logo Gattile San Paolo" class="logo-img" width="48" height="48">
        <strong>Gattile San Paolo</strong>
    </a>

    <button class="menu-toggle"
            type="button"
            aria-expanded="false"
            aria-controls="menu-principale"
            aria-label="Apri menu di navigazione">
        ☰
    </button>

    <nav id="menu-principale" aria-label="Navigazione principale">
        <ul role="list">
            <?php foreach ($navLinks as $href => $etichetta):
                $attivo = ($href === $paginaCorrente);
            ?>
            <li>
                <a href="<?= esc($href) ?>"
                   <?= $attivo ? 'class="active" aria-current="page"' : '' ?>>
                    <?= esc($etichetta) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <section class="stato-autenticazione" id="userStatusBox" aria-label="Stato autenticazione">
        <article class="account-box">
            <?php if ($utente): ?>
                <p class="utente-info">
                    <b aria-hidden="true">👤</b>
                    <strong><?= esc($utente['nome']) ?></strong>
                    <em class="nome-utente">(<?= esc($utente['username']) ?>)</em>
                    <?php if ((bool)$utente['is_admin']): ?>
                        <b class="badge-gatto">Amministratore</b>
                    <?php else: ?>
                        <b class="badge-gatto">Utente</b>
                    <?php endif; ?>
                </p>
                <nav aria-label="Azioni account" class="azioni-account-nav">
                    <a href="logout.php" class="btn-account btn-logout">Esci</a>
                </nav>
            <?php else: ?>
                <p class="utente-info">
                    Stato: <em>non loggato</em>
                </p>
                <nav aria-label="Accesso e registrazione" class="azioni-account-nav">
                    <a href="login.php"
                       class="btn-account btn-login<?= $paginaCorrente === 'login.php' ? ' attivo' : '' ?>">
                        Accedi
                    </a>
                    <a href="registrazione.php"
                       class="btn-account btn-login<?= $paginaCorrente === 'registrazione.php' ? ' attivo' : '' ?>">
                        Registrati
                    </a>
                </nav>
            <?php endif; ?>
        </article>
    </section>

</header>

<script src="js/menu.js" defer></script>
