// Selettore tema a 3 stati: Sistema / Chiaro / Scuro.
//
// La preferenza esplicita (chiaro/scuro) viene memorizzata nel localStorage
// (chiave "tema"). Essendo una semplice impostazione tecnica di interfaccia
// (nessun dato personale, nessun tracciamento), è utilizzabile anche senza il
// consenso ai cookie: la scelta persiste quindi tra le visite a prescindere
// dal banner.
//
// Il rilevamento della preferenza di sistema è demandato al CSS, tramite la
// media query (prefers-color-scheme: dark) applicata a html:not([data-tema]):
// il JavaScript imposta l'attributo data-tema solo quando l'utente sceglie
// manualmente chiaro o scuro; in stato "sistema" l'attributo viene rimosso e
// vince quindi la preferenza di sistema rilevata dal CSS.
//
//   "sistema" -> segue prefers-color-scheme (nessun attributo data-tema)
//   "chiaro"  -> forza tema chiaro
//   "scuro"   -> forza tema scuro
'use strict';

(function () {
    const radice = document.documentElement;
    const bottone = document.getElementById('toggle-tema');
    if (!bottone) return;

    const testo = bottone.querySelector('.testo-tema');
    const STATI = ['sistema', 'chiaro', 'scuro'];
    const ETICHETTE = {
        sistema: 'Tema: sistema',
        chiaro: 'Tema: chiaro',
        scuro: 'Tema: scuro'
    };

    function leggiPreferenza() {
        // La preferenza persiste nel localStorage (impostazione tecnica).
        const salvato = window.localStorage ? localStorage.getItem('tema') : null;
        if (STATI.indexOf(salvato) !== -1) return salvato;
        return 'sistema';
    }

    function salvaPreferenza(valore) {
        if (!window.localStorage) return;
        if (valore === 'sistema') {
            localStorage.removeItem('tema');
        } else {
            localStorage.setItem('tema', valore);
        }
    }

    function applica(stato) {
        if (stato === 'sistema') {
            radice.removeAttribute('data-tema');
        } else {
            radice.setAttribute('data-tema', stato);
        }
        if (testo) testo.textContent = ETICHETTE[stato];
        bottone.setAttribute('aria-label', 'Cambia tema (attuale: ' + stato + ')');
        bottone.setAttribute('data-stato', stato);
    }

    applica(leggiPreferenza());

    bottone.addEventListener('click', function () {
        const corrente = bottone.getAttribute('data-stato') || 'sistema';
        const prossimo = STATI[(STATI.indexOf(corrente) + 1) % STATI.length];
        applica(prossimo);
        salvaPreferenza(prossimo);
    });
})();
