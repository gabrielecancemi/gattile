<?php
// ============================================================
// banner.php — Banner pubblicitari (negozio per animali).
// ------------------------------------------------------------
// Espone due helper, usati da header.php e footer.php:
//
//   bannerLaterali()
//     Stampa i due <aside> pubblicitari verticali, uno a sinistra
//     e uno a destra, che restano fissi allo scroll (CSS position:
//     fixed) finché c'è spazio laterale a sufficienza. Quando la
//     finestra si restringe oltre la soglia (larghezza immagine +
//     bordo), questi vengono nascosti via CSS e subentrano i
//     banner orizzontali.
//
//   bannerOrizzontale(string $posizione)
//     Stampa un <aside> pubblicitario orizzontale. Ne viene messo
//     uno prima dell'<h1> della pagina ($posizione = 'alto') e uno
//     in fondo a tutto ($posizione = 'basso'). Sono visibili solo
//     quando i banner laterali non ci stanno più.
//
// Il passaggio automatico tra versione verticale e orizzontale è
// gestito dal tag <picture> con attributo media sulle <source>,
// così il browser scarica solo l'immagine effettivamente mostrata.
// ============================================================

// Soglia oltre la quale i banner laterali verticali lasciano il
// posto a quelli orizzontali. Deve coincidere con quella del CSS.
if (!defined('BANNER_SOGLIA')) {
    define('BANNER_SOGLIA', '1500px');
}

// Banner verticali laterali fissi (sinistra + destra).
function bannerLaterali(): void
{
    $soglia = BANNER_SOGLIA;
    foreach (['sinistra' => 'banner-laterale-sinistra', 'destra' => 'banner-laterale-destra'] as $lato => $cls) {
        ?>
        <aside class="banner-pubblicita banner-laterale <?= $cls ?>" aria-label="Pubblicità">
            <button type="button" class="banner-chiudi" aria-label="Chiudi pubblicità" title="Chiudi pubblicità">&times;</button>
            <a href="#" class="banner-link" rel="nofollow sponsored">
                <picture>
                    <source srcset="img/banner-verticale.png" media="(min-width: <?= $soglia ?>)" width="160" height="600">
                    <source srcset="img/banner-orizzontale.png" media="(max-width: <?= $soglia ?>)" width="728" height="140">
                    <img src="img/banner-verticale.png" alt="Gatto &amp; Amici — negozio per animali: -30% sulle crocchette premium" width="160" height="600">
                </picture>
            </a>
        </aside>
        <?php
    }
}

// Banner orizzontale ('alto' = prima dell'h1, 'basso' = in fondo).
function bannerOrizzontale(string $posizione = 'alto'): void
{
    $soglia = BANNER_SOGLIA;
    $cls = $posizione === 'basso' ? 'banner-orizzontale-basso' : 'banner-orizzontale-alto';
    ?>
    <aside class="banner-pubblicita banner-orizzontale <?= $cls ?>" aria-label="Pubblicità">
        <span class="banner-wrap">
            <button type="button" class="banner-chiudi" aria-label="Chiudi pubblicità" title="Chiudi pubblicità">&times;</button>
            <a href="#" class="banner-link" rel="nofollow sponsored">
                <picture>
                    <source srcset="img/banner-orizzontale.png" media="(max-width: <?= $soglia ?>)" width="728" height="140">
                    <img src="img/banner-orizzontale.png" alt="Gatto &amp; Amici — negozio per animali: -30% sulle crocchette premium" width="728" height="140">
                </picture>
            </a>
        </span>
    </aside>
    <?php
}
