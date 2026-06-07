/**
 * prenotazione.js — Form prenotazione visita (Vanilla JS).
 *
 * Si iscrive all'evento CustomEvent 'gattiSelezionatiAggiornati'
 * emesso dal componente React ogni volta che l'utente cambia la selezione.
 *
 * Il giorno e l'orario vengono scelti con due selettori facilitati e
 * distinti (input date + select degli orari) e poi combinati nel valore
 * 'data_ora' atteso dal backend.
 */
'use strict';

(function () {
    const form            = document.getElementById('form-prenotazione');
    const inputGattiIds   = document.getElementById('gatti-ids');
    const riepilogo       = document.getElementById('gatti-selezionati-riepilogo');
    const inputData       = document.getElementById('data-visita');
    const selectOra       = document.getElementById('ora-visita');
    const btnPrenota      = document.getElementById('btn-prenota');
    const errDataVisita   = document.getElementById('err-data-visita');
    const msgPrenotazione = document.getElementById('msg-prenotazione');
    const noteBtnPrenota  = document.getElementById('note-btn-prenota');

    if (!form) return; // Solo per utenti loggati non-admin

    let gattiCorrente = [];

    /** Escape HTML minimale lato client */
    function sicuriesc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /** Combina data + ora nel formato 'YYYY-MM-DDTHH:MM' */
    function valoreDataOra() {
        const d = inputData ? inputData.value : '';
        const o = selectOra ? selectOra.value : '';
        return (d && o) ? d + 'T' + o : '';
    }

    /** Aggiorna il riepilogo e il campo hidden con gli ID */
    function aggiornaRiepilogo(gatti) {
        gattiCorrente = gatti;
        if (!riepilogo || !inputGattiIds) return;

        inputGattiIds.value = gatti.map(g => g.id).join(',');

        if (gatti.length === 0) {
            riepilogo.innerHTML = '<p class="messaggio messaggio-avviso">Nessun gatto selezionato. Clicca sulle card per sceglierli.</p>';
        } else {
            let html = '<p><strong>Gatti selezionati (' + gatti.length + '):</strong></p><ul>';
            gatti.forEach(function (g) {
                html += '<li>' + sicuriesc(g.nome) + ' — ' + sicuriesc(g.razza) + ', ' + sicuriesc(g.colore_mantello) + '</li>';
            });
            html += '</ul>';
            riepilogo.innerHTML = html;
        }
        aggiornaStatoPulsante();
    }

    /** Abilita/disabilita il pulsante Prenota */
    function aggiornaStatoPulsante() {
        if (!btnPrenota) return;
        const haGatti = gattiCorrente.length > 0;
        const haData  = inputData && inputData.value.trim() !== '';
        const haOra   = selectOra && selectOra.value.trim() !== '';
        const pronto  = haGatti && haData && haOra;

        btnPrenota.disabled = !pronto;
        btnPrenota.setAttribute('aria-disabled', String(!pronto));

        if (noteBtnPrenota) {
            if (!haGatti && (!haData || !haOra)) {
                noteBtnPrenota.textContent = 'Seleziona almeno un gatto, un giorno e un orario per abilitare la prenotazione.';
            } else if (!haGatti) {
                noteBtnPrenota.textContent = 'Seleziona almeno un gatto dalle card qui sopra.';
            } else if (!haData) {
                noteBtnPrenota.textContent = 'Scegli il giorno della visita.';
            } else if (!haOra) {
                noteBtnPrenota.textContent = 'Scegli l\u2019orario della visita.';
            } else {
                noteBtnPrenota.textContent = '';
            }
        }
    }

    /** Valida data + ora */
    function validaQuando() {
        if (!errDataVisita) return true;
        const valore = valoreDataOra();
        if (!valore) { errDataVisita.hidden = true; errDataVisita.textContent = ''; return true; }

        const selezionata = new Date(valore);
        if (selezionata < new Date()) {
            errDataVisita.textContent = 'La data e ora devono essere future.';
            errDataVisita.hidden = false;
            return false;
        }
        const ora = selezionata.getHours();
        if (ora < 9 || ora >= 18) {
            errDataVisita.textContent = 'Le visite sono possibili dalle 9:00 alle 18:00.';
            errDataVisita.hidden = false;
            return false;
        }
        errDataVisita.hidden = true;
        errDataVisita.textContent = '';
        return true;
    }

    // Ascolto CustomEvent da React
    document.addEventListener('gattiSelezionatiAggiornati', function (evento) {
        const gatti = evento.detail && evento.detail.gatti ? evento.detail.gatti : [];
        aggiornaRiepilogo(gatti);
    });

    if (inputData) {
        const oggi = new Date();
        oggi.setMinutes(oggi.getMinutes() - oggi.getTimezoneOffset());
        inputData.min = oggi.toISOString().slice(0, 10);
        inputData.addEventListener('change', function () { validaQuando(); aggiornaStatoPulsante(); });
    }
    if (selectOra) {
        selectOra.addEventListener('change', function () { validaQuando(); aggiornaStatoPulsante(); });
    }

    // Submit
    form.addEventListener('submit', function (evento) {
        evento.preventDefault();

        if (gattiCorrente.length === 0) {
            mostraMessaggio('Seleziona almeno un gatto prima di prenotare.', 'errore');
            return;
        }
        const dataOra = valoreDataOra();
        if (!dataOra) {
            mostraMessaggio('Scegli un giorno e un orario per la visita.', 'errore');
            return;
        }
        if (!validaQuando()) {
            mostraMessaggio('Controlla il giorno e l\u2019orario selezionati.', 'errore');
            return;
        }

        const body = new FormData();
        body.append('data_ora', dataOra);
        body.append('gatti_ids', gattiCorrente.map(g => g.id).join(','));

        btnPrenota.disabled = true;
        btnPrenota.textContent = 'Invio in corso…';

        fetch('api/prenota_visita.php', { method: 'POST', body, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.errore) {
                    mostraMessaggio('Errore: ' + data.errore, 'errore');
                    console.error('[Prenotazione] Errore server:', data.errore, '— Codice:', data.codice);
                } else {
                    mostraMessaggio(data.messaggio, 'successo');
                    form.reset();
                    aggiornaRiepilogo([]);
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

    function mostraMessaggio(testo, tipo) {
        if (!msgPrenotazione) return;
        msgPrenotazione.textContent = testo;
        msgPrenotazione.className = 'messaggio messaggio-' + tipo;
        msgPrenotazione.classList.remove('sr-solo');
        msgPrenotazione.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    aggiornaStatoPulsante();
})();
