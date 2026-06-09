// Selettore tema a 3 stati: Sistema / Chiaro / Scuro.
//
// La preferenza esplicita (chiaro/scuro) viene memorizzata SOLO se l'utente ha
// dato il consenso ai cookie: in quel caso si scrive il cookie tecnico "tema".
// Senza consenso non viene salvato nulla (né cookie né altro storage): il tema
// scelto resta valido per la navigazione corrente tramite l'attributo
// data-tema sull'elemento <html>, ma non viene persistito.
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
    const DURATA_MS = 72 * 3600 * 1000; // 72 ore

    function leggiCookie(nome) {
        return document.cookie.split('; ').reduce(function (acc, c) {
            const parti = c.split('=');
            return parti[0] === nome ? decodeURIComponent(parti.slice(1).join('=')) : acc;
        }, '');
    }

    function consensoDato() {
        return document.cookie.split('; ').some(function (c) {
            return c.startsWith('consenso_cookie=');
        });
    }

    function leggiPreferenza() {
        // La preferenza persiste solo nel cookie "tema" (scritto con consenso).
        const da_cookie = leggiCookie('tema');
        if (STATI.indexOf(da_cookie) !== -1) return da_cookie;
        return 'sistema';
    }

    function salvaPreferenza(valore) {
        // Nessuna persistenza senza consenso ai cookie: in tal caso la scelta
        // vale solo per la navigazione corrente (attributo data-tema).
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
