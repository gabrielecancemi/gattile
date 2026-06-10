<?php
// Banner pubblicitari (negozio per animali).

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
