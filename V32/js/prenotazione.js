// Form di prenotazione visita

'use strict';

(function () {
    /* componenti della pagina */
    const form = document.getElementById('form-prenotazione');
    const input_gatti_ids = document.getElementById('gatti-ids');
    const riepilogo = document.getElementById('gatti-selezionati-riepilogo');
    const input_data = document.getElementById('data-visita');
    const select_ora = document.getElementById('ora-visita');
    const bottone_prenota = document.getElementById('btn-prenota');
    const errore_data = document.getElementById('err-data-visita');
    const errore_giorno = document.getElementById('err-giorno-visita');
    const errore_ora = document.getElementById('err-ora-visita');
    const errore_gatti = document.getElementById('err-gatti-selezione');
    const messaggio_prenotazione = document.getElementById('msg-prenotazione');
    const successo_prenotazione = document.getElementById('successo-prenotazione');

    if (!form) return;

    let gatti_correnti = [];

    function validaGiorno(mostra = true) {
        const v = input_data ? input_data.value : '';

        if (!v) {
            if (mostra) mostraErroreCampo(input_data, errore_giorno, 'Scegli il giorno della visita.');
            return false;
        }

        const scelta = new Date(v + 'T00:00');
        if (isNaN(scelta.getFullYear())) {
            if (mostra) mostraErroreCampo(input_data, errore_giorno, 'Formato data non valido.');
            return false;
        }

        const oggi = new Date();
        oggi.setHours(0, 0, 0, 0);
        if (scelta < oggi) {
            if (mostra) mostraErroreCampo(input_data, errore_giorno, 'La data non può essere nel passato.');
            return false;
        }

        if (mostra) mostraErroreCampo(input_data, errore_giorno, '');
        return true;
    }

    function validaOra(mostra = true) {
        const v = select_ora ? select_ora.value : '';

        if (!v) {
            if (mostra) mostraErroreCampo(select_ora, errore_ora, 'Scegli l\u2019orario della visita.');
            return false;
        }

        const ora = parseInt(v.slice(0, 2), 10);
        if (isNaN(ora) || ora < 9 || ora >= 18) {
            if (mostra) mostraErroreCampo(select_ora, errore_ora, 'Le visite sono possibili dalle 9:00 alle 18:00.');
            return false;
        }

        if (mostra) mostraErroreCampo(select_ora, errore_ora, '');
        return true;
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
            // nodo di errore.
            riepilogo.innerHTML = '<p class="messaggio messaggio-avviso">Nessun gatto selezionato. Clicca sulle card per sceglierli.</p>';
            if (errore_gatti) riepilogo.appendChild(errore_gatti);
        } else {
            let html = '<p><strong>Gatti selezionati (' + gatti.length + '):</strong></p><ul>';
            for (let i = 0; i < gatti.length; i++) {
                let g = gatti[i];

                html += '<li><strong>' + ripuliscihtml(g.nome) + '</strong>: ' +
                    ripuliscihtml(g.razza) + ', ' +
                    ripuliscihtml(g.colore_mantello) +
                    '</li>';
            }
            html += '</ul>';
            riepilogo.innerHTML = html;
            if (errore_gatti) riepilogo.appendChild(errore_gatti);
            mostraErroreCampo(null, errore_gatti, '');
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
        // Data minima
        const oggi = new Date();
        const anno = oggi.getFullYear();
        let mese = (oggi.getMonth() + 1) + '';
        let giorno = oggi.getDate() + '';
        if (mese.length < 2) mese = '0' + mese;
        if (giorno.length < 2) giorno = '0' + giorno;
        input_data.min = anno + '-' + mese + '-' + giorno;
        input_data.addEventListener('change', function () { validaGiorno(); validaQuando(); });
        input_data.addEventListener('blur', function () { validaGiorno(); });
    }
    if (select_ora) {
        select_ora.addEventListener('change', function () { validaOra(); validaQuando(); });
        select_ora.addEventListener('blur', function () { validaOra(); });
    }

    form.addEventListener('submit', function (evento) {
        evento.preventDefault();

        const gatti_ok = gatti_correnti.length > 0;
        if (!gatti_ok) {
            mostraErroreCampo(null, errore_gatti, 'Seleziona almeno un gatto prima di prenotare.');
        } else {
            mostraErroreCampo(null, errore_gatti, '');
        }

        const giorno_ok = validaGiorno();
        const ora_ok = validaOra();
        const quando_ok = validaQuando();

        if (!gatti_ok || !giorno_ok || !ora_ok || !quando_ok) {
            if (!giorno_ok && input_data) input_data.focus();
            else if (!ora_ok && select_ora) select_ora.focus();
            return;
        }

        const data_ora = valoreDataOra();

        const corpo =
            'data_ora=' + data_ora +
            '&gatti_ids=' + gatti_correnti.map(g => g.id).join(',');

        bottone_prenota.disabled = true;
        bottone_prenota.textContent = 'Invio in corso…';

        // Ripristino del pulsante eseguito in entrambi gli esiti
        function ripristinaPulsante() {
            bottone_prenota.disabled = false;
            bottone_prenota.textContent = 'Conferma prenotazione';
        }

        fetch('interfaccia/prenota_visita.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: corpo,
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(function (dati) {
                if (dati.errore) {
                    mostraMessaggio(dati.errore, 'errore');
                    console.error('[Prenotazione] errore server:', dati.errore, '— codice:', dati.codice);
                    ripristinaPulsante();
                } else {
                    if (successo_prenotazione) {
                        successo_prenotazione.innerHTML =
                            '<output class="messaggio messaggio-successo" role="status" aria-live="assertive">' +
                            ripuliscihtml(dati.messaggio) + '</output>';
                        bottoniConferma(successo_prenotazione, [
                            { href: 'adozioni.php', testo: 'Prenota un\'altra visita' },
                            { href: 'home.php', testo: 'Torna alla home' }
                        ]);
                    }
                    form.hidden = true;
                    // Chiede al componente React di deselezionare tutte le card.
                    document.dispatchEvent(new CustomEvent('gattiDeselezionaTutti'));
                }
            }, function (err) {
                console.error('[Prenotazione] errore fetch:', err);
                mostraMessaggio('Errore di rete durante la prenotazione. Controlla la connessione e riprova.', 'errore');
                ripristinaPulsante();
            });
    });

    function mostraMessaggio(testo, tipo) {
        mostraMessaggioComune(messaggio_prenotazione, testo, tipo);
    }

})();
