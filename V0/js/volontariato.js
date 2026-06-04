/**
 * volontariato.js — Gestione form prenotazione turni volontariato.
 * Interroga l'API per la disponibilità, disabilita le fasce piene
 * prima ancora della submit (controllo visivo lato client).
 * Il server verifica nuovamente in modo indipendente.
 * Vanilla JS, 'use strict'.
 */
'use strict';

(function () {
    const contenitore    = document.getElementById('contenitore-turni');
    const form           = document.getElementById('form-volontariato');
    const btnVolontariato= document.getElementById('btn-volontariato');
    const msgVolontariato= document.getElementById('msg-volontariato');
    const noteBtn        = document.getElementById('note-btn-volontariato');

    if (!form || !contenitore) return; // Solo per utenti loggati

    // Fasce selezionate dall'utente
    const fasceSelezionate = new Set();

    /** Carica fasce orarie dall'API e le renderizza */
    function caricaTurni() {
        contenitore.setAttribute('aria-busy', 'true');
        contenitore.innerHTML = '<p class="caricamento">Caricamento fasce orarie in corso…</p>';

        fetch('api/turni.php', { credentials: 'same-origin' })
            .then(function (r) {
                if (!r.ok) throw new Error('Risposta server: ' + r.status);
                return r.json();
            })
            .then(function (data) {
                if (data.errore) throw new Error(data.errore);
                renderizzaTurni(data.fasce || []);
                console.log('[Volontariato] Caricate', (data.fasce || []).length, 'fasce orarie');
            })
            .catch(function (err) {
                console.error('[Volontariato] Errore caricamento turni:', err.message);
                contenitore.innerHTML = '<output class="messaggio messaggio-errore" role="alert">⚠ Impossibile caricare i turni: ' + err.message + '. Ricarica la pagina per riprovare.</output>';
            })
            .finally(function () {
                contenitore.removeAttribute('aria-busy');
            });
    }

    /** Renderizza la griglia di fasce orarie */
    function renderizzaTurni(fasce) {
        if (fasce.length === 0) {
            contenitore.innerHTML = '<p>Nessuna fascia oraria disponibile nel prossimo periodo. Riprova tra qualche giorno.</p>';
            return;
        }

        // Raggruppa per data
        const perData = {};
        fasce.forEach(function (f) {
            const data = f.fascia_oraria.slice(0, 10);
            if (!perData[data]) perData[data] = [];
            perData[data].push(f);
        });

        let html = '';

        Object.keys(perData).sort().forEach(function (data) {
            const dt = new Date(data + 'T12:00:00');
            const etichettaData = dt.toLocaleDateString('it-IT', { weekday: 'long', day: 'numeric', month: 'long' });

            html += '<section aria-labelledby="data-' + data + '">';
            html += '<h3 id="data-' + data + '">' + capitolizza(etichettaData) + '</h3>';
            html += '<ul class="griglia-turni" role="list">';

            perData[data].forEach(function (fascia) {
                const ora = fascia.fascia_oraria.slice(11, 16);
                const piena = fascia.piena;
                const id = 'turno-' + fascia.fascia_oraria.replace(/[^0-9]/g, '');

                html += '<li class="turno-item ' + (piena ? 'pieno' : 'disponibile') + '">';

                if (!piena) {
                    html += '<label>';
                    html += '<input type="checkbox" id="' + id + '" value="' + esc(fascia.fascia_oraria) + '"'
                          + ' aria-describedby="stato-' + id + '"'
                          + (fascia.piena ? ' disabled' : '') + '>';
                    html += ' ' + ora;
                    html += '</label>';
                } else {
                    // Fascia piena: non cliccabile
                    html += '<span aria-hidden="true">' + ora + '</span>';
                }

                // Indicatore disponibilità con meter
                html += '<meter min="0" max="' + fascia.max + '" value="' + fascia.iscritti + '"'
                      + ' low="1" high="2" optimum="0"'
                      + ' aria-label="Volontari iscritti per questa fascia: ' + fascia.iscritti + ' su ' + fascia.max + '"'
                      + ' title="' + fascia.iscritti + '/' + fascia.max + ' volontari"></meter>';

                html += '<p id="stato-' + id + '" class="stato-turno ' + (piena ? 'pieno' : 'libero') + '">';
                html += fascia.iscritti + '/' + fascia.max + ' ' + (piena ? '— pieno' : '— disponibile');
                html += '</p>';

                html += '</li>';
            });

            html += '</ul></section>';
        });

        contenitore.innerHTML = html;

        // Attacca listener ai checkbox
        contenitore.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                if (this.checked) {
                    fasceSelezionate.add(this.value);
                } else {
                    fasceSelezionate.delete(this.value);
                }
                aggiornaStatoPulsante();
            });
        });
    }

    /** Escape HTML minimale */
    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function capitolizza(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    /** Aggiorna lo stato del pulsante submit */
    function aggiornaStatoPulsante() {
        if (!btnVolontariato) return;
        const haSelezionati = fasceSelezionate.size > 0;
        btnVolontariato.disabled = !haSelezionati;
        btnVolontariato.setAttribute('aria-disabled', String(!haSelezionati));

        if (noteBtn) {
            noteBtn.textContent = haSelezionati
                ? fasceSelezionate.size + ' ' + (fasceSelezionate.size === 1 ? 'fascia selezionata' : 'fasce selezionate') + '.'
                : 'Seleziona almeno una fascia oraria disponibile.';
        }
    }

    /** Mostra messaggio risultato */
    function mostraMessaggio(testo, tipo) {
        if (!msgVolontariato) return;
        msgVolontariato.textContent = testo;
        msgVolontariato.className   = 'messaggio messaggio-' + tipo;
        msgVolontariato.classList.remove('sr-solo');
        msgVolontariato.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // ── Submit ────────────────────────────────────────────────────────────────
    form.addEventListener('submit', function (evento) {
        evento.preventDefault();

        if (fasceSelezionate.size === 0) {
            mostraMessaggio('Seleziona almeno una fascia oraria.', 'errore');
            return;
        }

        const body = new FormData();
        body.append('fasce', Array.from(fasceSelezionate).join(','));

        btnVolontariato.disabled = true;
        btnVolontariato.textContent = 'Invio in corso…';

        fetch('api/turni.php', {
            method:      'POST',
            body,
            credentials: 'same-origin',
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.errore) {
                // Il server segnala violazione del limite (SHIFT_FULL, ecc.)
                let dettaglio = data.errore;
                if (data.dettagli && data.dettagli.length > 0) {
                    dettaglio += ' Dettaglio: ' + data.dettagli.map(d => d.msg).join('; ');
                }
                mostraMessaggio(dettaglio, 'errore');
                console.error('[Volontariato] Errore server:', data.codice, data.dettagli);
            } else {
                let testo = data.messaggio;
                if (data.avvisi && data.avvisi.length > 0) {
                    testo += ' Avvisi: ' + data.avvisi.map(a => a.msg).join('; ');
                }
                mostraMessaggio(testo, 'successo');
                fasceSelezionate.clear();
                aggiornaStatoPulsante();
                // Ricarica i turni per aggiornare disponibilità
                caricaTurni();
                console.log('[Volontariato] Turni prenotati:', data.inseriti);
            }
        })
        .catch(function (err) {
            console.error('[Volontariato] Errore fetch:', err);
            mostraMessaggio('Errore di rete. Controlla la connessione e riprova.', 'errore');
        })
        .finally(function () {
            btnVolontariato.disabled = false;
            btnVolontariato.textContent = 'Conferma turni selezionati';
            aggiornaStatoPulsante();
        });
    });

    // Avvio: carica i turni
    caricaTurni();
    console.log('[Volontariato] Script caricato');
})();
