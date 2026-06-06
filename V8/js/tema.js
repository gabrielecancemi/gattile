/**
 * tema.js — Interruttore tema chiaro/scuro.
 *
 * Il tema è memorizzato in un cookie tecnico "tema" (valori: 'chiaro' | 'scuro')
 * e applicato come attributo data-tema sull'elemento <html>, così il CSS può
 * ridefinire le variabili dei colori. Il valore iniziale è già impostato lato
 * server (layout.php) per evitare il lampo di tema errato (FOUC).
 */
'use strict';

(function () {
    var radice = document.documentElement;
    var btn = document.getElementById('toggle-tema');

    function leggiCookie(nome) {
        return document.cookie.split('; ').reduce(function (acc, c) {
            var parti = c.split('=');
            return parti[0] === nome ? decodeURIComponent(parti.slice(1).join('=')) : acc;
        }, '');
    }

    function salvaTema(valore) {
        var scad = new Date(Date.now() + 365 * 24 * 3600 * 1000).toUTCString();
        document.cookie = 'tema=' + valore + '; expires=' + scad + '; path=/; SameSite=Strict';
    }

    function aggiornaPulsante(tema) {
        if (!btn) return;
        var scuro = tema === 'scuro';
        btn.setAttribute('aria-pressed', String(scuro));
        btn.setAttribute('aria-label', scuro ? 'Attiva tema chiaro' : 'Attiva tema scuro');
        var icona = btn.querySelector('.icona-tema');
        if (icona) icona.textContent = scuro ? '☀️' : '🌙';
    }

    // Allinea lo stato del pulsante al tema corrente (impostato dal server)
    var temaCorrente = radice.getAttribute('data-tema') === 'scuro' ? 'scuro' : 'chiaro';
    // Se non c'è cookie ma il sistema preferisce lo scuro, rispetta la preferenza
    if (!leggiCookie('tema') && window.matchMedia &&
        window.matchMedia('(prefers-color-scheme: dark)').matches) {
        temaCorrente = 'scuro';
        radice.setAttribute('data-tema', 'scuro');
    }
    aggiornaPulsante(temaCorrente);

    if (btn) {
        btn.addEventListener('click', function () {
            var nuovo = radice.getAttribute('data-tema') === 'scuro' ? 'chiaro' : 'scuro';
            radice.setAttribute('data-tema', nuovo);
            salvaTema(nuovo);
            aggiornaPulsante(nuovo);
        });
    }
})();
