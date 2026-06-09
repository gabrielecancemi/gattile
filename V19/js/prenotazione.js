// Form di prenotazione visita (Vanilla JS). Si mette in ascolto dell'evento
// 'gattiSelezionatiAggiornati' emesso dal componente React a ogni cambio di
// selezione. Giorno e orario si scelgono separatamente (input date + select)
// e poi vengono combinati nel valore 'data_ora' atteso dal backend.
'use strict';

(function () {
    const form = document.getElementById('form-prenotazione');
    const input_gatti_ids = document.getElementById('gatti-ids');
    const riepilogo = document.getElementById('gatti-selezionati-riepilogo');
    const input_data = document.getElementById('data-visita');
    const select_ora = document.getElementById('ora-visita');
    const bottone_prenota = document.getElementById('btn-prenota');
    const errore_data = document.getElementById('err-data-visita');
    const messaggio_prenotazione = document.getElementById('msg-prenotazione');
    const nota_bottone = document.getElementById('note-btn-prenota');

    if (!form) return; // solo per utenti loggati non-admin

    let gatti_correnti = [];

    function ripuliscihtml(stringa) {
        return String(stringa)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function valoreDataOra() {
        const giorno = input_data ? input_data.value : '';
        const ora = select_ora ? select_ora.value : '';
        return (giorno && ora) ? giorno + 'T' + ora : '';
    }

    function aggiornaRiepilogo(gatti) {
        gatti_correnti = gatti;
        if (!riepilogo || !input_gatti_ids) return;

        input_gatti_ids.value = gatti.map(g => g.id).join(',');

        if (gatti.length === 0) {
            riepilogo.innerHTML = '<p class="messaggio messaggio-avviso">Nessun gatto selezionato. Clicca sulle card per sceglierli.</p>';
        } else {
            let html = '<p><strong>Gatti selezionati (' + gatti.length + '):</strong></p><ul>';
            gatti.forEach(function (g) {
                html += '<li>' + ripuliscihtml(g.nome) + ' — ' + ripuliscihtml(g.razza) + ', ' + ripuliscihtml(g.colore_mantello) + '</li>';
            });
            html += '</ul>';
            riepilogo.innerHTML = html;
        }
        aggiornaStatoPulsante();
    }

    function aggiornaStatoPulsante() {
        if (!bottone_prenota) return;
        const ha_gatti = gatti_correnti.length > 0;
        const ha_data = input_data && input_data.value.trim() !== '';
        const ha_ora = select_ora && select_ora.value.trim() !== '';
        const pronto = ha_gatti && ha_data && ha_ora;

        bottone_prenota.disabled = !pronto;
        bottone_prenota.setAttribute('aria-disabled', String(!pronto));

        if (nota_bottone) {
            if (!ha_gatti && (!ha_data || !ha_ora)) {
                nota_bottone.textContent = 'Seleziona almeno un gatto, un giorno e un orario per abilitare la prenotazione.';
            } else if (!ha_gatti) {
                nota_bottone.textContent = 'Seleziona almeno un gatto dalle card qui sopra.';
            } else if (!ha_data) {
                nota_bottone.textContent = 'Scegli il giorno della visita.';
            } else if (!ha_ora) {
                nota_bottone.textContent = 'Scegli l\u2019orario della visita.';
            } else {
                nota_bottone.textContent = '';
            }
        }
    }

    function validaQuando() {
        if (!errore_data) return true;
        const valore = valoreDataOra();
        if (!valore) { errore_data.hidden = true; errore_data.textContent = ''; return true; }

        const scelta = new Date(valore);
        if (scelta < new Date()) {
            errore_data.textContent = 'La data e ora devono essere future.';
            errore_data.hidden = false;
            return false;
        }
        const ora = scelta.getHours();
        if (ora < 9 || ora >= 18) {
            errore_data.textContent = 'Le visite sono possibili dalle 9:00 alle 18:00.';
            errore_data.hidden = false;
            return false;
        }
        errore_data.hidden = true;
        errore_data.textContent = '';
        return true;
    }

    document.addEventListener('gattiSelezionatiAggiornati', function (evento) {
        const gatti = evento.detail && evento.detail.gatti ? evento.detail.gatti : [];
        aggiornaRiepilogo(gatti);
    });

    if (input_data) {
        const oggi = new Date();
        oggi.setMinutes(oggi.getMinutes() - oggi.getTimezoneOffset());
        input_data.min = oggi.toISOString().slice(0, 10);
        input_data.addEventListener('change', function () { validaQuando(); aggiornaStatoPulsante(); });
    }
    if (select_ora) {
        select_ora.addEventListener('change', function () { validaQuando(); aggiornaStatoPulsante(); });
    }

    form.addEventListener('submit', function (evento) {
        evento.preventDefault();

        if (gatti_correnti.length === 0) {
            mostraMessaggio('Seleziona almeno un gatto prima di prenotare.', 'errore');
            return;
        }
        const data_ora = valoreDataOra();
        if (!data_ora) {
            mostraMessaggio('Scegli un giorno e un orario per la visita.', 'errore');
            return;
        }
        if (!validaQuando()) {
            mostraMessaggio('Controlla il giorno e l\u2019orario selezionati.', 'errore');
            return;
        }

        const corpo = new FormData();
        corpo.append('data_ora', data_ora);
        corpo.append('gatti_ids', gatti_correnti.map(g => g.id).join(','));

        bottone_prenota.disabled = true;
        bottone_prenota.textContent = 'Invio in corso…';

        // Ripristino del pulsante eseguito in entrambi gli esiti (successo o
        // errore di rete), senza ricorrere a blocchi try/catch/finally.
        function ripristinaPulsante() {
            bottone_prenota.disabled = false;
            bottone_prenota.textContent = 'Conferma prenotazione';
            aggiornaStatoPulsante();
        }

        fetch('api/prenota_visita.php', { method: 'POST', body: corpo, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (dati) {
                if (dati.errore) {
                    mostraMessaggio('Errore: ' + dati.errore, 'errore');
                    console.error('[Prenotazione] errore server:', dati.errore, '— codice:', dati.codice);
                } else {
                    mostraMessaggio(dati.messaggio, 'successo');
                    form.reset();
                    aggiornaRiepilogo([]);
                    // Chiede al componente React di deselezionare tutte le card.
                    document.dispatchEvent(new CustomEvent('gattiDeselezionaTutti'));
                }
                ripristinaPulsante();
            }, function (err) {
                console.error('[Prenotazione] errore fetch:', err);
                mostraMessaggio('Errore di rete durante la prenotazione. Controlla la connessione e riprova.', 'errore');
                ripristinaPulsante();
            });
    });

    function mostraMessaggio(testo, tipo) {
        if (!messaggio_prenotazione) return;
        messaggio_prenotazione.textContent = testo;
        messaggio_prenotazione.className = 'messaggio messaggio-' + tipo;
        messaggio_prenotazione.classList.remove('sr-solo');
        messaggio_prenotazione.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    aggiornaStatoPulsante();
})();
