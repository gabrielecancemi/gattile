<?php
// Home: ultimi 2 gatti dal DB, sezioni informative, accordion FAQ.

require_once 'componenti/layout.php';

aprireSessione();

// Intestazione della pagina
$titolo_pagina = 'Home';
$descrizione_pagina = 'Gattile San Paolo: adotta un gatto o diventa volontario a Torino. Scopri i nostri ospiti felini.';


?>

<!DOCTYPE html>
<html lang="it">

<?php require 'componenti/head.php'; ?>
<?php require 'componenti/header.php'; ?>
<main id="contenuto-principale" class="main-home">

    <!-- intestazione -->
    <section class="zona-intro" aria-labelledby="titolo-home">
        <h1 id="titolo-home">
            Una casa, una famiglia, una seconda possibilità
        </h1>

        <p>
            Ogni gatto che arriva al <strong>Gattile San Paolo</strong> ha una storia.
            Alcuni sono stati abbandonati, altri recuperati dalla strada,
            altri ancora hanno semplicemente bisogno di una <strong>nuova famiglia</strong>.
        </p>

        <p>
            Aiutaci a trasformare un incontro in un'adozione e una visita
            in un nuovo inizio.
        </p>
    </section>

    <!-- informazioni -->
    <section class="zona-perche" aria-labelledby="titolo-perche">
        <h2 id="titolo-perche">Perché adottare dal Gattile San Paolo?</h2>

        <dl class="griglia-vantaggi box-vantaggio">
            <dt>Controlli veterinari</dt>
            <dd>Tutti i gatti vengono seguiti e monitorati prima dell'adozione.</dd>
            <dt>Supporto all'adozione</dt>
            <dd>Ti aiutiamo a trovare il gatto più adatto alla tua situazione.</dd>
            <dt>Volontari qualificati</dt>
            <dd>Ogni giorno persone dedicate si prendono cura dei nostri ospiti.</dd>
        </dl>
    </section>

    <!-- dati numerici dal database -->
    <section class="zona-impatto" id="contenitore-statistiche" aria-busy="true" aria-labelledby="titolo-impatto">
        <h2 id="titolo-impatto">Il nostro impatto</h2>
        <p class="caricamento" id="caricamento-statistiche" aria-live="polite">Caricamento statistiche in corso…</p>
    </section>

    <!-- come funziona -->
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

    <!-- testimonianze -->
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

    <!-- nuovi arrivi -->
    <section class="nuovi-arrivi zona-arrivi" id="contenitore-arrivi" aria-busy="true"
        aria-labelledby="titolo-nuovi-arrivi">
        <h2 id="titolo-nuovi-arrivi">Nuovi arrivi</h2>
        <p>Gli ultimi ospiti entrati nella struttura che aspettano una famiglia:</p>
        <p class="caricamento" id="caricamento-arrivi" aria-live="polite">Caricamento nuovi arrivi in corso…</p>
    </section>

    <!-- volontariato -->
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

    <!-- domande più richieste -->
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
    <script src="js/home.js" defer></script>

</main>
<?php require 'componenti/footer.php'; ?>