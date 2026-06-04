<?php
/**
 * index.php — Pagina principale di Gattile San Paolo.
 * Mostra presentazione generale e gli ultimi 2 gatti arrivati.
 */
declare(strict_types=1);

require_once 'includes/layout.php';
require_once 'includes/db.php';

avviaSessione();

$erroreDB    = null;
$nuoviArrivi = [];

try {
    $db  = getDB('reader');
    $stm = $db->prepare(
        'SELECT id, nome, descrizione, eta, sesso, colore_mantello, data_arrivo
         FROM gatti ORDER BY data_arrivo DESC LIMIT 2'
    );
    $stm->execute();
    $nuoviArrivi = $stm->fetchAll();
} catch (PDOException $e) {
    error_log('Errore DB index.php: ' . $e->getMessage());
    $erroreDB = 'Impossibile caricare i nuovi arrivi dal database. Riprova tra qualche minuto.';
}

stampaTesta(
    'Benvenuto',
    'Gattile San Paolo: adotta un gatto o diventa volontario a Torino. Scopri i nostri ospiti felini.',
    'index.php'
);
stampaHeader();
apriMain();
?>

<section aria-labelledby="titolo-benvenuto">
    <h1 id="titolo-benvenuto">Gattile San Paolo — Una casa per ogni gatto</h1>
    <p>
        Ogni anno, centinaia di gatti vengono abbandonati o nascono in strada,
        necessitando di cure e di una famiglia. Allo stesso tempo, molte persone
        desiderano accogliere un felino o dedicare il proprio tempo come volontari.
    </p>
    <p>
        <strong>Gattile San Paolo</strong> nasce per facilitare le adozioni e organizzare
        il supporto attivo alla struttura ospitante, con sede a <strong>Torino</strong>.
    </p>

    <nav aria-label="Azioni principali" class="azioni-principali">
        <ul role="list">
            <li><a href="gatti.php"        class="btn btn-primario">🐾 Adotta un gatto</a></li>
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
        <li><strong>Sfoglia i gatti</strong> disponibili nell'<a href="gatti.php">area adozioni</a>.</li>
        <li><strong>Registrati o accedi</strong> al tuo profilo per selezionare i gatti di interesse.</li>
        <li><strong>Prenota una visita</strong> conoscitiva scegliendo data e ora.</li>
        <li>In alternativa, <strong>diventa volontario</strong> scegliendo le fasce orarie disponibili.</li>
    </ol>
</section>

<hr class="separatore">

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
                        <img src="img/placeholder-gatto.svg"
                             alt="Sagoma stilizzata di un gatto — foto di <?= esc($gatto['nome']) ?> non ancora disponibile"
                             width="320" height="240" loading="lazy">
                        <figcaption class="sr-solo">Placeholder per <?= esc($gatto['nome']) ?></figcaption>
                    </figure>
                    <div class="card-gatto-corpo">
                        <h3 id="nuovo-<?= (int)$gatto['id'] ?>">
                            <?= esc($gatto['nome']) ?>
                            <span class="badge-nuovo" aria-label="Nuovo arrivo">Nuovo</span>
                        </h3>
                        <ul class="card-gatto-meta" role="list" aria-label="Caratteristiche">
                            <li class="tag"><?= esc($gatto['sesso'] === 'M' ? 'Maschio' : 'Femmina') ?></li>
                            <li class="tag"><?= esc((string)$gatto['eta']) ?> mesi</li>
                            <li class="tag"><?= esc($gatto['colore_mantello']) ?></li>
                        </ul>
                        <p><?= esc($gatto['descrizione']) ?></p>
                        <p><time datetime="<?= esc($gatto['data_arrivo']) ?>">
                            Arrivato il <?= date('d/m/Y', strtotime($gatto['data_arrivo'])) ?>
                        </time></p>
                        <a href="gatti.php" class="btn btn-secondario"
                           aria-label="Scopri di più su <?= esc($gatto['nome']) ?>">Scopri di più</a>
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
        <p>Assolutamente sì. Molti dei nostri gatti si adattano perfettamente alla vita in appartamento.</p>
    </details>
    <details>
        <summary>Quanto costa adottare un gatto?</summary>
        <p>L'adozione è gratuita. Chiediamo solo la disponibilità a prendersi cura dell'animale.</p>
    </details>
    <details>
        <summary>Come posso diventare volontario?</summary>
        <p><a href="registrazione.php">Registrati</a>, poi accedi alla pagina
        <a href="volontariato.php">Volontariato</a> e scegli le fasce orarie disponibili.</p>
    </details>
</section>

<?php
chiudiMain();
stampaFooter();
?>
