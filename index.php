<?php
/**
 * index.php — Pagina principale di Gattile Felice.
 * Mostra presentazione generale e gli ultimi 2 gatti arrivati.
 */
declare(strict_types=1);

require_once 'includes/layout.php';
require_once 'includes/db.php';

// Recupera gli ultimi 2 gatti arrivati
$erroreDB      = null;
$nuoviArrivi   = [];

try {
    $db  = getDB('reader');
    $stm = $db->prepare(
        'SELECT id, nome, descrizione, eta, sesso, colore_mantello, data_arrivo
         FROM gatti
         ORDER BY data_arrivo DESC
         LIMIT 2'
    );
    $stm->execute();
    $nuoviArrivi = $stm->fetchAll();
} catch (PDOException $e) {
    error_log('Errore DB index.php: ' . $e->getMessage());
    $erroreDB = 'Impossibile caricare i nuovi arrivi dal database. Riprova tra qualche minuto.';
}

avviaSessione();
stampaTesta(
    'Benvenuto',
    'Gattile Felice: adotta un gatto o diventa volontario a Torino. Scopri i nostri ospiti felini e come aiutarci.',
    'index.php'
);

echo '<body>';
stampaHeader();
stampaBannerCookie();
apriMain();
?>

<section class="sezione-benvenuto" aria-labelledby="titolo-benvenuto">
    <h1 id="titolo-benvenuto">Gattile Felice — Una casa per ogni gatto</h1>
    <p>
        Ogni anno, centinaia di gatti vengono abbandonati o nascono in strada, 
        necessitando di cure e di una famiglia. Allo stesso tempo, molte persone 
        desiderano accogliere un felino o dedicare il proprio tempo come volontari.
    </p>
    <p>
        <strong>Gattile Felice</strong> nasce per facilitare le adozioni e organizzare 
        il supporto attivo alla struttura ospitante, con sede a <strong>Torino</strong>.
    </p>

    <nav aria-label="Azioni principali" class="azioni-principali">
        <ul role="list">
            <li><a href="gatti.php" class="btn btn-primario">🐾 Adotta un gatto</a></li>
            <li><a href="volontariato.php" class="btn btn-secondario">❤️ Fai volontariato</a></li>
            <?php if (!utenteLoggato()): ?>
            <li><a href="registrazione.php" class="btn btn-secondario">✏️ Registrati</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</section>

<hr class="separatore">

<section aria-labelledby="titolo-come-funziona">
    <h2 id="titolo-come-funziona">Come funziona</h2>
    <ol>
        <li>
            <strong>Sfoglia i gatti</strong> disponibili nell'<a href="gatti.php">area adozioni</a>: 
            puoi filtrare per nome, descrizione, età o colore del manto.
        </li>
        <li>
            <strong>Registrati o accedi</strong> al tuo profilo per selezionare i gatti 
            di cui vorresti sapere di più.
        </li>
        <li>
            <strong>Prenota una visita</strong> conoscitiva direttamente dal sito, 
            indicando la data e l'ora che preferisci.
        </li>
        <li>
            In alternativa, puoi <strong>diventare volontario</strong> e scegliere le 
            fasce orarie in cui prestare il tuo aiuto.
        </li>
    </ol>
</section>

<hr class="separatore">

<!-- Sezione "nuovi arrivi" — dati estratti dal DB -->
<section class="nuovi-arrivi" aria-labelledby="titolo-nuovi-arrivi">
    <h2 id="titolo-nuovi-arrivi">🆕 Nuovi arrivi</h2>
    <p>Gli ultimi ospiti entrati nella nostra struttura che aspettano una famiglia:</p>

    <?php if ($erroreDB): ?>
        <?= messaggioUtente($erroreDB, 'errore') ?>
    <?php elseif (empty($nuoviArrivi)): ?>
        <p>Nessun gatto registrato al momento. Torna presto!</p>
    <?php else: ?>
        <ul class="griglia-gatti" role="list" aria-label="Nuovi arrivi">
        <?php foreach ($nuoviArrivi as $gatto): ?>
            <li>
                <article class="card-gatto" aria-labelledby="nuovo-<?= (int)$gatto['id'] ?>">
                    <figure>
                        <img
                            src="img/placeholder-gatto.svg"
                            alt="Sagoma stilizzata di un gatto — foto di <?= esc($gatto['nome']) ?> non ancora disponibile"
                            width="320"
                            height="240"
                            loading="lazy"
                        >
                        <figcaption class="sr-solo">Foto placeholder per <?= esc($gatto['nome']) ?></figcaption>
                    </figure>
                    <div class="card-gatto-corpo">
                        <h3 id="nuovo-<?= (int)$gatto['id'] ?>">
                            <?= esc($gatto['nome']) ?>
                            <span class="badge-nuovo" aria-label="Nuovo arrivo">Nuovo</span>
                        </h3>
                        <ul class="card-gatto-meta" role="list" aria-label="Caratteristiche">
                            <li class="tag"><?= esc($gatto['sesso'] === 'M' ? 'Maschio' : 'Femmina') ?></li>
                            <li class="tag"><?= esc($gatto['eta']) ?> <?= (int)$gatto['eta'] === 1 ? 'mese' : 'mesi' ?></li>
                            <li class="tag"><?= esc($gatto['colore_mantello']) ?></li>
                        </ul>
                        <p><?= esc($gatto['descrizione']) ?></p>
                        <p>
                            <time datetime="<?= esc($gatto['data_arrivo']) ?>">
                                Arrivato il <?= date('d/m/Y', strtotime($gatto['data_arrivo'])) ?>
                            </time>
                        </p>
                        <a href="gatti.php" class="btn btn-secondario" aria-label="Scopri di più su <?= esc($gatto['nome']) ?>">Scopri di più</a>
                    </div>
                </article>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<hr class="separatore">

<section aria-labelledby="titolo-faq">
    <h2 id="titolo-faq">Domande frequenti</h2>

    <details>
        <summary>Posso adottare anche se vivo in appartamento?</summary>
        <p>Assolutamente sì. Molti dei nostri gatti sono nati in ambienti chiusi e si 
        adattano perfettamente alla vita in appartamento, purché abbiano spazi per giocare 
        e qualcuno che li ami.</p>
    </details>

    <details>
        <summary>Quanto costa adottare un gatto?</summary>
        <p>L'adozione è gratuita. Chiediamo solo la disponibilità a prendersi cura 
        dell'animale e a sostenere le spese veterinarie ordinarie.</p>
    </details>

    <details>
        <summary>Come posso diventare volontario?</summary>
        <p><a href="registrazione.php">Registrati al sito</a>, poi accedi alla pagina 
        <a href="volontariato.php">Volontariato</a> e scegli le fasce orarie in cui 
        desideri prestare servizio. La struttura accoglie fino a due volontari per fascia.</p>
    </details>
</section>

<?php
chiudiMain();
stampaFooter();
chiudiHTML();
?>
