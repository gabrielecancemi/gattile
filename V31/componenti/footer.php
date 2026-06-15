<?php
// Piè di pagina comune

// Nome del file corrente (ultima parte del percorso).
$percorso_corrente = $_SERVER['PHP_SELF'] ?? '';
$parti_percorso = explode('/', $percorso_corrente);
$pagina_corrente = $parti_percorso[count($parti_percorso) - 1];
$anno = date('Y');

$voci_nav = [
    'home.php' => 'Home',
    'adozioni.php' => 'Adozioni',
    'volontariato.php' => 'Volontariato',
    'faq.php' => 'FAQ',
];
if (!empty($_SESSION['utente']['is_admin'])) {
    $voci_nav['inserisci_gatto.php'] = 'Inserisci gatto';
}
?>

<!-- cookie -->
<?php if (!isset($_COOKIE['consenso_cookie'])): ?>
    <aside id="banner-cookie" class="banner-cookie" aria-live="polite" aria-label="Informativa cookie">
        <p>
            Questo sito usa solo cookie tecnici di sessione, necessari al funzionamento.
            Nessuna profilazione di terze parti.
            <a href="privacy.php">Maggiori informazioni</a>.
        </p>
        <nav aria-label="Gestione consenso cookie">
            <ul>
                <li><button type="button" id="btn-accetta-cookie" class="btn btn-primario">Accetta</button></li>
                <li><a href="privacy.php" class="btn btn-secondario">Gestisci</a></li>
            </ul>
        </nav>
    </aside>
<?php endif; ?>

<!-- footer -->
<footer class="footer">

    <a href="home.php" class="brand-logo" aria-label="Torna alla Home Page">
        <picture class="logo-container">
            <source srcset="img/logo_grande.png" media="(min-width: 600px)">
            <img src="img/logo_piccolo.png" alt="Logo Gattile San Paolo" class="logo-img">
        </picture>
        <strong>Gattile San Paolo</strong>
    </a>

    <section class="footer-navigazione">

        <nav aria-label="Navigazione footer">
            <ul>
                <?php foreach ($voci_nav as $href => $etichetta):
                    $attivo = ($href === $pagina_corrente);
                    ?>
                    <li>
                        <a href="<?= $href ?>" class="btn<?= $attivo ? ' active' : '' ?>" <?= $attivo ? ' aria-current="page"' : '' ?>>
                            <?= $etichetta ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <h2 class="sr-solo">Collegamenti</h2>

        <nav aria-label="Privacy e gestione dati" class="footer-privacy">
            <ul>
                <li><a href="privacy.php">Informativa privacy</a></li>
                <li><a href="privacy.php#elimina">Elimina i miei cookie</a></li>
            </ul>
        </nav>
    </section>

    <address class="footer-contatti">
        <strong>Contatti</strong>
        <p>Via San Paolo 1, 10100 Torino (TO)</p>
        <p><a href="tel:+390111234567">011 123 4567</a></p>
        <p><a href="mailto:info@gattile-sanpaolo.it">info@gattile-sanpaolo.it</a></p>
    </address>

    <p class="footer-copy">
        &copy; <time datetime="<?= $anno ?>"><?= $anno ?></time>
        Gattile San Paolo &middot; Tutti i diritti riservati
    </p>

</footer>

<a href="faq.php" id="faq" class="faq-button <?= $pagina_corrente === 'faq.php' ? 'active' : '' ?>"
    aria-label="Domande frequenti" title="FAQ — Domande frequenti">?</a>

<script src="js/tema.js" defer></script>
<script src="js/footer.js" defer></script>

</body>

</html>