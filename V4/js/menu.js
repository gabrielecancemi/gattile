/**
 * menu.js — Toggle hamburger menu su mobile.
 * Alterna le classi "aperto" su nav e area account.
 */
'use strict';

(function () {
    var btn = document.querySelector('.menu-toggle');
    if (!btn) return;

    btn.addEventListener('click', function () {
        var aperto = this.getAttribute('aria-expanded') === 'true';
        var nuovoStato = !aperto;
        this.setAttribute('aria-expanded', String(nuovoStato));
        this.textContent = nuovoStato ? '✕' : '☰';

        var nav = document.getElementById('menu-principale');
        var account = document.getElementById('autenticazione');
        if (nav) nav.classList.toggle('aperto', nuovoStato);
        if (account) account.classList.toggle('aperto', nuovoStato);
    });

    console.log('[Menu] Script caricato');
})();
