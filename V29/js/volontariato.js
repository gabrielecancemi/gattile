// Prenotazione turni di volontariato

'use strict';

(function () {
    const contenitore = document.getElementById('contenitore-turni');
    const input_data = document.getElementById('data-turno');
    const form = document.getElementById('form-volontariato');
    const bottone_volontariato = document.getElementById('btn-volontariato');
    const messaggio_volontariato = document.getElementById('msg-volontariato');
    const errore_data = document.getElementById('err-data-turno');
    const errore_fasce = document.getElementById('err-fasce-turni');
    const successo_volontariato = document.getElementById('successo-volontariato');

    if (!form || !contenitore) return;

    const fasce_selezionate = [];
    let fasce_per_data = {};

    function validaGiorno(mostra = true) {
        const v = input_data ? input_data.value : '';

        if (!v) {
            if (mostra) mostraErroreCampo(input_data, errore_data, 'Scegli un giorno per visualizzare le fasce orarie.');
            return false;
        }

        const scelta = new Date(v + 'T00:00');
        if (isNaN(scelta.getTime())) {
            if (mostra) mostraErroreCampo(input_data, errore_data, 'Formato data non valido.');
            return false;
        }

        const oggi = new Date();
        oggi.setHours(0, 0, 0, 0);
        if (scelta < oggi) {
            if (mostra) mostraErroreCampo(input_data, errore_data, 'La data non può essere nel passato.');
            return false;
        }

        if (input_data && input_data.min && v < input_data.min) {
            if (mostra) mostraErroreCampo(input_data, errore_data, 'Nessuna fascia disponibile prima di questa data.');
            return false;
        }

        if (input_data && input_data.max && v > input_data.max) {
            if (mostra) mostraErroreCampo(input_data, errore_data, 'Nessuna fascia disponibile dopo questa data.');
            return false;
        }

        if (mostra) mostraErroreCampo(input_data, errore_data, '');
        return true;
    }

    function caricaTurni() {
        contenitore.setAttribute('aria-busy', 'true');
        contenitore.innerHTML =
            '<p class="caricamento">Caricamento fasce orarie in corso…</p>';

        function gestisciErrore(messaggio) {
            console.error('[Volontariato] errore caricamento turni:', messaggio);
            contenitore.innerHTML =
                '<output class="messaggio messaggio-errore" role="alert">' +
                messaggio +
                '</output>';
            contenitore.removeAttribute('aria-busy');
        }

        fetch('api/turni.php', { credentials: 'same-origin' })
            .then(function (r) {

                if (!r.ok) {
                    gestisciErrore('Impossibile caricare le fasce. Riprova tra qualche minuto.');
                    return;
                }

                return r.json();

            })
            .then(function (dati) {

                if (!dati) return;

                if (dati.errore) {
                    gestisciErrore(dati.errore);
                    return;
                }

                preparaDati(dati.fasce || []);
                contenitore.removeAttribute('aria-busy');

            }, function () {

                gestisciErrore(
                    'Impossibile caricare i turni. Riprova tra qualche minuto.'
                );

            });
    }

    // Raggruppa le fasce per data e imposta i limiti del selettore.
    function preparaDati(fasce) {
        fasce_selezionate.length = 0;
        aggiornaStatoPulsante();

        fasce_per_data = {};
        fasce.forEach(function (f) {
            const giorno = f.fascia_oraria.slice(0, 10);
            if (!fasce_per_data[giorno]) fasce_per_data[giorno] = [];
            fasce_per_data[giorno].push(f);
        });

        let giorni = [];

        for (let giorno in fasce_per_data) {
            giorni.push(giorno);
        }

        giorni.sort();

        if (input_data) {
            if (giorni.length === 0) {
                input_data.disabled = true;
                contenitore.innerHTML = '<p>Nessuna fascia oraria disponibile nel prossimo periodo. Riprova tra qualche giorno.</p>';
                return;
            }
            input_data.disabled = false;
            input_data.min = giorni[0];
            input_data.max = giorni[giorni.length - 1];
        }

        // Dati caricati ma nessun giorno scelto
        if (!input_data || !input_data.value) {
            contenitore.innerHTML = '';
        } else {
            mostraGiorno(input_data.value);
        }
    }

    function mostraGiorno(giorno) {
        fasce_selezionate.length = 0;
        aggiornaStatoPulsante();

        if (!fasce_per_data[giorno]) {
            contenitore.innerHTML = '<p>Nessuna fascia disponibile per il giorno scelto. Prova un altro giorno.</p>';
            return;
        }

        const fasce = fasce_per_data[giorno];
        if (fasce.length === 0) {
            contenitore.innerHTML = '<p>Nessuna fascia disponibile per il giorno scelto.</p>';
            return;
        }

        let html = '<ul class="griglia-turni">';
        fasce.forEach(function (fascia) {
            const ora = fascia.fascia_oraria.slice(11, 16);
            const piena = fascia.piena;
            const gia_iscritto = fascia.gia_iscritto;
            const bloccata = piena || gia_iscritto;
            const id = 'turno-' + fascia.fascia_oraria.replace(/[^0-9]/g, '');

            let classe = 'turno-item ';
            if (gia_iscritto) classe += 'iscritto';
            else if (piena) classe += 'pieno';
            else classe += 'disponibile';

            html += '<li class="' + classe + '">';

            if (!bloccata) {
                html += '<label>';
                html += '<input type="checkbox" id="' + id + '" value="' + ripuliscihtml(fascia.fascia_oraria) + '"'
                    + ' aria-describedby="stato-' + id + '"> ';
                html += '<time datetime="' + ripuliscihtml(fascia.fascia_oraria) + '" class="turno-ora">' + ora + '</time>';
                html += '</label>';
            } else {
                html += '<time datetime="' + ripuliscihtml(fascia.fascia_oraria) + '" class="turno-ora">' + ora + '</time>';
            }

            html += '<meter min="0" max="' + fascia.max + '" value="' + fascia.iscritti + '"'
                + ' low="1" high="2" optimum="0"'
                + ' aria-label="Volontari iscritti: ' + fascia.iscritti + ' su ' + fascia.max + '"'
                + ' title="' + fascia.iscritti + '/' + fascia.max + ' volontari"></meter>';

            let testo_stato, classe_stato;
            if (gia_iscritto) { testo_stato = '— sei già iscritto'; classe_stato = 'iscritto'; }
            else if (piena) { testo_stato = '— pieno'; classe_stato = 'pieno'; }
            else { testo_stato = '— disponibile'; classe_stato = 'libero'; }

            html += '<p id="stato-' + id + '" class="stato-turno ' + classe_stato + '">'
                + fascia.iscritti + '/' + fascia.max + ' ' + testo_stato + '</p>';
            html += '</li>';
        });
        html += '</ul>';

        contenitore.innerHTML = html;

        contenitore.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {

                if (this.checked) {

                    if (!fasce_selezionate.includes(this.value)) {
                        fasce_selezionate.push(this.value);
                    }
                    mostraErroreCampo(null, errore_fasce, '');

                } else {

                    let indice = fasce_selezionate.indexOf(this.value);

                    if (indice !== -1) {
                        fasce_selezionate.splice(indice, 1);
                    }
                }

                aggiornaStatoPulsante();
            });
        });
    }

    function aggiornaStatoPulsante() {
        if (!bottone_volontariato) return;

        const ha_selezionati = fasce_selezionate.length > 0;

    }

    function mostraMessaggio(testo, tipo) {
        mostraMessaggioComune(messaggio_volontariato, testo, tipo);
    }

    if (input_data) {
        input_data.addEventListener('change', function () {
            if (validaGiorno() && this.value) mostraGiorno(this.value);
        });
        input_data.addEventListener('blur', function () { validaGiorno(); });
    }

    form.addEventListener('submit', function (evento) {
        evento.preventDefault();

        const giorno_ok = validaGiorno();

        const fasce_ok = fasce_selezionate.length > 0;
        if (!fasce_ok) {
            mostraErroreCampo(null, errore_fasce, 'Seleziona almeno una fascia oraria.');
        } else {
            mostraErroreCampo(null, errore_fasce, '');
        }

        if (!giorno_ok || !fasce_ok) {
            if (input_data) input_data.focus();
            return;
        }

        const corpo = new FormData();
        corpo.append('fasce', fasce_selezionate.join(','));

        bottone_volontariato.disabled = true;
        bottone_volontariato.textContent = 'Invio in corso…';

        // Ripristino del pulsante in entrambi gli esiti
        function ripristinaPulsante() {
            bottone_volontariato.disabled = false;
            bottone_volontariato.textContent = 'Conferma turni selezionati';
            aggiornaStatoPulsante();
        }

        fetch('api/turni.php', { method: 'POST', body: corpo, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (dati) {
                if (dati.errore) {
                    let dettaglio = dati.errore;
                    if (dati.dettagli && dati.dettagli.length > 0) {
                        dettaglio += ' Dettaglio: ' + dati.dettagli.map(function (d) { return d.msg; }).join('; ');
                    }
                    mostraMessaggio(dettaglio, 'errore');
                    caricaTurni();
                    ripristinaPulsante();
                } else {
                    let testo = dati.messaggio;
                    if (dati.avvisi && dati.avvisi.length > 0) {
                        testo += ' Avvisi: ' + dati.avvisi.map(function (a) { return a.msg; }).join('; ');
                    }
                    if (successo_volontariato) {
                        successo_volontariato.innerHTML =
                            '<output class="messaggio messaggio-successo" role="status" aria-live="assertive">' +
                            ripuliscihtml(testo) + '</output>';
                        bottoniConferma(successo_volontariato, [
                            { href: 'volontariato.php', testo: 'Prenota altri turni' },
                            { href: 'index.php', testo: 'Torna alla home' }
                        ]);
                    }
                    form.hidden = true;
                }
            }, function (err) {
                console.error('[Volontariato] errore fetch:', err);
                mostraMessaggio('Errore di rete. Riprova tra qualche minuto.', 'errore');
                ripristinaPulsante();
            });
    });

    caricaTurni();
})();