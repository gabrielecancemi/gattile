<?php
// Visualizzazione condivisa della scheda gatto.

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

// Trasformazioni HTML delle schede gatto.
function costruisciSchedaGatto(array $gatto, array $opzioni = []): string
{
    $nuovo = !empty($opzioni['nuovo']);
    $id = (int) ($gatto['id'] ?? 0);
    $sesso = ($gatto['sesso'] ?? 'F') === 'M' ? 'Maschio' : 'Femmina';
    $eta = etaInParole((int) ($gatto['eta'] ?? 0));
    $immagine = (isset($gatto['foto']) && trim((string) $gatto['foto']) !== '')
        ? $gatto['foto']
        : 'img/placeholder-gatto.jpg';
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
    // Conversione della data da AAAA-MM-GG a GG/MM/AAAA.
    $data_it = '';
    if (!empty($gatto['data_arrivo'])) {
        $parti_data = explode('-', substr((string) $gatto['data_arrivo'], 0, 10));
        if (count($parti_data) === 3) {
            $data_it = $parti_data[2] . '/' . $parti_data[1] . '/' . $parti_data[0];
        }
    }
    $badge = $nuovo
        ? ' <strong class="badge-nuovo">Nuovo</strong>'
        : '';
    return <<<HTML
<li>
<article class="card-gatto" aria-labelledby="{$id_titolo}">
    <!-- Nel caso in cui l'immagine non fosse supportata dal browser -->
    <picture>
        <source srcset="{$immagine_pulita}">
        <img src="img/placeholder-gatto.jpg" alt="Placeholder di {$nome}" loading="lazy" decoding="async" class="foto-gatto">
    </picture>
    <section class="card-gatto-corpo">
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
    </section>
</article>
</li>
HTML;
}
