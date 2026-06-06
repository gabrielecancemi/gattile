<?php
/**
 * card_gatto.php — Renderer condiviso della "card gatto".
 *
 * Un'unica funzione produce il markup della card, usata sia nella sezione
 * "Nuovi arrivi" della home (index.php) sia — a livello di struttura/CSS —
 * dalle card generate dal componente React in gatti.php.
 *
 * Vantaggi:
 *  - una sola fonte di verità per la struttura HTML e le classi CSS della card
 *    (DRY: Don't Repeat Yourself);
 *  - le due pagine NON ripetono la stessa query: la home estrae solo gli
 *    ultimi 2 arrivi, la pagina adotta usa l'endpoint JSON; entrambe però
 *    riusano lo stesso identico renderer e lo stesso stile.
 *
 * NOTA: declare(strict_types=1) e esc() sono forniti dal contesto chiamante
 * (layout.php definisce esc()).
 */

if (!function_exists('formattaEtaGatto')) {
    /** Converte un'eta' in mesi in testo leggibile (es. "1 anno e 3 mesi"). */
    function formattaEtaGatto(int $mesi): string
    {
        if ($mesi < 12) {
            return $mesi . ' ' . ($mesi === 1 ? 'mese' : 'mesi');
        }
        $anni  = intdiv($mesi, 12);
        $resto = $mesi % 12;
        $testo = $anni . ' ' . ($anni === 1 ? 'anno' : 'anni');
        if ($resto > 0) {
            $testo .= ' e ' . $resto . ' ' . ($resto === 1 ? 'mese' : 'mesi');
        }
        return $testo;
    }
}

if (!function_exists('renderCardGatto')) {
    /**
     * Restituisce il markup HTML di una card gatto.
     *
     * @param array $gatto  Riga del DB (nome, descrizione, peso, eta, sesso,
     *                      colore_mantello, lunghezza_pelo, razza,
     *                      colore_occhi, data_arrivo, id, [foto]).
     * @param array $opts   Opzioni: ['nuovo' => bool] per il badge "Nuovo".
     */
    function renderCardGatto(array $gatto, array $opts = []): string
    {
        $nuovo  = !empty($opts['nuovo']);
        $id     = (int) ($gatto['id'] ?? 0);
        $sesso  = ($gatto['sesso'] ?? 'F') === 'M' ? 'Maschio' : 'Femmina';
        $eta    = formattaEtaGatto((int) ($gatto['eta'] ?? 0));
        $img    = (isset($gatto['foto']) && trim((string) $gatto['foto']) !== '')
            ? $gatto['foto']
            : 'img/placeholder-gatto.svg';

        $titoloId = 'gatto-' . $id;
        $nome     = esc($gatto['nome'] ?? '');
        $descr    = esc($gatto['descrizione'] ?? '');
        $mantello = esc($gatto['colore_mantello'] ?? '');
        $pelo     = esc($gatto['lunghezza_pelo'] ?? '');
        $razza    = esc($gatto['razza'] ?? '');
        $occhi    = esc($gatto['colore_occhi'] ?? '');
        $peso     = esc((string) ($gatto['peso'] ?? ''));
        $imgSafe  = esc($img);
        $dataIso  = esc($gatto['data_arrivo'] ?? '');
        $dataIt   = !empty($gatto['data_arrivo'])
            ? date('d/m/Y', (int) strtotime((string) $gatto['data_arrivo']))
            : '';

        $badge = $nuovo
            ? ' <strong class="badge-nuovo">Nuovo</strong>'
            : '';

        // Dimensioni esplicite su <img> per riservare lo spazio ed evitare
        // layout shift (CLS): il rapporto 4/3 e' garantito anche dal CSS.
        return <<<HTML
<li>
    <article class="card-gatto" aria-labelledby="{$titoloId}">
        <figure>
            <img src="{$imgSafe}"
                 alt="Sagoma stilizzata — foto di {$nome} non ancora disponibile"
                 width="320" height="240" loading="lazy" decoding="async">
            <figcaption class="sr-solo">Placeholder foto per {$nome}</figcaption>
        </figure>
        <div class="card-gatto-corpo">
            <h3 id="{$titoloId}">{$nome}{$badge}</h3>
            <ul class="card-gatto-meta" aria-label="Caratteristiche principali">
                <li class="tag">{$sesso}</li>
                <li class="tag">{$eta}</li>
                <li class="tag">{$mantello}</li>
                <li class="tag">{$pelo}</li>
                <li class="tag">{$razza}</li>
            </ul>
            <p class="card-gatto-descr">{$descr}</p>
            <dl>
                <dt>Peso</dt>
                <dd><data value="{$peso}">{$peso} kg</data></dd>
                <dt>Occhi</dt>
                <dd>{$occhi}</dd>
                <dt>Arrivato il</dt>
                <dd><time datetime="{$dataIso}">{$dataIt}</time></dd>
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
