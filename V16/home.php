<?php
// Home: ultimi 2 gatti dal DB, sezioni informative, accordion FAQ.
declare(strict_types=1);

require_once 'includes/layout.php';
require_once 'includes/db.php';
require_once 'includes/card_gatto.php';

aprireSessione();

$errore_db = null;
$errore_statistiche = null;
$nuovi_arrivi = [];
$statistiche = [
    'gatti' => 0,
    'visite' => 0,
    'volontari' => 0,
    'arrivi' => 0,
];

$conn = connessioneDb('reader');
if (!$conn) {
    $errore_db = 'Impossibile connettersi al database. Riprova tra qualche minuto.';
    $errore_statistiche = 'Statistiche non disponibili al momento.';
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
        error_log('[index] prepare arrivi fallita: ' . mysqli_error($conn));
        $errore_db = 'Impossibile caricare i nuovi arrivi. Riprova tra qualche minuto.';
    } else {
        if (!mysqli_stmt_execute($stm)) {
            error_log('[index] execute arrivi fallita: ' . mysqli_stmt_error($stm));
            $errore_db = 'Impossibile caricare i nuovi arrivi. Riprova tra qualche minuto.';
        } else {
            $risultato = mysqli_stmt_get_result($stm);
            while ($riga = mysqli_fetch_assoc($risultato)) {
                $nuovi_arrivi[] = $riga;
            }
        }
        mysqli_stmt_close($stm);
    }

    $sql = '
        SELECT
            (SELECT COUNT(*) FROM gatti) AS totale_gatti,
            (SELECT COUNT(*) FROM visita_gatti) AS totale_visite,
            (SELECT COUNT(DISTINCT utente_id) FROM turni_volontariato) AS totale_volontari,
            (SELECT COUNT(*) FROM gatti WHERE YEAR(data_arrivo) = YEAR(CURDATE())) AS nuovi_arrivi
    ';

    $stm = mysqli_prepare($conn, $sql);

    if (!$stm) {
        error_log('[index] prepare statistiche fallita: ' . mysqli_error($conn));
        $errore_statistiche = 'Statistiche non disponibili al momento.';
    } else {
        if (!mysqli_stmt_execute($stm)) {
            error_log('[index] execute statistiche fallita: ' . mysqli_stmt_error($stm));
            $errore_statistiche = 'Statistiche non disponibili al momento.';
        } else {
            $risultato = mysqli_stmt_get_result($stm);
            if ($risultato !== false) {
                $riga = mysqli_fetch_assoc($risultato);
                if ($riga) {
                    $statistiche['gatti'] = (int) $riga['totale_gatti'];
                    $statistiche['visite'] = (int) $riga['totale_visite'];
                    $statistiche['volontari'] = (int) $riga['totale_volontari'];
                    $statistiche['arrivi'] = (int) $riga['nuovi_arrivi'];
                } else {
                    $errore_statistiche = 'Statistiche non disponibili al momento.';
                }
            } else {
                $errore_statistiche = 'Statistiche non disponibili al momento.';
            }
        }
        mysqli_stmt_close($stm);
    }
    mysqli_close($conn);
}


generaIntestazioneHtml(
    'Home',
    'Gattile San Paolo: adotta un gatto o diventa volontario a Torino. Scopri i nostri ospiti felini.'
);
generaTestata();
aprireContenuto('main-home');
?>

<section class="zona-hero" aria-labelledby="titolo-home">
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

<section class="zona-perche" aria-labelledby="titolo-perche">
    <h2 id="titolo-perche">Perché adottare dal Gattile San Paolo?</h2>

    <dl class="griglia-vantaggi">
        <div class="box-vantaggio">
            <dt>Controlli veterinari</dt>
            <dd>Tutti i gatti vengono seguiti e monitorati prima dell'adozione.</dd>
        </div>
        <div class="box-vantaggio">
            <dt>Supporto all'adozione</dt>
            <dd>Ti aiutiamo a trovare il gatto più adatto alla tua situazione.</dd>
        </div>
        <div class="box-vantaggio">
            <dt>Volontari qualificati</dt>
            <dd>Ogni giorno persone dedicate si prendono cura dei nostri ospiti.</dd>
        </div>
    </dl>
