<?php
/**
 * privacy.php — Informativa privacy e gestione cookie.
 */
declare(strict_types=1);

require_once 'includes/layout.php';

avviaSessione();
$eliminati = isset($_GET['eliminati']) && $_GET['eliminati'] === '1';

stampaTesta('Privacy e Cookie', 'Informativa sulla privacy e gestione dei cookie del sito Gattile San Paolo.', 'privacy.php');
stampaHeader();
apriMain();
?>

<section aria-labelledby="titolo-privacy">
    <h1 id="titolo-privacy">Informativa sulla Privacy e Cookie</h1>
    <p><time datetime="<?= date('Y-m-d') ?>">Aggiornata il <?= date('d/m/Y') ?></time></p>

    <?php if ($eliminati): echo messaggioUtente('I tuoi cookie sono stati eliminati con successo.', 'successo'); endif; ?>

    <article aria-labelledby="sez-titolare">
        <h2 id="sez-titolare">Titolare del trattamento</h2>
        <p>
            <strong>Gattile San Paolo</strong><br>
            Via Felina 1, 10100 Torino (TO)<br>
            Email: <a href="mailto:privacy@gattile-San Paolo.example.it">privacy@gattile-San Paolo.example.it</a>
        </p>
    </article>

    <article aria-labelledby="sez-cookie">
        <h2 id="sez-cookie">Cookie utilizzati</h2>
        <p>Questo sito usa <strong>esclusivamente cookie tecnici</strong>. Nessuna profilazione.</p>
        <table>
            <caption>Elenco cookie del sito</caption>
            <thead>
                <tr>
                    <th scope="col">Nome</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Durata</th>
                    <th scope="col">Finalità</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>PHPSESSID</code></td>
                    <td>Sessione (tecnico)</td>
                    <td>Sessione browser</td>
                    <td>Gestione della sessione autenticata</td>
                </tr>
                <tr>
                    <td><code>gattile_remember</code></td>
                    <td>Persistente (tecnico)</td>
                    <td>72 ore</td>
                    <td>Token opaco per precompilare lo username al login. <strong>Niente credenziali in chiaro.</strong></td>
                </tr>
                <tr>
                    <td><code>cookie_consenso</code></td>
                    <td>Persistente (tecnico)</td>
                    <td>1 anno</td>
                    <td>Memorizza che l'utente ha letto l'informativa</td>
                </tr>
            </tbody>
            <tfoot>
                <tr><td colspan="4">Nessun cookie di profilazione o di terze parti.</td></tr>
            </tfoot>
        </table>
    </article>

    <article aria-labelledby="sez-dati">
        <h2 id="sez-dati">Dati personali raccolti</h2>
        <p>
            In fase di registrazione raccogliamo nome, cognome, indirizzo e credenziali.
            La password è conservata cifrata con <abbr title="Bcrypt, algoritmo di hashing sicuro">Bcrypt</abbr>
            e non è mai leggibile. I dati non vengono ceduti a terzi.
        </p>
    </article>

    <article aria-labelledby="sez-elimina" id="elimina">
        <h2 id="sez-elimina">Elimina i tuoi cookie</h2>
        <p>Rimuovi tutti i cookie impostati da questo sito. Verrai disconnesso.</p>
        <button type="button" id="btn-elimina-cookie-privacy" class="btn btn-pericolo"
                aria-describedby="nota-elimina">
            Elimina tutti i miei cookie
        </button>
        <p id="nota-elimina" class="aiuto-campo">
            Verrai reindirizzato a questa pagina con conferma dell'avvenuta eliminazione.
        </p>
    </article>
</section>

<script>
document.getElementById('btn-elimina-cookie-privacy').addEventListener('click', function () {
    if (confirm('Confermi l\'eliminazione di tutti i cookie? Verrai disconnesso.')) {
        fetch('api/elimina_cookie.php', { method: 'POST', credentials: 'same-origin' })
            .then(r => r.json())
            .then(() => { window.location.href = 'privacy.php?eliminati=1'; })
            .catch(() => { window.location.href = 'privacy.php?eliminati=1'; });
    }
});
</script>

<?php chiudiMain(); stampaFooter(); ?>
