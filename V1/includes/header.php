<?php
/**
 * header.php — Header comune a tutte le pagine.
 * Incluso da ogni pagina dopo stampaTesta().
 * Stile: palette saddlebrown/darkolivegreen/cornsilk, hamburger mobile.
 */
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$utente         = utenteLoggato();
$paginaCorrente = basename($_SERVER['PHP_SELF']);

// Link di navigazione principali
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

<!-- Skip link accessibilità -->
<a href="#contenuto-principale" class="skip-link">Vai al contenuto principale</a>

<header class="header" role="banner">

    <!-- Logo -->
    <a href="index.php" class="brand-logo" aria-label="Torna alla Home Page">
        <img src="img/logo.svg" alt="Zampa stilizzata — logo Gattile Felice" class="logo-img" width="48" height="48">
        <strong>Gattile Felice</strong>
    </a>

    <!-- Hamburger mobile -->
    <button class="menu-toggle"
            aria-expanded="false"
            aria-controls="menu-principale"
            aria-label="Apri menu di navigazione"
            type="button">
        ☰
    </button>

    <!-- Navigazione principale -->
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

    <!-- Stato autenticazione -->
    <section class="stato-autenticazione" id="userStatusBox" aria-label="Stato autenticazione">
        <article class="account-box">
            <?php if ($utente): ?>
                <p class="utente-info">
                    <span aria-hidden="true">👤</span>
                    <strong><?= esc($utente['nome']) ?></strong>
                    <span>(<?= esc($utente['username']) ?>)</span>
                    <?php if ((bool)$utente['is_admin']): ?>
                        <small class="badge-gatto">Amministratore</small>
                    <?php else: ?>
                        <small class="badge-gatto">Utente</small>
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
<script>
(function () {
    'use strict';
    const btn = document.querySelector('.menu-toggle');
    if (!btn) return;
    btn.addEventListener('click', function () {
        const aperto = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', String(!aperto));
        document.getElementById('menu-principale')
                ?.classList.toggle('aperto', !aperto);
        document.getElementById('userStatusBox')
                ?.classList.toggle('aperto', !aperto);
    });
})();
</script>