</section>

<section class="zona-impatto" aria-labelledby="titolo-impatto">
    <h2 id="titolo-impatto">Il nostro impatto</h2>

    <?php if ($errore_statistiche): ?>
        <?= avvisoUtente($errore_statistiche, 'errore') ?>
    <?php else: ?>
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
    <?php endif; ?>
</section>

<section class="come-funziona zona-come" aria-labelledby="titolo-come-funziona">
    <h2 id="titolo-come-funziona">Come funziona</h2>
    <ol>
        <li>
            <strong>Sfoglia i gatti</strong> disponibili nell'area adozioni:
            puoi filtrare per nome, descrizione, età o colore del manto.
            <a href="gatti.php" class="btn btn-primario">Sfoglia i gatti</a>
        </li>
        <li>
            <strong>Registrati o accedi</strong> al tuo profilo per selezionare
            i gatti di cui vorresti sapere di più.
            <a href="registrazione.php" class="btn btn-primario">Registrati</a>
        </li>
        <li>
            <strong>Prenota una visita</strong> conoscitiva direttamente dal sito,
            indicando la data e l'ora che preferisci.
            <a href="gatti.php" class="btn btn-primario">Prenota una visita</a>
        </li>
        <li>
            In alternativa, puoi <strong>diventare volontario</strong> e scegliere
            le fasce orarie in cui prestare il tuo aiuto.
            <a href="volontariato.php" class="btn btn-primario">Diventa volontario</a>
        </li>
    </ol>
</section>

<aside class="zona-storia" aria-labelledby="titolo-testimonianza">
    <h2 id="titolo-testimonianza">Storie di successo</h2>

    <figure class="storia-successo">
        <blockquote>
            "Pensavamo di adottare un gatto.
            In realtà abbiamo trovato un nuovo membro della famiglia."
        </blockquote>
        <figcaption>— Famiglia Rossi, Torino</figcaption>
    </figure>

    <figure class="storia-successo">
        <blockquote>
            "Luna era timidissima quando è arrivata. Dopo qualche settimana
            di pazienza ha iniziato a fidarsi: oggi dorme sul divano come se
            fosse sempre stata a casa nostra."
        </blockquote>
        <figcaption>— Marco e Giulia, Moncalieri</figcaption>
    </figure>

    <figure class="storia-successo">
        <blockquote>
            "Per me è diventato come un figlio, non riuscirei più a vivere senza Pippo insieme a me."
        </blockquote>
        <figcaption>— Gabriele, Torino</figcaption>
    </figure>
</aside>

<section class="nuovi-arrivi zona-arrivi" aria-labelledby="titolo-nuovi-arrivi">
    <h2 id="titolo-nuovi-arrivi">Nuovi arrivi</h2>
    <p>Gli ultimi ospiti entrati nella struttura che aspettano una famiglia:</p>

    <?php if ($errore_db): ?>
        <?= avvisoUtente($errore_db, 'errore') ?>
    <?php elseif (empty($nuovi_arrivi)): ?>
        <p>Nessun gatto registrato al momento. Torna presto!</p>
    <?php else: ?>
        <ul class="griglia-gatti" aria-label="Nuovi arrivi">
            <?php foreach ($nuovi_arrivi as $gatto): ?>
                <?= costruisciSchedaGatto($gatto, ['nuovo' => true]) ?>
            <?php endforeach; ?>
        </ul>

    <?php endif; ?>
</section>

<section class="zona-aiuta" aria-labelledby="titolo-aiuta">
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

<section class="zona-faq" aria-labelledby="titolo-faq">
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

<?php chiudereContenuto();
generaPiePagina(); ?>