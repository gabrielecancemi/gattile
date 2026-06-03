<?php
/**
 * footer.php — Footer comune a tutte le pagine.
 * Incluso da ogni pagina dopo chiudiMain().
 * Contiene: logo, nav, contatti, privacy/cookie, bottone FAQ.
 */
declare(strict_types=1);

$paginaCorrente = basename($_SERVER['PHP_SELF']);
$anno           = date('Y');

// Stessi link del header
$navLinks = [
    'index.php'        => 'Home',
    'gatti.php'        => 'Adozioni',
    'volontariato.php' => 'Volontariato',
    'privacy.php'      => 'Privacy',
];
if (!empty($_SESSION['is_admin'])) {
    $navLinks['inserisci_gatto.php'] = 'Inserisci gatto';
}
?>

<!-- Banner consenso cookie — mostrato solo se non già accettato -->
<?php if (!isset($_COOKIE['cookie_consenso'])): ?>
<aside id="banner-cookie" class="banner-cookie" role="dialog"
       aria-live="polite" aria-label="Informativa cookie">
    <p>
        Questo sito usa solo cookie tecnici di sessione, necessari al funzionamento.
        Nessuna profilazione.
        <a href="privacy.php">Maggiori informazioni</a>.
    </p>
    <nav aria-label="Scelte consenso cookie">
        <button type="button" id="btn-accetta-cookie" class="btn btn-primario">Accetto</button>
        <a href="privacy.php#elimina" class="btn btn-secondario">Gestisci</a>
    </nav>
</aside>
<script>
document.getElementById('btn-accetta-cookie').addEventListener('click', function () {
    const scad = new Date(Date.now() + 365 * 24 * 3600 * 1000).toUTCString();
    document.cookie = 'cookie_consenso=1; expires=' + scad + '; path=/; SameSite=Strict';
    document.getElementById('banner-cookie').hidden = true;
});
</script>
<?php endif; ?>

<footer class="footer" role="contentinfo">

    <!-- Logo -->
    <a href="index.php" class="brand-logo" aria-label="Torna alla Home Page">
        <img src="img/logo.svg" alt="Zampa stilizzata — logo Gattile Felice" class="logo-img" width="48" height="48">
        <strong>Gattile Felice</strong>
    </a>

    <!-- Navigazione footer -->
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

    <!-- Contatti e funzionalità privacy -->
    <section class="footer-info">

        <address>
            <strong>Contatti</strong><br>
            Via Felina 1, 10100 Torino<br>
            <a href="tel:+390111234567">011 123 4567</a><br>
            <a href="mailto:info@gattile-felice.example.it">info@gattile-felice.example.it</a>
        </address>

        <!-- Gestione cookie/privacy dal footer -->
        <nav aria-label="Privacy e cookie">
            <ul role="list">
                <li>
                    <a href="privacy.php">Informativa privacy</a>
                </li>
                <li>
                    <!-- Elimina tutti i cookie senza lasciare la pagina -->
                    <button type="button" id="btn-elimina-cookie" class="link-button">
                        Elimina i miei cookie
                    </button>
                </li>
            </ul>
        </nav>

        <!-- URL pagina corrente (visibile solo a stampa via CSS) -->
        <p class="footer-url" aria-hidden="true">
            Pagina: <strong id="footer-url-corrente"></strong>
        </p>

    </section>

    <!-- Copyright -->
    <p class="footer-copy">
        &copy;
        <time datetime="<?= $anno ?>"><?= $anno ?></time>
        Gattile Felice &middot; Tutti i diritti riservati
    </p>

</footer>

<!-- Pulsante FAQ fisso in basso a destra -->
<a href="privacy.php" class="faq-button" aria-label="Privacy e domande frequenti" title="Privacy e cookie">
    ?
</a>

<script>
// Popola URL nel footer (usato anche dal CSS di stampa via data-attribute)
(function () {
    const el = document.getElementById('footer-url-corrente');
    if (el) el.textContent = window.location.href;

    // Imposta data-url sul footer per il CSS di stampa
    const footer = document.querySelector('.footer');
    if (footer) footer.setAttribute('data-url', window.location.href);
})();

// Elimina cookie
document.getElementById('btn-elimina-cookie').addEventListener('click', function () {
    if (confirm('Sei sicuro di voler eliminare tutti i cookie? Verrai disconnesso.')) {
        fetch('api/elimina_cookie.php', { method: 'POST', credentials: 'same-origin' })
            .then(r => r.json())
            .then(() => { window.location.href = 'privacy.php?eliminati=1'; })
            .catch(() => { window.location.href = 'privacy.php?eliminati=1'; });
    }
});
</script>

</body>
</html>
