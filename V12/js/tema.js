/**
 * tema.js — Selettore tema a 3 stati: Sistema / Chiaro / Scuro.
 *
 * Il tema si può cambiare SEMPRE, anche senza cookie accettati.
 * La preferenza viene salvata:
 *   - in localStorage (sempre, senza consenso cookie): persiste nella sessione
 *     corrente e anche tra sessioni sullo stesso browser;
 *   - nel cookie "tema" (solo se il consenso cookie è già stato dato):
 *     persiste tra browser diversi e dispositivi diversi.
 *
 * Se i cookie vengono accettati DOPO aver scelto un tema, il cookie
 * viene scritto al momento dell'accettazione (footer.js lo notifica
 * tramite l'evento 'cookieAccettati').
 *
 * Tre stati:
 *   "sistema" → segue prefers-color-scheme (nessun attributo data-tema)
 *   "chiaro"  → forza tema chiaro (data-tema="chiaro")
 *   "scuro"   → forza tema scuro  (data-tema="scuro")
 */
'use strict';

(function () {
    const radice = document.documentElement;
    const btn    = document.getElementById('toggle-tema');
    if (!btn) return;

    const testo  = btn.querySelector('.testo-tema');
    const STATI  = ['sistema', 'chiaro', 'scuro'];
    const ETICHETTE = {
        sistema: 'Tema: sistema',
        chiaro:  'Tema: chiaro',
        scuro:   'Tema: scuro'
    };
    const DURATA_MS = 72 * 3600 * 1000; // 72 ore

    /* ── Lettura preferenza ───────────────────────────────────── */

    function leggiCookie(nome) {
        return document.cookie.split('; ').reduce(function (acc, c) {
            const p = c.split('=');
            return p[0] === nome ? decodeURIComponent(p.slice(1).join('=')) : acc;
        }, '');
    }

    function haCookieConsensato() {
        return document.cookie.split('; ').some(function (c) {
            return c.startsWith('cookie_consenso=');
        });
    }

    function leggiPreferenza() {
        // Cookie ha precedenza (impostato lato server per evitare FOUC)
        const dalCookie = leggiCookie('tema');
        if (STATI.indexOf(dalCookie) !== -1) return dalCookie;
        // Fallback: localStorage
        try {
            const dalStorage = localStorage.getItem('tema');
            if (STATI.indexOf(dalStorage) !== -1) return dalStorage;
        } catch (_) { /* localStorage non disponibile */ }
        return 'sistema';
    }

    /* ── Salvataggio preferenza ───────────────────────────────── */

    function salvaPreferenza(valore) {
        // Sempre su localStorage (non richiede consenso)
        try {
            if (valore === 'sistema') {
                localStorage.removeItem('tema');
            } else {
                localStorage.setItem('tema', valore);
            }
        } catch (_) { /* ignore */ }

        // Cookie solo se il consenso è già stato dato
        if (haCookieConsensato()) {
            scriviCookieTema(valore);
        }
    }

    function scriviCookieTema(valore) {
        if (valore === 'sistema') {
            document.cookie = 'tema=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; SameSite=Strict';
        } else {
            const scad = new Date(Date.now() + DURATA_MS).toUTCString();
            document.cookie = 'tema=' + valore + '; expires=' + scad + '; path=/; SameSite=Strict';
        }
    }

    /* ── Applicazione tema ────────────────────────────────────── */

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

    /* ── Inizializzazione ─────────────────────────────────────── */

    const iniziale = leggiPreferenza();
    applica(iniziale);

    /* ── Click sul pulsante ───────────────────────────────────── */

    btn.addEventListener('click', function () {
        const corrente = btn.getAttribute('data-stato') || 'sistema';
        const prossimo = STATI[(STATI.indexOf(corrente) + 1) % STATI.length];
        applica(prossimo);
        salvaPreferenza(prossimo);
    });

    /* ── Quando i cookie vengono accettati: scrivi anche il cookie tema ── */
    // footer.js emette questo evento dopo aver scritto il cookie di consenso.
    document.addEventListener('cookieAccettati', function () {
        const corrente = btn.getAttribute('data-stato') || 'sistema';
        scriviCookieTema(corrente);
    });
})();
