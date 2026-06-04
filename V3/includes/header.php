<?php
// header.php — Header comune.

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

        <a href="index.php" class="brand-logo" aria-label="Torna alla Home Page">
            <img src="img/logo.png" alt="Logo Gattile San Paolo" class="logo-img" width="48" height="48">
            <strong>Gattile San Paolo</strong>
        </a>

        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="menu-principale"
            aria-label="Apri menu di navigazione">
            ☰
        </button>

        <nav id="menu-principale" aria-label="Navigazione principale">
            <ul role="list">
                <?php foreach ($navLinks as $href => $etichetta):
                    $attivo = ($href === $paginaCorrente);
                    ?>
                    <li>
                        <a href="<?= esc($href) ?>" <?= $attivo ? 'class="active" aria-current="page"' : '' ?>>
                            <?= esc($etichetta) ?>
                        </a>
                    </li>
                <?php endforeach; ?>

                <?php
                /*
                 * Su mobile i link account vengono aggiunti qui dentro al menu,
                 * così restano visibili quando si apre l'hamburger.
                 * Su desktop questi li nascondiamo con CSS (.nav-account-mobile).
                 */
                ?>
                <?php if ($utente): ?>
                    <li class="nav-account-mobile nav-sep" role="separator" aria-hidden="true"></li>
                    <li class="nav-account-mobile">
                        <a href="logout.php" class="nav-link-logout">Esci</a>
                    </li>
                <?php else: ?>
                    <li class="nav-account-mobile nav-sep" role="separator" aria-hidden="true"></li>
                    <li class="nav-account-mobile">
                        <a href="login.php" <?= $paginaCorrente === 'login.php' ? 'class="active" aria-current="page"' : '' ?>>
                            Accedi
                        </a>
                    </li>
                    <li class="nav-account-mobile">
                        <a href="registrazione.php" <?= $paginaCorrente === 'registrazione.php' ? 'class="active" aria-current="page"' : '' ?>>
                            Registrati
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <section class="stato-autenticazione" id="userStatusBox" aria-label="Stato autenticazione">
            <article class="account-box">
                <?php if ($utente): ?>
                    <p class="utente-info">
                        <abbr title="Utente" aria-hidden="true">👤</abbr>
                        <strong><?= esc($utente['nome']) ?></strong>
                        <em class="nome-utente">(<?= esc($utente['username']) ?>)</em>
                        <?php if ((bool) $utente['is_admin']): ?>
                            <mark class="badge-gatto">Amministratore</mark>
                        <?php else: ?>
                            <mark class="badge-gatto">Utente</mark>
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