// Prenotazione turni di volontariato (Vanilla JS).
//
// L'utente sceglie il giorno con <input type="date">; vengono mostrate solo
// le fasce di quel giorno. Le fasce piene (2/2) o già prenotate sono
// disabilitate. Il server riverifica comunque il limite in autonomia. Dopo
// una prenotazione la pagina viene ricaricata.
'use strict';

(function () {
    const contenitore = document.getElementById('contenitore-turni');
    const input_data = document.getElementById('data-turno');
    const form = document.getElementById('form-volontariato');
    const bottone_volontariato = document.getElementById('btn-volontariato');
    const messaggio_volontariato = document.getElementById('msg-volontariato');
    const nota_bottone = document.getElementById('note-btn-volontariato');

    if (!form || !contenitore) return; // solo per utenti loggati

    const fasce_selezionate = new Set();
    let fasce_per_data = {};   // { '2026-06-10': [ {...}, ... ] }

    function ripuliscihtml(stringa) {
        return String(stringa)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function caricaTurni() {
        contenitore.setAttribute('aria-busy', 'true');
        contenitore.innerHTML = '<p class="caricamento">Caricamento fasce orarie in corso…</p>';

        // Errore di rete o risposta non valida gestiti dal secondo handler di
        // then() (onRejected), senza blocchi try/catch/finally. Si controlla
        // res.ok perché fetch non considera errore i codici HTTP 4xx/5xx.
        function gestisciErrore(err) {
            console.error('[Volontariato] errore caricamento turni:', err.message);
            contenitore.innerHTML = '<output class="messaggio messaggio-errore" role="alert">Impossibile caricare i turni: ' + ripuliscihtml(err.message) + '. Ricarica la pagina per riprovare.</output>';
            contenitore.removeAttribute('aria-busy');
        }

        fetch('api/turni.php', { credentials: 'same-origin' })
            .then(function (r) {
                if (!r.ok) return Promise.reject(new Error('Risposta server: ' + r.status));
                return r.json();
            })
            .then(function (dati) {
                if (dati.errore) return Promise.reject(new Error(dati.errore));
                preparaDati(dati.fasce || []);
                contenitore.removeAttribute('aria-busy');
            }, gestisciErrore);
    }

    // Raggruppa le fasce per data e imposta i limiti del selettore.
    function preparaDati(fasce) {
        fasce_selezionate.clear();
        aggiornaStatoPulsante();

        fasce_per_data = {};
        fasce.forEach(function (f) {
            const giorno = f.fascia_oraria.slice(0, 10);
            if (!fasce_per_data[giorno]) fasce_per_data[giorno] = [];
            fasce_per_data[giorno].push(f);
        });

        const giorni = Object.keys(fasce_per_data).sort();

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

        // Dati caricati ma nessun giorno scelto: invito a scegliere il giorno,
        // niente messaggio di caricamento residuo.
        if (!input_data || !input_data.value) {
            contenitore.innerHTML = '';
        } else {
            mostraGiorno(input_data.value);
        }
    }

    function mostraGiorno(giorno) {
        fasce_selezionate.clear();
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
                if (this.checked) { fasce_selezionate.add(this.value); }
                else { fasce_selezionate.delete(this.value); }
                aggiornaStatoPulsante();
            });
        });
    }

    function aggiornaStatoPulsante() {
        if (!bottone_volontariato) return;
        const ha_selezionati = fasce_selezionate.size > 0;
        bottone_volontariato.disabled = !ha_selezionati;
        bottone_volontariato.setAttribute('aria-disabled', String(!ha_selezionati));
        if (nota_bottone) {
            nota_bottone.textContent = ha_selezionati
                ? fasce_selezionate.size + ' ' + (fasce_selezionate.size === 1 ? 'fascia selezionata' : 'fasce selezionate') + '.'
                : 'Seleziona un giorno e almeno una fascia oraria disponibile.';
        }
    }

    function mostraMessaggio(testo, tipo) {
        if (!messaggio_volontariato) return;
        messaggio_volontariato.textContent = testo;
        messaggio_volontariato.className = 'messaggio messaggio-' + tipo;
        messaggio_volontariato.classList.remove('sr-solo');
        messaggio_volontariato.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    if (input_data) {
        input_data.addEventListener('change', function () {
            if (this.value) mostraGiorno(this.value);
        });
    }

    form.addEventListener('submit', function (evento) {
        evento.preventDefault();
        if (fasce_selezionate.size === 0) {
            mostraMessaggio('Seleziona almeno una fascia oraria.', 'errore');
            return;
        }

        const corpo = new FormData();
        corpo.append('fasce', Array.from(fasce_selezionate).join(','));

        bottone_volontariato.disabled = true;
        bottone_volontariato.textContent = 'Invio in corso…';

        // Ripristino del pulsante in entrambi gli esiti, senza try/catch/finally.
        function ripristinaPulsante() {
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
                } else {
                    let testo = dati.messaggio;
                    if (dati.avvisi && dati.avvisi.length > 0) {
                        testo += ' Avvisi: ' + dati.avvisi.map(function (a) { return a.msg; }).join('; ');
                    }
                    mostraMessaggio(testo, 'successo');
                    // Ricarico solo le fasce per aggiornare i conteggi: senza
                    // reload della pagina il messaggio di successo resta a video.
                    caricaTurni();
                }
                ripristinaPulsante();
            }, function (err) {
                console.error('[Volontariato] errore fetch:', err);
                mostraMessaggio('Errore di rete. Controlla la connessione e riprova.', 'errore');
                ripristinaPulsante();
            });
    });

    caricaTurni();
})();
