// Caricamento asincrono dei dati della home (statistiche e nuovi arrivi)

'use strict';

(function () {
    const contenitore_statistiche = document.getElementById('contenitore-statistiche');
    const caricamento_statistiche = document.getElementById('caricamento-statistiche');
    const contenitore_arrivi = document.getElementById('contenitore-arrivi');
    const caricamento_arrivi = document.getElementById('caricamento-arrivi');

    if (!contenitore_statistiche && !contenitore_arrivi) return;

    function dataItaliana(iso) {
        if (!iso) return '';
        const parti = String(iso).slice(0, 10).split('-');
        if (parti.length !== 3) return '';
        return parti[2] + '/' + parti[1] + '/' + parti[0];
    }

    function mostraStatistiche(statistiche, errore) {
        if (!contenitore_statistiche || !caricamento_statistiche) return;
        contenitore_statistiche.removeAttribute('aria-busy');
        caricamento_statistiche.style.display = "none"

        if (errore) {
            contenitore_statistiche.innerHTML += messaggioErrore(errore);
            return;
        }

        let html = '<dl class="statistiche">';
        html += '<dt>Gatti ospitati</dt><dd>' + statistiche.gatti + '</dd>';
        html += '<dt>Incontri organizzati</dt><dd>' + statistiche.visite + '</dd>';
        html += '<dt>Volontari attivi</dt><dd>' + statistiche.volontari + '</dd>';
        html += '<dt>Nuovi arrivi quest\u0027anno</dt><dd>' + statistiche.arrivi + '</dd>';
        html += '</dl>';
        contenitore_statistiche.innerHTML += html;
    }

    function schedaGatto(gatto) {
        const id_titolo = 'gatto-' + gatto.id;
        const sesso = gatto.sesso === 'M' ? 'Maschio' : 'Femmina';
        const eta = etaInParole(gatto.eta);
        const immagine = gatto.foto && String(gatto.foto).trim() !== ''
            ? gatto.foto
            : 'img/placeholder-gatto.jpg';
        const nome = ripuliscihtml(gatto.nome);
        const data_iso = ripuliscihtml(gatto.data_arrivo);
        const data_it = dataItaliana(gatto.data_arrivo);

        let html = '<li>';
        html += '<article class="card-gatto" aria-labelledby="' + id_titolo + '">';
        html += '<h3 class="sr-solo"> Info sul gatto </h3>';
        html += '<picture>';
        html += '<source srcset="' + ripuliscihtml(immagine) + '">';
        html += '<img src="img/placeholder-gatto.jpg" alt="Placeholder di ' + nome + '" loading="lazy" decoding="async" class="foto-gatto">';
        html += '</picture>';
        html += '<section class="card-gatto-corpo">';
        html += '<h4 id="' + id_titolo + '">' + nome + ' <strong class="badge-nuovo">Nuovo</strong></h4>';
        html += '<ul class="card-gatto-meta" aria-label="Caratteristiche principali">';
        html += '<li class="tag">' + sesso + '</li>';
        html += '<li class="tag">' + eta + '</li>';
        html += '<li class="tag">' + ripuliscihtml(gatto.colore_mantello) + '</li>';
        html += '<li class="tag">Pelo ' + ripuliscihtml(gatto.lunghezza_pelo) + '</li>';
        html += '<li class="tag">' + ripuliscihtml(gatto.razza) + '</li>';
        html += '</ul>';
        html += '<p class="card-gatto-descr">' + ripuliscihtml(gatto.descrizione) + '</p>';
        html += '<dl>';
        html += '<dt>Peso</dt><dd><data value="' + ripuliscihtml(gatto.peso) + '">' + ripuliscihtml(gatto.peso) + ' kg</data></dd>';
        html += '<dt>Occhi</dt><dd>' + ripuliscihtml(gatto.colore_occhi) + '</dd>';
        html += '<dt>Arrivato il</dt><dd><time datetime="' + data_iso + '">' + data_it + '</time></dd>';
        html += '</dl>';
        html += '<a href="adozioni.php" class="btn btn-primario" aria-label="Vai alla pagina adozioni per ' + nome + '">Adotta</a>';
        html += '</section>';
        html += '</article>';
        html += '</li>';
        return html;
    }

    function mostraArrivi(arrivi, errore) {
        if (!contenitore_arrivi || !caricamento_arrivi) return;
        contenitore_arrivi.removeAttribute('aria-busy');
        caricamento_arrivi.style.display = "none";

        if (errore) {
            contenitore_arrivi.innerHTML += messaggioErrore(errore);
            return;
        }

        if (!arrivi || arrivi.length === 0) {
            contenitore_arrivi.innerHTML += '<p>Nessun gatto registrato al momento. Torna presto!</p>';
            return;
        }

        let html = '<ul class="griglia-gatti" aria-label="Nuovi arrivi">';
        arrivi.forEach(function (gatto) {
            html += schedaGatto(gatto);
        });
        html += '</ul>';
        contenitore_arrivi.innerHTML += html;
    }

    function gestisciErroreGenerale() {
        mostraStatistiche(null, 'Statistiche non disponibili al momento. Riprova tra qualche minuto.');
        mostraArrivi(null, 'Impossibile caricare i nuovi arrivi. Riprova tra qualche minuto.');
    }

    fetch('interfaccia/recupera_home.php', { credentials: 'same-origin' })
        .then(function (r) {
            if (!r.ok) return null;
            return r.json();
        })
        .then(function (dati) {
            if (dati === null) {
                gestisciErroreGenerale();
                return;
            }
            mostraStatistiche(dati.statistiche, dati.errore_statistiche);
            mostraArrivi(dati.nuovi_arrivi, dati.errore_arrivi);
        }, function () {
            gestisciErroreGenerale();
        });
})();
