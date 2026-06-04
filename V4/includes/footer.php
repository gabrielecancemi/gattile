<?php
/**
 * footer.php — Footer comune.
 * Incluso da layout.php: stampaFooter().
 */

$paginaCorrente = basename($_SERVER['PHP_SELF']);
$anno = date('Y');

$navLinks = [
    'index.php' => 'Home',
    'gatti.php' => 'Adotta un gatto',
    'volontariato.php' => 'Volontariato',
    'faq.php' => 'FAQ',
];
if (!empty($_SESSION['utente']['is_admin'])) {
    $navLinks['inserisci_gatto.php'] = 'Inserisci gatto';
}
?>

<?php if (!isset($_COOKIE['cookie_consenso'])): ?>
    <aside id="banner-cookie" class="banner-cookie" role="dialog" aria-modal="true" aria-live="polite"
        aria-label="Informativa cookie">
        <p>
            Questo sito usa solo cookie tecnici di sessione, necessari al funzionamento.
            Nessuna profilazione di terze parti.
            <a href="privacy.php">Maggiori informazioni</a>.
        </p>
        <nav aria-label="Gestione consenso cookie">
            <ul role="list">
                <li><button type="button" id="btn-accetta-cookie" class="btn btn-primario">Accetto</button></li>
                <li><a href="privacy.php" class="btn btn-secondario">Gestisci</a></li>
            </ul>
        </nav>
    </aside>
<?php endif; ?>

<footer class="footer" role="contentinfo">

    <!-- Logo -->
    <a href="index.php" class="brand-logo" aria-label="Torna alla Home Page">
        <img src="img/logo.png" alt="Logo Gattile San Paolo" class="logo-img" width="48" height="48">
        <strong>Gattile San Paolo</strong>
    </a>

    <!-- Navigazione + privacy: colonna centrale -->
    <section class="footer-centro">

        <nav aria-label="Navigazione footer">
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

        <!-- Link privacy e cookie centrati sotto il menu -->
        <nav aria-label="Privacy e gestione dati" class="footer-privacy">
            <ul role="list">
                <li><a href="privacy.php">Informativa privacy</a></li>
                <li><a href="privacy.php#elimina">Elimina i miei cookie</a></li>
            </ul>
        </nav>

    </section>

    <!-- Contatti -->
    <address class="footer-contatti">
        <strong>Contatti</strong><br>
        Via San Paolo 1, 10100 Torino (TO)<br>
        <a href="tel:+390111234567">011 123 4567</a><br>
        <a href="mailto:info@gattile-San Paolo.example.it">info@gattile-sanpaolo.it</a>
    </address>

    <!-- Copyright -->
    <p class="footer-copy">
        &copy; <time datetime="<?= $anno ?>"><?= $anno ?></time>
        Gattile San Paolo &middot; Tutti i diritti riservati
    </p>

</footer>

<a href="faq.php" id="faq" class="faq-button <?= $paginaCorrente === 'faq.php' ? 'active' : '' ?>" aria-label="Domande frequenti" title="FAQ — Domande frequenti">?</a>

<script src="js/footer.js" defer></script>

</body>

</html>