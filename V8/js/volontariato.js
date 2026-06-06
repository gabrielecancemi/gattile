/**
 * volontariato.js — Gestione form prenotazione turni volontariato (Vanilla JS).
 *
 * Strategia "gestita meglio":
 *  - Le fasce piene (2/2) e quelle a cui l'utente e' GIA' iscritto vengono
 *    disabilitate visivamente e rese non selezionabili lato client, prima
 *    ancora della submit; il server riverifica comunque in modo indipendente.
 *  - Un riepilogo live mostra quante fasce sono selezionate.
 *  - Dopo una prenotazione riuscita la lista viene ricaricata, cosi' le
 *    disponibilita' e gli stati "gia' iscritto" sono sempre aggiornati.
 *  - I messaggi di errore del server (es. SHIFT_FULL) vengono intercettati
 *    e mostrati in modo esplicito.
 */
'use strict';

(function () {
    const contenitore     = document.getElementById('contenitore-turni');
    const form            = document.getElementById('form-volontariato');
    const btnVolontariato = document.getElementById('btn-volontariato');
    const msgVolontariato = document.getElementById('msg-volontariato');
    const noteBtn         = document.getElementById('note-btn-volontariato');

    if (!form || !contenitore) return; // Solo per utenti loggati

    const fasceSelezionate = new Set();

    /* Escape HTML minimale */
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
            })
            .catch(function (err) {
                console.error('[Volontariato] Errore caricamento turni:', err.message);
                contenitore.innerHTML = '<output class="messaggio messaggio-errore" role="alert">⚠ Impossibile caricare i turni: ' + esc(err.message) + '. Ricarica la pagina per riprovare.</output>';
            })
            .finally(function () {
                contenitore.removeAttribute('aria-busy');
            });
    }

    /** Renderizza la griglia di fasce orarie, raggruppata per data */
    function renderizzaTurni(fasce) {
        // Le selezioni precedenti non sono piu' valide dopo un ricaricamento
        fasceSelezionate.clear();
        aggiornaStatoPulsante();

        if (fasce.length === 0) {
            contenitore.innerHTML = '<p>Nessuna fascia oraria disponibile nel prossimo periodo. Riprova tra qualche giorno.</p>';
            return;
        }

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
            html += '<ul class="griglia-turni">';

            perData[data].forEach(function (fascia) {
                const ora        = fascia.fascia_oraria.slice(11, 16);
                const piena      = fascia.piena;
                const giaIscr    = fascia.gia_iscritto;
                const bloccata   = piena || giaIscr;
                const id         = 'turno-' + fascia.fascia_oraria.replace(/[^0-9]/g, '');

                let classe = 'turno-item ';
                if (giaIscr)      classe += 'iscritto';
                else if (piena)   classe += 'pieno';
                else              classe += 'disponibile';

                html += '<li class="' + classe + '">';

                if (!bloccata) {
                    html += '<label>';
                    html += '<input type="checkbox" id="' + id + '" value="' + esc(fascia.fascia_oraria) + '"'
                          + ' aria-describedby="stato-' + id + '">';
                    html += ' <span class="turno-ora">' + ora + '</span>';
                    html += '</label>';
                } else {
                    html += '<span class="turno-ora" aria-hidden="true">' + ora + '</span>';
                }

                html += '<meter min="0" max="' + fascia.max + '" value="' + fascia.iscritti + '"'
                      + ' low="1" high="2" optimum="0"'
                      + ' aria-label="Volontari iscritti per questa fascia: ' + fascia.iscritti + ' su ' + fascia.max + '"'
                      + ' title="' + fascia.iscritti + '/' + fascia.max + ' volontari"></meter>';

                let testoStato, classeStato;
                if (giaIscr)      { testoStato = '— sei già iscritto'; classeStato = 'iscritto'; }
                else if (piena)   { testoStato = '— pieno';            classeStato = 'pieno'; }
                else              { testoStato = '— disponibile';      classeStato = 'libero'; }

                html += '<p id="stato-' + id + '" class="stato-turno ' + classeStato + '">';
                html += fascia.iscritti + '/' + fascia.max + ' ' + testoStato;
                html += '</p>';

                html += '</li>';
            });

            html += '</ul></section>';
        });

        contenitore.innerHTML = html;

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

    /** Aggiorna lo stato del pulsante submit e la nota */
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

    // -- Submit --
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
                let dettaglio = data.errore;
                if (data.dettagli && data.dettagli.length > 0) {
                    dettaglio += ' Dettaglio: ' + data.dettagli.map(function (d) { return d.msg; }).join('; ');
                }
                mostraMessaggio(dettaglio, 'errore');
                console.error('[Volontariato] Errore server:', data.codice, data.dettagli);
                // Ricarica per riflettere lo stato reale (potrebbe essersi riempito)
                caricaTurni();
            } else {
                let testo = data.messaggio;
                if (data.avvisi && data.avvisi.length > 0) {
                    testo += ' Avvisi: ' + data.avvisi.map(function (a) { return a.msg; }).join('; ');
                }
                mostraMessaggio(testo, 'successo');
                caricaTurni();
            }
        })
        .catch(function (err) {
            console.error('[Volontariato] Errore fetch:', err);
            mostraMessaggio('Errore di rete. Controlla la connessione e riprova.', 'errore');
        })
        .finally(function () {
            btnVolontariato.textContent = 'Conferma turni selezionati';
            aggiornaStatoPulsante();
        });
    });

    caricaTurni();
})();
