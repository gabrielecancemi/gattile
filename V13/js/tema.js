// Selettore tema a 3 stati: Sistema / Chiaro / Scuro.
//
// Si può cambiare sempre, anche senza consenso cookie. La preferenza viene
// salvata su localStorage (sempre) e nel cookie "tema" solo se il consenso è
// già stato dato. Se i cookie vengono accettati dopo, footer.js avvisa con
// l'evento 'cookieAccettati' e qui scriviamo il cookie.
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
        chiaro:  'Tema: chiaro',
        scuro:   'Tema: scuro'
    };
    const DURATA_MS = 72 * 3600 * 1000; // 72 ore

    function leggiCookie(nome) {
        return document.cookie.split('; ').reduce(function (acc, c) {
            const parti = c.split('=');
            return parti[0] === nome ? decodeURIComponent(parti.slice(1).join('=')) : acc;
        }, '');
    }

    function consensoDato() {
        return document.cookie.split('; ').some(function (c) {
            return c.startsWith('cookie_consenso=');
        });
    }

    function leggiPreferenza() {
        // Il cookie ha la precedenza (impostato lato server per evitare FOUC).
        const da_cookie = leggiCookie('tema');
        if (STATI.indexOf(da_cookie) !== -1) return da_cookie;
        try {
            const da_storage = localStorage.getItem('tema');
            if (STATI.indexOf(da_storage) !== -1) return da_storage;
        } catch (_) { }
        return 'sistema';
    }

    function salvaPreferenza(valore) {
        try {
            if (valore === 'sistema') {
                localStorage.removeItem('tema');
            } else {
                localStorage.setItem('tema', valore);
            }
        } catch (_) { }

        if (consensoDato()) {
            scriviCookieTema(valore);
        }
    }

    function scriviCookieTema(valore) {
        if (valore === 'sistema') {
            document.cookie = 'tema=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; SameSite=Strict';
        } else {
            const scadenza = new Date(Date.now() + DURATA_MS).toUTCString();
            document.cookie = 'tema=' + valore + '; expires=' + scadenza + '; path=/; SameSite=Strict';
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

    // footer.js emette questo evento dopo aver scritto il cookie di consenso.
    document.addEventListener('cookieAccettati', function () {
        const corrente = bottone.getAttribute('data-stato') || 'sistema';
        scriviCookieTema(corrente);
    });
})();
