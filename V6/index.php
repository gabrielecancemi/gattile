<?php
/**
 * index.php — Home page di Gattile San Paolo.
 * Ultimi 2 gatti dal DB, sezioni informative, FAQ accordion.
 * MySQLi + prepared statement, nessuna eccezione.
 */
declare(strict_types=1);

require_once 'includes/layout.php';
require_once 'includes/db.php';

avviaSessione();

$erroreDB = null;
$nuoviArrivi = [];

$conn = getDB('reader');
if (!$conn) {
    $erroreDB = 'Impossibile connettersi al database. Riprova tra qualche minuto.';
} else {
    $stm = mysqli_prepare(
        $conn,
        'SELECT id, nome, descrizione, peso, eta, sesso,
                colore_mantello, lunghezza_pelo, razza, colore_occhi, data_arrivo
         FROM gatti
         ORDER BY data_arrivo DESC
         LIMIT 2'
    );

    if (!$stm) {
        error_log('[index] Prepare arrivi fallita: ' . mysqli_error($conn));
        $erroreDB = 'Impossibile caricare i nuovi arrivi. Riprova tra qualche minuto.';
    } else {
        if (!mysqli_stmt_execute($stm)) {
            error_log('[index] Execute arrivi fallita: ' . mysqli_stmt_error($stm));
            $erroreDB = 'Impossibile caricare i nuovi arrivi. Riprova tra qualche minuto.';
        } else {
            $result = mysqli_stmt_get_result($stm);
            while ($row = mysqli_fetch_assoc($result)) {
                $nuoviArrivi[] = $row;
            }
        }
        mysqli_stmt_close($stm);
    }

    $statistiche = [
        'gatti' => 0,
        'visite' => 0,
        'volontari' => 0,
        'arrivi' => 0
    ];

    $sql = '
        SELECT
            (SELECT COUNT(*) FROM gatti) AS totale_gatti,
            (SELECT COUNT(*) FROM visita_gatti) AS totale_visite,
            (SELECT COUNT(DISTINCT utente_id) FROM turni_volontariato) AS totale_volontari,
            (SELECT COUNT(*) FROM gatti WHERE YEAR(data_arrivo) = YEAR(CURDATE())) AS nuovi_arrivi
    ';

    $stm = mysqli_prepare($conn, $sql);

    if (!$stm) {
        error_log('[index] Prepare statistiche fallita: ' . mysqli_error($conn));
    } else {
        if (!mysqli_stmt_execute($stm)) {
            error_log('[index] Execute statistiche fallita: ' . mysqli_stmt_error($stm));
        } else {
            $result = mysqli_stmt_get_result($stm);
            if ($result !== false) {
                $row = mysqli_fetch_assoc($result);
                if ($row) {
                    $statistiche['gatti'] = (int) $row['totale_gatti'];
                    $statistiche['visite'] = (int) $row['totale_visite'];
                    $statistiche['volontari'] = (int) $row['totale_volontari'];
                    $statistiche['arrivi'] = (int) $row['nuovi_arrivi'];
                }
            }
        }
        mysqli_stmt_close($stm);
    }
    mysqli_close($conn);
}


stampaTesta(
    'Home',
    'Gattile San Paolo: adotta un gatto o diventa volontario a Torino. Scopri i nostri ospiti felini.'
);
stampaHeader();
apriMain();
?>

<section aria-labelledby="titolo-home">
    <h1 id="titolo-home">
        Una casa, una famiglia, una seconda possibilità
    </h1>

    <p>
        Ogni gatto che arriva al Gattile San Paolo ha una storia.
        Alcuni sono stati abbandonati, altri recuperati dalla strada,
        altri ancora hanno semplicemente bisogno di una nuova famiglia.
    </p>

    <p>
        Aiutaci a trasformare un incontro in un'adozione e una visita
        in un nuovo inizio.
    </p>
</section>

<section aria-labelledby="titolo-perche">
    <h2 id="titolo-perche">Perché adottare dal Gattile San Paolo?</h2>

    <ul>
        <li>
            <strong>Controlli veterinari</strong><br>
            Tutti i gatti vengono seguiti e monitorati prima dell'adozione.
        </li>

        <li>
            <strong>Supporto all'adozione</strong><br>
            Ti aiutiamo a trovare il gatto più adatto alla tua situazione.
        </li>

        <li>
            <strong>Volontari qualificati</strong><br>
            Ogni giorno persone dedicate si prendono cura dei nostri ospiti.
        </li>
    </ul>
</section>

<section aria-labelledby="titolo-impatto">
    <h2 id="titolo-impatto">Il nostro impatto</h2>

    <dl class="statistiche">
        <dt>Gatti ospitati</dt>
        <dd><?= $statistiche['gatti'] ?></dd>

        <dt>Incontri organizzati</dt>
        <dd><?= $statistiche['visite'] ?></dd>

        <dt>Volontari attivi</dt>
        <dd><?= $statistiche['volontari'] ?></dd>

        <dt>Nuovi arrivi quest'anno</dt>
        <dd><?= $statistiche['arrivi'] ?></dd>
    </dl>
</section>

<section aria-labelledby="titolo-adozione">
    <h2 id="titolo-adozione">Adottare è semplice</h2>

    <ol>
        <li>
            Consulta i profili dei gatti disponibili.
        </li>

        <li>
            Registrati gratuitamente sul sito.
        </li>

        <li>
            Prenota una visita conoscitiva.
        </li>

        <li>
            Conosci il gatto e completa il percorso di adozione.
        </li>
    </ol>
</section>

