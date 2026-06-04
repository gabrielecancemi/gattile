<?php
/**
 * Funzioni per header e footer comuni a tutte le pagine.
 * Includere questo file nelle pagine PHP prima dell'output HTML.
 */
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

/**
 * Stampa l'<head> HTML con tutti i meta necessari.
 *
 * @param string $titolo     Titolo della pagina (senza suffisso)
 * @param string $descrizione Meta description
 * @param string $canonical  URL canonico relativo
 */
function stampaTesta(string $titolo, string $descrizione, string $canonical = ''): void
{
    $titoloPagina = htmlspecialchars($titolo) . ' — Gattile San Paolo';
    $descSafe     = htmlspecialchars($descrizione);
    $base         = 'https://gattile-San Paolo.example.it/'; // adattare in produzione
    $canonUrl     = $canonical ? $base . ltrim($canonical, '/') : $base;
    echo <<<HTML
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$titoloPagina}</title>
    <meta name="description" content="{$descSafe}">
    <meta name="keywords" content="gattile, adozione gatti, volontariato, felini, Torino">
    <meta name="author" content="Gattile San Paolo">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{$canonUrl}">
    <link rel="icon" href="img/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="css/stile.css">
    <link rel="stylesheet" href="css/stampa.css" media="print">
</head>
HTML;
}

/**
 * Stampa l'header con navigazione principale.
 * Mostra username in alto a destra se loggato.
 */
function stampaHeader(): void
{
    $utente   = utenteLoggato();
    $username = $utente ? htmlspecialchars($utente['username']) : null;
    $nomeDisplay = $username ?? 'non loggato';

    // Link attivo: determina pagina corrente
    $paginaCorrente = basename($_SERVER['PHP_SELF']);
    $navLinks = [
        'index.php'       => 'Home',
        'gatti.php'       => 'Adotta un gatto',
        'volontariato.php'=> 'Volontariato',
    ];
    if ($utente && (bool)$utente['is_admin']) {
        $navLinks['inserisci_gatto.php'] = 'Inserisci gatto';
    }

    echo '<header class="sito-header" role="banner">';
    echo '<a href="#contenuto-principale" class="skip-link">Vai al contenuto principale</a>';
    echo '<figure class="logo-container">';
    echo '<img src="img/logo.svg" alt="Logo Gattile San Paolo — una zampa di gatto stilizzata" width="60" height="60">';
    echo '<figcaption class="logo-testo"><strong>Gattile San Paolo</strong></figcaption>';
    echo '</figure>';

    // Stato utente in alto a destra
    echo '<aside class="stato-utente" aria-label="Stato autenticazione">';
    if ($utente) {
        echo '<span class="badge-utente" aria-live="polite">Ciao, <strong>' . htmlspecialchars($utente['nome']) . '</strong> (' . $nomeDisplay . ')</span>';
        echo ' <a href="logout.php" class="link-logout">Esci</a>';
    } else {
        echo '<span class="badge-utente">non loggato</span>';
        echo ' <a href="login.php" class="link-login">Accedi</a>';
        echo ' <a href="registrazione.php" class="link-registra">Registrati</a>';
    }
    echo '</aside>';

    echo '<nav class="navigazione-principale" aria-label="Navigazione principale">';
    echo '<ul role="list">';
    foreach ($navLinks as $href => $etichetta) {
        $aria    = ($href === $paginaCorrente) ? ' aria-current="page"' : '';
        $classAtt = ($href === $paginaCorrente) ? ' class="link-attivo"' : '';
        echo "<li><a href=\"{$href}\"{$classAtt}{$aria}>{$etichetta}</a></li>";
    }
    echo '</ul>';
    echo '</nav>';
    echo '</header>';
}

/**
 * Apre il <main> con id per skip-link.
 */
function apriMain(): void
{
    echo '<main id="contenuto-principale" tabindex="-1">';
}

/**
 * Chiude il <main>.
 */
function chiudiMain(): void
{
    echo '</main>';
}

