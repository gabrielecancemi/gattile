/**
 * prenotazione.js — Form prenotazione visita (Vanilla JS).
 *
 * Si iscrive all'evento CustomEvent 'gattiSelezionatiAggiornati'
 * emesso dal componente React ogni volta che l'utente cambia la selezione.
 *
 * Motivazione del CustomEvent: questo meccanismo permette comunicazione
 * unidirezionale (React → Vanilla JS) senza accoppiamento diretto tra
 * i due moduli. Il componente React non sa nulla del form di prenotazione
 * e il form non dipende da React. È uno standard Web nativo, robusto e
 * facilmente testabile in isolamento.
 */
'use strict';

(function () {
    const form            = document.getElementById('form-prenotazione');
    const inputGattiIds   = document.getElementById('gatti-ids');
    const riepilogo       = document.getElementById('gatti-selezionati-riepilogo');
    const inputDataOra    = document.getElementById('data-visita');
    const btnPrenota      = document.getElementById('btn-prenota');
    const errDataVisita   = document.getElementById('err-data-visita');
    const msgPrenotazione = document.getElementById('msg-prenotazione');
    const noteBtnPrenota  = document.getElementById('note-btn-prenota');

    if (!form) return; // Solo per utenti loggati non-admin

    // Stato locale: gatti selezionati ricevuti da React
    let gattiCorrente = [];

    /** Aggiorna il riepilogo HTML e il campo hidden con gli ID */
    function aggiornaRiepilogo(gatti) {
        gattiCorrente = gatti;

        if (!riepilogo || !inputGattiIds) return;

        // Aggiorna campo nascosto con lista ID separati da virgola
        inputGattiIds.value = gatti.map(g => g.id).join(',');

        if (gatti.length === 0) {
            riepilogo.innerHTML = '<p class="messaggio messaggio-avviso">Nessun gatto selezionato. Clicca sulle card per sceglierli.</p>';
        } else {
            // Costruisce lista leggibile
            let html = '<p><strong>Gatti selezionati (' + gatti.length + '):</strong></p><ul role="list">';
            gatti.forEach(function (g) {
                html += '<li>' + sicuriesc(g.nome) + ' — ' + sicuriesc(g.razza) + ', ' + sicuriesc(g.colore_mantello) + '</li>';
            });
            html += '</ul>';
            riepilogo.innerHTML = html;
        }

        aggiornaStatoPulsante();
    }

    /** Escape HTML minimale lato client (i dati vengono dal server, ma per sicurezza) */
    function sicuriesc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /** Abilita/disabilita il pulsante Prenota */
    function aggiornaStatoPulsante() {
        if (!btnPrenota) return;
        const haGatti  = gattiCorrente.length > 0;
        const haData   = inputDataOra && inputDataOra.value.trim() !== '';
        const pronto   = haGatti && haData;

        btnPrenota.disabled = !pronto;
        btnPrenota.setAttribute('aria-disabled', String(!pronto));

        if (noteBtnPrenota) {
            if (!haGatti && !haData) {
                noteBtnPrenota.textContent = 'Seleziona almeno un gatto e una data per abilitare la prenotazione.';
            } else if (!haGatti) {
                noteBtnPrenota.textContent = 'Seleziona almeno un gatto dalle card qui sopra.';
            } else if (!haData) {
                noteBtnPrenota.textContent = 'Scegli una data e un orario per la visita.';
            } else {
                noteBtnPrenota.textContent = '';
            }
        }
    }

    // ── Ascolto CustomEvent da React ──────────────────────────────────────────
    document.addEventListener('gattiSelezionatiAggiornati', function (evento) {
        const gatti = evento.detail && evento.detail.gatti ? evento.detail.gatti : [];
        aggiornaRiepilogo(gatti);
        console.log('[Prenotazione] Ricevuta selezione React:', gatti.length, 'gatti');
    });

    // ── Validazione data ──────────────────────────────────────────────────────
    if (inputDataOra) {
        // Imposta minimo a ora corrente
        const ora = new Date();
        ora.setMinutes(ora.getMinutes() - ora.getTimezoneOffset());
        inputDataOra.min = ora.toISOString().slice(0, 16);

        inputDataOra.addEventListener('change', function () {
            const selezionata = new Date(this.value);
            const ora = selezionata.getHours();

            if (errDataVisita) {
                if (this.value && selezionata < new Date()) {
                    errDataVisita.textContent = 'La data deve essere futura.';
                    errDataVisita.hidden = false;
                    this.setAttribute('aria-invalid', 'true');
                } else if (this.value && (ora < 9 || ora >= 18)) {
                    errDataVisita.textContent = 'Le visite sono disponibili dalle 9:00 alle 18:00.';
                    errDataVisita.hidden = false;
                    this.setAttribute('aria-invalid', 'true');
                } else {
                    errDataVisita.hidden = true;
                    errDataVisita.textContent = '';
                    this.removeAttribute('aria-invalid');
                }
            }

            aggiornaStatoPulsante();
        });
    }

    // ── Submit form ───────────────────────────────────────────────────────────
    form.addEventListener('submit', function (evento) {
        evento.preventDefault();

        if (gattiCorrente.length === 0) {
            mostraMessaggio('Seleziona almeno un gatto prima di prenotare.', 'errore');
            return;
        }

        const dataOra = inputDataOra ? inputDataOra.value : '';
        if (!dataOra) {
            mostraMessaggio('Scegli una data e un orario per la visita.', 'errore');
            return;
        }

        // Prepara i dati
        const body = new FormData();
        body.append('data_ora',   dataOra);
        body.append('gatti_ids',  gattiCorrente.map(g => g.id).join(','));

        btnPrenota.disabled = true;
        btnPrenota.textContent = 'Invio in corso…';

        fetch('api/prenota_visita.php', {
            method:      'POST',
            body,
            credentials: 'same-origin',
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.errore) {
                mostraMessaggio('Errore: ' + data.errore, 'errore');
                console.error('[Prenotazione] Errore server:', data.errore, '— Codice:', data.codice);
            } else {
                mostraMessaggio(data.messaggio, 'successo');
                form.reset();
                aggiornaRiepilogo([]);
                console.log('[Prenotazione] Visita prenotata con ID:', data.prenotazione_id);
            }
        })
        .catch(function (err) {
            console.error('[Prenotazione] Errore fetch:', err);
            mostraMessaggio('Errore di rete durante la prenotazione. Controlla la connessione e riprova.', 'errore');
        })
        .finally(function () {
            btnPrenota.disabled = false;
            btnPrenota.textContent = 'Conferma prenotazione';
            aggiornaStatoPulsante();
        });
    });

    /** Mostra messaggio risultato nel campo <output> */
    function mostraMessaggio(testo, tipo) {
        if (!msgPrenotazione) return;
        msgPrenotazione.textContent = testo;
        msgPrenotazione.className   = 'messaggio messaggio-' + tipo;
        msgPrenotazione.classList.remove('sr-solo');
        msgPrenotazione.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Stato iniziale
    aggiornaStatoPulsante();
    console.log('[Prenotazione] Script caricato — in attesa di selezioni da React');
})();
