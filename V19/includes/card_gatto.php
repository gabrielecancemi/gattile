<?php
// Renderer condiviso della scheda gatto.
//
// Un'unica funzione produce il markup della scheda, usata sia nei "Nuovi
// arrivi" della home sia, a livello di struttura/CSS, dalle schede generate
// dal componente React in gatti.php. Una sola fonte di verità per markup e
// classi: niente CSS duplicato e niente query ripetute (la home prende solo
// gli ultimi 2 arrivi, la pagina adozioni usa l'endpoint JSON).
//
// strict_types e ripulisci() arrivano dal contesto chiamante (layout.php).

if (!function_exists('etaInParole')) {
    // Converte un'età in mesi in testo leggibile (es. "1 anno e 3 mesi").
    function etaInParole(int $mesi): string
    {
        if ($mesi < 12) {
            return $mesi . ' ' . ($mesi === 1 ? 'mese' : 'mesi');
        }
        $anni = intdiv($mesi, 12);
        $resto = $mesi % 12;
        $testo = $anni . ' ' . ($anni === 1 ? 'anno' : 'anni');
        if ($resto > 0) {
            $testo .= ' e ' . $resto . ' ' . ($resto === 1 ? 'mese' : 'mesi');
        }
        return $testo;
    }
}

if (!function_exists('costruisciSchedaGatto')) {
    // Markup HTML di una scheda gatto.
    // $gatto: riga del DB. $opzioni: ['nuovo' => bool] per il badge "Nuovo".
    function costruisciSchedaGatto(array $gatto, array $opzioni = []): string
    {
        $nuovo = !empty($opzioni['nuovo']);
        $id = (int) ($gatto['id'] ?? 0);
        $sesso = ($gatto['sesso'] ?? 'F') === 'M' ? 'Maschio' : 'Femmina';
        $eta = etaInParole((int) ($gatto['eta'] ?? 0));
        $immagine = (isset($gatto['foto']) && trim((string) $gatto['foto']) !== '')
            ? $gatto['foto']
            : 'img/placeholder-gatto.svg';

        $id_titolo = 'gatto-' . $id;
        $nome = ripulisci($gatto['nome'] ?? '');
        $descrizione = ripulisci($gatto['descrizione'] ?? '');
        $mantello = ripulisci($gatto['colore_mantello'] ?? '');
        $pelo = ripulisci($gatto['lunghezza_pelo'] ?? '');
        $razza = ripulisci($gatto['razza'] ?? '');
        $occhi = ripulisci($gatto['colore_occhi'] ?? '');
        $peso = ripulisci((string) ($gatto['peso'] ?? ''));
        $immagine_pulita = ripulisci($immagine);
        $data_iso = ripulisci($gatto['data_arrivo'] ?? '');
        $data_it = !empty($gatto['data_arrivo'])
            ? date('d/m/Y', (int) strtotime((string) $gatto['data_arrivo']))
            : '';

        $badge = $nuovo
            ? ' <strong class="badge-nuovo">Nuovo</strong>'
            : '';

        // width/height espliciti per riservare lo spazio ed evitare layout shift (CLS).
        return <<<HTML
<li>
    <article class="card-gatto" aria-labelledby="{$id_titolo}">
        <figure>
            <img src="{$immagine_pulita}"
                 alt="Sagoma stilizzata — foto di {$nome} non ancora disponibile"
                 width="320" height="240" loading="lazy" decoding="async">
            <figcaption class="sr-solo">Placeholder foto per {$nome}</figcaption>
        </figure>
        <div class="card-gatto-corpo">
            <h3 id="{$id_titolo}">{$nome}{$badge}</h3>
            <ul class="card-gatto-meta" aria-label="Caratteristiche principali">
                <li class="tag">{$sesso}</li>
                <li class="tag">{$eta}</li>
                <li class="tag">{$mantello}</li>
                <li class="tag">{$pelo}</li>
                <li class="tag">{$razza}</li>
            </ul>
            <p class="card-gatto-descr">{$descrizione}</p>
            <dl>
                <dt>Peso</dt>
                <dd><data value="{$peso}">{$peso} kg</data></dd>
                <dt>Occhi</dt>
                <dd>{$occhi}</dd>
                <dt>Arrivato il</dt>
                <dd><time datetime="{$data_iso}">{$data_it}</time></dd>
            </dl>
            <a href="gatti.php" class="btn btn-primario" aria-label="Vai alla pagina adozioni per {$nome}">
                Adotta
            </a>
        </div>
    </article>
</li>
HTML;
    }
}