/**
 * Stampa il footer con informazioni legali e privacy.
 */
function stampaFooter(): void
{
    $anno = date('Y');
    echo <<<HTML
    <footer class="sito-footer" role="contentinfo">
        <nav aria-label="Link footer">
            <ul role="list">
                <li><a href="privacy.php">Privacy e Cookie</a></li>
                <li><a href="index.php">Home</a></li>
                <li><a href="gatti.php">Adozioni</a></li>
                <li><a href="volontariato.php">Volontariato</a></li>
            </ul>
        </nav>
        <p>© {$anno} Gattile San Paolo — Tutti i diritti riservati.</p>
        <p>
            <a href="privacy.php">Informativa sulla privacy</a> — 
            <button type="button" id="btn-elimina-cookie" class="link-button">Elimina i miei cookie</button>
        </p>
        <p class="footer-url" aria-hidden="true">Stai visitando: <strong id="footer-url-corrente"></strong></p>
    </footer>
    <script>
        // Mostra URL corrente nel footer (utile anche per stampa via JS)
        document.getElementById('footer-url-corrente').textContent = window.location.href;

        // Pulsante elimina cookie: cancella tutti i cookie del dominio e reindirizza a privacy.php
        document.getElementById('btn-elimina-cookie').addEventListener('click', function() {
            if (confirm('Sei sicuro di voler eliminare tutti i cookie? Verrai disconnesso.')) {
                fetch('api/elimina_cookie.php', { method: 'POST', credentials: 'same-origin' })
                    .then(r => r.json())
                    .then(() => { window.location.href = 'privacy.php?eliminati=1'; })
                    .catch(() => { window.location.href = 'privacy.php?eliminati=1'; });
            }
        });
    </script>
HTML;
}

/**
 * Chiude il documento HTML.
 */
function chiudiHTML(): void
{
    echo '</body>';
    echo '</html>';
}

/**
 * Banner consenso cookie — mostrato solo se non già accettato.
 */
function stampaBannerCookie(): void
{
    if (!isset($_COOKIE['cookie_consenso'])) {
        echo <<<HTML
        <aside id="banner-cookie" class="banner-cookie" role="dialog" aria-live="polite" aria-label="Informativa cookie">
            <p>Questo sito utilizza cookie tecnici di sessione necessari al funzionamento. 
            Nessun cookie di profilazione viene utilizzato. 
            <a href="privacy.php">Maggiori informazioni</a>.</p>
            <nav aria-label="Scelte consenso cookie">
                <button type="button" id="btn-accetta-cookie" class="btn btn-primario">Accetto</button>
                <a href="privacy.php#elimina" class="btn btn-secondario">Gestisci</a>
            </nav>
        </aside>
        <script>
            document.getElementById('btn-accetta-cookie').addEventListener('click', function() {
                // Cookie consenso: scade dopo 1 anno
                const scadenza = new Date(Date.now() + 365 * 24 * 3600 * 1000).toUTCString();
                document.cookie = 'cookie_consenso=1; expires=' + scadenza + '; path=/; SameSite=Strict';
                document.getElementById('banner-cookie').hidden = true;
            });
        </script>
HTML;
    }
}

/**
 * Funzione di escape HTML sicura — da usare sempre per output utente.
 */
function esc(mixed $val): string
{
    return htmlspecialchars((string)$val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Restituisce un messaggio di errore formattato per l'utente.
 * $tipo: 'errore' | 'successo' | 'avviso'
 */
function messaggioUtente(string $testo, string $tipo = 'errore'): string
{
    $icone = ['errore' => '⚠', 'successo' => '✓', 'avviso' => 'ℹ'];
    $icona = $icone[$tipo] ?? '•';
    $ruolo = ($tipo === 'errore') ? 'alert' : 'status';
    $testoSafe = esc($testo);
    return "<output class=\"messaggio messaggio-{$tipo}\" role=\"{$ruolo}\" aria-live=\"assertive\"><span aria-hidden=\"true\">{$icona}</span> {$testoSafe}</output>";
}
