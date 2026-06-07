/**
 * volontariato.js — Prenotazione turni volontariato (Vanilla JS).
 *
 * L'utente sceglie il GIORNO tramite <input type="date">; vengono
 * mostrate solo le fasce orarie di quel giorno fra cui selezionare.
 *  - Le fasce piene (2/2) o già prenotate dall'utente sono disabilitate.
 *  - Il server riverifica sempre il limite in modo indipendente.
 *  - Dopo una prenotazione la lista viene ricaricata.
 */
'use strict';

(function () {
    const contenitore = document.getElementById('contenitore-turni');
    const inputData = document.getElementById('data-turno');
    const form = document.getElementById('form-volontariato');
    const btnVolontariato = document.getElementById('btn-volontariato');
    const msgVolontariato = document.getElementById('msg-volontariato');
    const noteBtn = document.getElementById('note-btn-volontariato');

    if (!form || !contenitore) return; // Solo per utenti loggati

    const fasceSelezionate = new Set();
    let fascePerData = {};   // { '2026-06-10': [ {...}, ... ] }

    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /** Carica fasce orarie dall'API */
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
                preparaDati(data.fasce || []);
            })
            .catch(function (err) {
                console.error('[Volontariato] Errore caricamento turni:', err.message);
                contenitore.innerHTML = '<output class="messaggio messaggio-errore" role="alert">Impossibile caricare i turni: ' + esc(err.message) + '. Ricarica la pagina per riprovare.</output>';
            })
            .finally(function () {
                contenitore.removeAttribute('aria-busy');
            });
    }

    /** Raggruppa le fasce per data e imposta i limiti del selettore date */
    function preparaDati(fasce) {
        fasceSelezionate.clear();
        aggiornaStatoPulsante();

        fascePerData = {};
        fasce.forEach(function (f) {
            const giorno = f.fascia_oraria.slice(0, 10);
            if (!fascePerData[giorno]) fascePerData[giorno] = [];
            fascePerData[giorno].push(f);
        });

        const giorni = Object.keys(fascePerData).sort();

        if (inputData) {
            if (giorni.length === 0) {
                // Nessun giorno disponibile: disabilita il campo
                inputData.disabled = true;
                contenitore.innerHTML = '<p>Nessuna fascia oraria disponibile nel prossimo periodo. Riprova tra qualche giorno.</p>';
                return;
            }
            // Limita la scelta ai giorni con fasce disponibili
            inputData.disabled = false;
            inputData.min = giorni[0];
            inputData.max = giorni[giorni.length - 1];
        }

        // (#9) Dati caricati ma nessun giorno ancora scelto: NON lasciare il
        // messaggio "Caricamento fasce orarie in corso…". Mostra invece
        // l'invito a scegliere prima un giorno (le fasce compaiono dopo).
        if (!inputData || !inputData.value) {
            contenitore.innerHTML = '<p>Scegli prima un giorno qui sopra: verranno mostrate le fasce orarie disponibili.</p>';
        } else {
            mostraGiorno(inputData.value);
        }
    }

    /** Mostra le fasce del giorno selezionato */
    function mostraGiorno(giorno) {
        fasceSelezionate.clear();
        aggiornaStatoPulsante();

        // Giorno non presente tra quelli con fasce
        if (!fascePerData[giorno]) {
            contenitore.innerHTML = '<p>Nessuna fascia disponibile per il giorno scelto. Prova un altro giorno.</p>';
            return;
        }

        const fasce = fascePerData[giorno];
        if (fasce.length === 0) {
            contenitore.innerHTML = '<p>Nessuna fascia disponibile per il giorno scelto.</p>';
            return;
        }

        let html = '<ul class="griglia-turni">';
        fasce.forEach(function (fascia) {
            const ora = fascia.fascia_oraria.slice(11, 16);
            const piena = fascia.piena;
            const giaIscr = fascia.gia_iscritto;
            const bloccata = piena || giaIscr;
            const id = 'turno-' + fascia.fascia_oraria.replace(/[^0-9]/g, '');

            let classe = 'turno-item ';
            if (giaIscr) classe += 'iscritto';
            else if (piena) classe += 'pieno';
            else classe += 'disponibile';

            html += '<li class="' + classe + '">';

            if (!bloccata) {
                html += '<label>';
                html += '<input type="checkbox" id="' + id + '" value="' + esc(fascia.fascia_oraria) + '"'
                    + ' aria-describedby="stato-' + id + '"> ';
                html += '<time datetime="' + esc(fascia.fascia_oraria) + '" class="turno-ora">' + ora + '</time>';
                html += '</label>';
            } else {
                html += '<time datetime="' + esc(fascia.fascia_oraria) + '" class="turno-ora">' + ora + '</time>';
            }

            html += '<meter min="0" max="' + fascia.max + '" value="' + fascia.iscritti + '"'
                + ' low="1" high="2" optimum="0"'
                + ' aria-label="Volontari iscritti: ' + fascia.iscritti + ' su ' + fascia.max + '"'
                + ' title="' + fascia.iscritti + '/' + fascia.max + ' volontari"></meter>';

            let testoStato, classeStato;
            if (giaIscr) { testoStato = '— sei già iscritto'; classeStato = 'iscritto'; }
            else if (piena) { testoStato = '— pieno'; classeStato = 'pieno'; }
            else { testoStato = '— disponibile'; classeStato = 'libero'; }

            html += '<p id="stato-' + id + '" class="stato-turno ' + classeStato + '">'
                + fascia.iscritti + '/' + fascia.max + ' ' + testoStato + '</p>';
            html += '</li>';
        });
        html += '</ul>';

        contenitore.innerHTML = html;

        contenitore.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                if (this.checked) { fasceSelezionate.add(this.value); }
                else { fasceSelezionate.delete(this.value); }
                aggiornaStatoPulsante();
            });
        });
    }

    function aggiornaStatoPulsante() {
        if (!btnVolontariato) return;
        const haSelezionati = fasceSelezionate.size > 0;
        btnVolontariato.disabled = !haSelezionati;
        btnVolontariato.setAttribute('aria-disabled', String(!haSelezionati));
        if (noteBtn) {
            noteBtn.textContent = haSelezionati
                ? fasceSelezionate.size + ' ' + (fasceSelezionate.size === 1 ? 'fascia selezionata' : 'fasce selezionate') + '.'
                : 'Seleziona un giorno e almeno una fascia oraria disponibile.';
        }
    }

    function mostraMessaggio(testo, tipo) {
        if (!msgVolontariato) return;
        msgVolontariato.textContent = testo;
        msgVolontariato.className = 'messaggio messaggio-' + tipo;
        msgVolontariato.classList.remove('sr-solo');
        msgVolontariato.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    if (inputData) {
        inputData.addEventListener('change', function () {
            if (this.value) mostraGiorno(this.value);
        });
    }

    // Submit
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

        fetch('api/turni.php', { method: 'POST', body, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.errore) {
                    let dettaglio = data.errore;
                    if (data.dettagli && data.dettagli.length > 0) {
                        dettaglio += ' Dettaglio: ' + data.dettagli.map(function (d) { return d.msg; }).join('; ');
                    }
                    mostraMessaggio(dettaglio, 'errore');
                    caricaTurni();
                } else {
                    let testo = data.messaggio;
                    if (data.avvisi && data.avvisi.length > 0) {
                        testo += ' Avvisi: ' + data.avvisi.map(function (a) { return a.msg; }).join('; ');
                    }
                    mostraMessaggio(testo, 'successo');
                    // (#3) Dopo una prenotazione andata a buon fine ricarica la pagina.
                    window.location.reload();
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
