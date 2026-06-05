<?php
/**
 * index.php — Home page di Gattile San Paolo.
 * Le card dei nuovi arrivi usano la stessa struttura HTML delle card React (gatti.php),
 * così il CSS condiviso le rende identiche visivamente.
 */
declare(strict_types=1);

require_once 'includes/layout.php';
require_once 'includes/db.php';

avviaSessione();

$erroreDB    = null;
$nuoviArrivi = [];

$conn = null;
try {
    $conn = getDB('reader');

    $stm = mysqli_prepare(
        $conn,
        'SELECT id, nome, descrizione, peso, eta, sesso,
                colore_mantello, lunghezza_pelo, razza, colore_occhi, data_arrivo
         FROM gatti
         ORDER BY data_arrivo DESC
         LIMIT 2'
    );

    if (!$stm) {
        throw new RuntimeException(mysqli_error($conn));
    }

    mysqli_stmt_execute($stm);
    $result = mysqli_stmt_get_result($stm);

    while ($row = mysqli_fetch_assoc($result)) {
        $nuoviArrivi[] = $row;
    }

    mysqli_stmt_close($stm);
    mysqli_close($conn);

} catch (RuntimeException $e) {
    if ($conn) mysqli_close($conn);
    error_log('[index] Errore DB nuovi arrivi: ' . $e->getMessage());
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
        <li><strong>Registrati o accedi</strong> per selezionare i gatti di interesse.</li>
        <li><strong>Prenota una visita</strong> conoscitiva scegliendo data e ora.</li>
        <li>In alternativa, <strong>diventa volontario</strong> scegliendo le fasce orarie libere.</li>
    </ol>
</section>

<hr class="separatore">

<section class="nuovi-arrivi" aria-labelledby="titolo-nuovi-arrivi">
    <h2 id="titolo-nuovi-arrivi">🆕 Nuovi arrivi</h2>
    <p>Gli ultimi ospiti entrati nella struttura che aspettano una famiglia:</p>

    <?php if ($erroreDB): ?>
        <?= messaggioUtente($erroreDB, 'errore') ?>

    <?php elseif (empty($nuoviArrivi)): ?>
        <p>Nessun gatto registrato al momento. Torna presto!</p>

    <?php else: ?>
        <ul class="griglia-gatti" role="list" aria-label="Nuovi arrivi">
        <?php foreach ($nuoviArrivi as $gatto):
            $sesso    = $gatto['sesso'] === 'M' ? 'Maschio' : 'Femmina';
            $etaMesi  = (int)$gatto['eta'];
            $etaTesto = $etaMesi < 12
                ? $etaMesi . ' ' . ($etaMesi === 1 ? 'mese' : 'mesi')
                : floor($etaMesi / 12) . ' ' . (floor($etaMesi/12) === 1 ? 'anno' : 'anni');
        ?>
            <li>
                <article class="card-gatto" aria-labelledby="nuovo-<?= (int)$gatto['id'] ?>">

                    <figure>
                        <img src="img/placeholder-gatto.svg"
                             alt="Sagoma stilizzata — foto di <?= esc($gatto['nome']) ?> non ancora disponibile"
                             width="320" height="240" loading="lazy">
                        <figcaption class="sr-solo">Placeholder foto per <?= esc($gatto['nome']) ?></figcaption>
                    </figure>

                    <section class="card-gatto-corpo">
                        <h3 id="nuovo-<?= (int)$gatto['id'] ?>">
                            <?= esc($gatto['nome']) ?>
                            <mark class="badge-nuovo" aria-label="Nuovo arrivo">Nuovo</mark>
                        </h3>

                        <ul class="card-gatto-meta" role="list" aria-label="Caratteristiche principali">
                            <li class="tag"><?= esc($sesso) ?></li>
                            <li class="tag"><?= esc($etaTesto) ?></li>
                            <li class="tag"><?= esc($gatto['colore_mantello']) ?></li>
                            <li class="tag"><?= esc($gatto['lunghezza_pelo']) ?></li>
                            <li class="tag"><?= esc($gatto['razza']) ?></li>
                        </ul>

                        <p><?= esc($gatto['descrizione']) ?></p>

                        <dl>
                            <dt>Peso</dt>
                            <dd>
                                <data value="<?= esc((string)$gatto['peso']) ?>"><?= esc((string)$gatto['peso']) ?> kg</data>
                                <meter min="0" max="10" low="1" high="7" optimum="4"
                                       value="<?= esc((string)$gatto['peso']) ?>"
                                       aria-label="Peso di <?= esc($gatto['nome']) ?>: <?= esc((string)$gatto['peso']) ?> kg"
                                       title="Peso: <?= esc((string)$gatto['peso']) ?> kg"></meter>
                            </dd>
                            <dt>Occhi</dt>
                            <dd><?= esc($gatto['colore_occhi']) ?></dd>
                            <dt>Arrivato il</dt>
                            <dd>
                                <time datetime="<?= esc($gatto['data_arrivo']) ?>">
                                    <?= date('d/m/Y', strtotime($gatto['data_arrivo'])) ?>
                                </time>
                            </dd>
                        </dl>

                        <a href="gatti.php" class="btn btn-secondario"
                           aria-label="Scopri di più su <?= esc($gatto['nome']) ?>">
                            Scopri di più
                        </a>
                    </section>

                </article>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<hr class="separatore">

<section aria-labelledby="titolo-faq-home">
    <h2 id="titolo-faq-home">Domande frequenti</h2>
    <details>
        <summary>Posso adottare anche se vivo in appartamento?</summary>
        <p>Assolutamente sì. Molti dei nostri gatti si adattano perfettamente alla vita in appartamento.</p>
    </details>
    <details>
        <summary>Quanto costa adottare un gatto?</summary>
        <p>L'adozione è gratuita. Chiediamo solo disponibilità e amore per l'animale.</p>
    </details>
    <details>
        <summary>Come posso diventare volontario?</summary>
        <p>
            <a href="registrazione.php">Registrati</a>, poi accedi alla pagina
            <a href="volontariato.php">Volontariato</a> e scegli le fasce orarie.
        </p>
    </details>
    <p><a href="faq.php" class="btn btn-secondario">Tutte le domande frequenti</a></p>
</section>

<?php chiudiMain(); stampaFooter(); ?>
