<?php
/**
 * footer.php — Footer comune, incluso da layout.php::stampaFooter().
 * Nessun JS inline: gli script sono in js/footer.js (defer).
 * Nessun tag non-semantico.
 */
declare(strict_types=1);

$paginaCorrente = basename($_SERVER['PHP_SELF']);
$anno           = date('Y');

$navLinks = [
    'index.php'        => 'Home',
    'gatti.php'        => 'Adozioni',
    'volontariato.php' => 'Volontariato',
    'faq.php'          => 'FAQ',
    'privacy.php'      => 'Privacy',
];
if (!empty($_SESSION['utente']['is_admin'])) {
    $navLinks['inserisci_gatto.php'] = 'Inserisci gatto';
}
?>

<?php if (!isset($_COOKIE['cookie_consenso'])): ?>
<aside id="banner-cookie" class="banner-cookie" role="dialog"
       aria-modal="true" aria-live="polite" aria-label="Informativa cookie">
    <p>
        Questo sito usa solo cookie tecnici di sessione, necessari al funzionamento.
        Nessuna profilazione di terze parti.
        <a href="privacy.php">Maggiori informazioni</a>.
    </p>
    <nav aria-label="Gestione consenso cookie">
        <ul role="list">
            <li><button type="button" id="btn-accetta-cookie" class="btn btn-primario">Accetto</button></li>
            <li><a href="privacy.php#elimina" class="btn btn-secondario">Gestisci</a></li>
        </ul>
    </nav>
</aside>
<?php endif; ?>

<footer class="footer" role="contentinfo">

    <a href="index.php" class="brand-logo" aria-label="Torna alla Home Page">
        <img src="img/logo.png" alt="Logo Gattile San Paolo" class="logo-img" width="48" height="48">
        <strong>Gattile San Paolo</strong>
    </a>

    <nav aria-label="Navigazione footer">
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

    <section class="footer-info">

        <address>
            <strong>Contatti</strong><br>
            Via Felina 1, 10100 Torino<br>
            <a href="tel:+390111234567">011 123 4567</a><br>
            <a href="mailto:info@gattile-San Paolo.example.it">info@gattile-San Paolo.example.it</a>
        </address>

        <nav aria-label="Privacy e gestione dati">
            <ul role="list">
                <li><a href="privacy.php">Informativa privacy</a></li>
                <li>
                    <button type="button" id="btn-elimina-cookie" class="link-button">
                        Elimina i miei cookie
                    </button>
                </li>
            </ul>
        </nav>

        <p class="footer-url" aria-hidden="true">
            Pagina: <strong id="footer-url-corrente"></strong>
        </p>

    </section>

    <p class="footer-copy">
        &copy; <time datetime="<?= esc($anno) ?>"><?= esc($anno) ?></time>
        Gattile San Paolo &middot; Tutti i diritti riservati
    </p>

</footer>

<a href="faq.php" class="faq-button" aria-label="Domande frequenti" title="FAQ — Domande frequenti">?</a>

<script src="js/footer.js" defer></script>

</body>
</html>
