<?php
/**
 * header.php — Header comune a tutte le pagine.
 * Incluso da ogni pagina dopo stampaTesta().
 * Stile: palette saddlebrown/darkolivegreen/cornsilk, hamburger mobile.
 */
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$utente = utenteLoggato();
$paginaCorrente = basename($_SERVER['PHP_SELF']);

// Link di navigazione principali
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

        <!-- Hamburger mobile -->
        <button class="menu-toggle" aria-expanded="false" aria-controls="menu-principale"
            aria-label="Apri menu di navigazione" type="button">
            ☰
        </button>

        <!-- Navigazione principale -->
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

        <!-- Stato autenticazione -->
        <section class="stato-autenticazione" id="userStatusBox" aria-label="Stato autenticazione">
            <article class="account-box">
                <?php if ($utente): ?>
                    <p class="utente-info">
                        <span aria-hidden="true">👤</span>
                        <strong><?= $utente['nome'] ?></strong>
                        <span>(<?= $utente['username'] ?>)</span>
                        <?php if ((bool) $utente['is_admin']): ?>
                            <p class="badge-gatto">Amministratore</p>
                        <?php else: ?>
                            <p class="badge-gatto">Utente</p>
                        <?php endif; ?>
                    </p>
                    <ul class="azioni-account" role="list">
                        <li><a href="logout.php" class="btn-account btn-logout">Esci</a></li>
                    </ul>
                <?php else: ?>
                    <p class="utente-info">
                        Stato: <em>non loggato</em>
                    </p>
                    <ul class="azioni-account" role="list">
                        <li>
                            <a href="login.php"
                                class="btn-account btn-login<?= $paginaCorrente === 'login.php' ? ' active' : '' ?>">
                                Accedi
                            </a>
                        </li>
                        <li>
                            <a href="registrazione.php"
                                class="btn-account btn-login<?= $paginaCorrente === 'registrazione.php' ? ' active' : '' ?>">
                                Registrati
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            </article>
        </section>

    </header>

    <!-- Script hamburger — inline minimo per reattività immediata -->
    <script src="js/menu.js" defer></script>
    