<aside aria-labelledby="titolo-testimonianza">
    <h2 id="titolo-testimonianza">Una storia di successo</h2>

    <blockquote>
        "Pensavamo di adottare un gatto.
        In realtà abbiamo trovato un nuovo membro della famiglia."
    </blockquote>

    <p>
        — Famiglia Rossi, Torino
    </p>
</aside>

<section aria-labelledby="titolo-aiuta">
    <h2 id="titolo-aiuta">Non puoi adottare?</h2>

    <p>
        Puoi comunque fare la differenza dedicando qualche ora del tuo tempo
        ai nostri ospiti.
    </p>

    <p>
        Ogni volontario contribuisce a migliorare la qualità della vita
        dei gatti accolti nella struttura.
    </p>

    <p>
        <a href="volontariato.php" class="btn btn-primario">
            Scopri il volontariato
        </a>
    </p>
</section>

<!-- ── Come funziona ──────────────────────────────────────── -->
<section aria-labelledby="titolo-come-funziona">
    <h2 id="titolo-come-funziona">Come funziona</h2>
    <ol>
        <li>
            <strong>Sfoglia i gatti</strong> disponibili nell'<a href="gatti.php">area adozioni</a>:
            puoi filtrare per nome, descrizione, età o colore del manto.
        </li>
        <li>
            <strong>Registrati o accedi</strong> al tuo profilo per selezionare
            i gatti di cui vorresti sapere di più.
        </li>
        <li>
            <strong>Prenota una visita</strong> conoscitiva direttamente dal sito,
            indicando la data e l'ora che preferisci.
        </li>
        <li>
            In alternativa, puoi <strong>diventare volontario</strong> e scegliere
            le fasce orarie in cui prestare il tuo aiuto.
        </li>
    </ol>
</section>

<!-- ── Nuovi arrivi ───────────────────────────────────────── -->
<section class="nuovi-arrivi" aria-labelledby="titolo-nuovi-arrivi">
    <h2 id="titolo-nuovi-arrivi">🆕 Nuovi arrivi</h2>
    <p>Gli ultimi ospiti entrati nella struttura che aspettano una famiglia:</p>

    <?php if ($erroreDB): ?>

        <?= messaggioUtente($erroreDB, 'errore') ?>

    <?php elseif (empty($nuoviArrivi)): ?>

        <p>Nessun gatto registrato al momento. Torna presto!</p>

    <?php else: ?>

        <ul class="griglia-gatti" aria-label="Nuovi arrivi">
            <?php foreach ($nuoviArrivi as $gatto):
                $sesso = $gatto['sesso'] === 'M' ? 'Maschio' : 'Femmina';
                $etaMesi = (int) $gatto['eta'];
                $etaTesto = $etaMesi < 12
                    ? $etaMesi . ' ' . ($etaMesi === 1 ? 'mese' : 'mesi')
                    : floor($etaMesi / 12) . ' ' . (floor($etaMesi / 12) === 1 ? 'anno' : 'anni');
                ?>
                <li>
                    <article class="card-gatto" aria-labelledby="nuovo-<?= (int) $gatto['id'] ?>">
                        <figure>
                            <img src="img/placeholder-gatto.svg"
                                alt="Sagoma stilizzata — foto di <?= esc($gatto['nome']) ?> non ancora disponibile" width="320"
                                height="240" loading="lazy">
                            <figcaption class="sr-solo">Placeholder foto per <?= esc($gatto['nome']) ?></figcaption>
                        </figure>
                        <h3 id="nuovo-<?= (int) $gatto['id'] ?>"><?= esc($gatto['nome']) ?>
                            <dfn class="badge-nuovo">Nuovo</dfn>
                        </h3>
                            
                            <ul class="card-gatto-meta" aria-label="Caratteristiche principali">
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
                                    <data value="<?= esc((string) $gatto['peso']) ?>">
                                        <?= esc((string) $gatto['peso']) ?> kg
                                    </data>
                                    <meter min="0" max="10" low="1" high="7" optimum="4"
                                        value="<?= esc((string) $gatto['peso']) ?>"
                                        aria-label="Peso di <?= esc($gatto['nome']) ?>: <?= esc((string) $gatto['peso']) ?> kg"
                                        title="Peso: <?= esc((string) $gatto['peso']) ?> kg"></meter>
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

                            <a href="gatti.php" class="btn btn-primario"
                                aria-label="Scopri di più su <?= esc($gatto['nome']) ?>">
                                Scopri di più
                            </a>

                    </article>
                </li>
            <?php endforeach; ?>
        </ul>

    <?php endif; ?>
</section>

<!-- ── FAQ ────────────────────────────────────────────────── -->
<section aria-labelledby="titolo-faq">
    <h2 id="titolo-faq">Domande frequenti</h2>

    <details>
        <summary>Posso adottare anche se vivo in appartamento?</summary>
        <p>
            Assolutamente sì. Molti dei nostri gatti sono nati in ambienti chiusi e si
            adattano perfettamente alla vita in appartamento, purché abbiano spazi per
            giocare e qualcuno che li ami.
        </p>
    </details>

    <details>
        <summary>Quanto costa adottare un gatto?</summary>
        <p>
            L'adozione è gratuita. Chiediamo solo la disponibilità a prendersi cura
            dell'animale e a sostenere le spese veterinarie ordinarie.
        </p>
    </details>

    <details>
        <summary>Come posso diventare volontario?</summary>
        <p>
            <a href="registrazione.php">Registrati al sito</a>, poi accedi alla pagina
            <a href="volontariato.php">Volontariato</a> e scegli le fasce orarie in cui
            desideri prestare servizio. La struttura accoglie fino a due volontari per fascia.
        </p>
    </details>

    <p><a href="faq.php" class="btn btn-primario">Tutte le domande frequenti</a></p>
</section>

<?php chiudiMain();
stampaFooter(); ?>