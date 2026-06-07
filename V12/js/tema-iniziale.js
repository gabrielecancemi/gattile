/**
 * tema-iniziale.js — Applica subito il tema salvato per evitare il "flash"
 * (FOUC) prima che il resto della pagina venga mostrato.
 *
 * Va incluso nel <head> SENZA defer/async, così viene eseguito prima del
 * rendering del <body>. È stato estratto da uno <script> inline presente in
 * precedenza in layout.php: nel sito non deve esserci codice JavaScript
 * scritto direttamente dentro l'HTML.
 *
 * 'chiaro'/'scuro' forzano il tema; qualsiasi altro valore (o cookie assente)
 * = tema di sistema (nessun attributo data-tema -> segue prefers-color-scheme).
 */
'use strict';

(function () {
    try {
        var m = document.cookie.match(/(?:^|; *)tema=([^;]+)/);
        var t = m ? decodeURIComponent(m[1]) : '';
        if (t === 'chiaro' || t === 'scuro') {
            document.documentElement.setAttribute('data-tema', t);
        } else {
            document.documentElement.removeAttribute('data-tema');
        }
    } catch (e) { }
})();
