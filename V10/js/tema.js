/**
 * tema.js — Selettore tema a 3 stati: Sistema / Chiaro / Scuro.
 *
 * - "sistema": nessun attributo data-tema sull'<html>, il tema segue
 *   prefers-color-scheme (gestito interamente dal CSS).
 * - "chiaro" / "scuro": l'utente forza il tema; viene impostato
 *   l'attributo data-tema sull'<html> e salvato nel cookie tecnico "tema"
 *   (durata 72 ore), così la scelta è ricordata tra le visite.
 *
 * Il valore iniziale per "chiaro"/"scuro" è già scritto lato server in
 * layout.php (niente FOUC). Qui gestiamo il ciclo col tasto e l'etichetta.
 */
'use strict';

(function () {
    const radice = document.documentElement;
    const btn = document.getElementById('toggle-tema');
    if (!btn) return;

    const testo = btn.querySelector('.testo-tema');
    const STATI = ['sistema', 'chiaro', 'scuro'];
    const ETICHETTE = { sistema: 'Tema: sistema', chiaro: 'Tema: chiaro', scuro: 'Tema: scuro' };
    const DURATA = 72 * 3600 * 1000; // 72 ore

    function leggiCookie(nome) {
        return document.cookie.split('; ').reduce(function (acc, c) {
            const p = c.split('=');
            return p[0] === nome ? decodeURIComponent(p.slice(1).join('=')) : acc;
        }, '');
    }

    function salvaTema(valore) {
        if (valore === 'sistema') {
            // Rimuove il cookie: si torna a seguire il sistema
            document.cookie = 'tema=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; SameSite=Strict';
        } else {
            const scad = new Date(Date.now() + DURATA).toUTCString();
            document.cookie = 'tema=' + valore + '; expires=' + scad + '; path=/; SameSite=Strict';
        }
    }

    function applica(stato) {
        if (stato === 'sistema') {
            radice.removeAttribute('data-tema');
        } else {
            radice.setAttribute('data-tema', stato);
        }
        if (testo) testo.textContent = ETICHETTE[stato];
        btn.setAttribute('aria-label', 'Cambia tema (attuale: ' + stato + ')');
        btn.setAttribute('data-stato', stato);
    }

    // Stato iniziale: dal cookie se valido, altrimenti "sistema"
    const iniziale = leggiCookie('tema');
    if (STATI.indexOf(iniziale) === -1) iniziale = 'sistema';
    applica(iniziale);

    btn.addEventListener('click', function () {
        const corrente = btn.getAttribute('data-stato') || 'sistema';
        const prossimo = STATI[(STATI.indexOf(corrente) + 1) % STATI.length];
        applica(prossimo);
        salvaTema(prossimo);
    });
})();
