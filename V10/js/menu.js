/*
 * menu.js — Toggle menu su mobile.
 * Alterna la classe "aperto" su nav e area account e aggiorna
 * etichetta/stato del pulsante (icona disegnata via CSS, niente emoji).
 */
'use strict';

(function () {
    const btn = document.querySelector('.menu-toggle');
    if (!btn) return;

    const etichetta = btn.lastChild; // nodo di testo " Menu"

    btn.addEventListener('click', function () {
        const aperto = this.getAttribute('aria-expanded') === 'true';
        const nuovoStato = !aperto;
        this.setAttribute('aria-expanded', String(nuovoStato));
        this.setAttribute('aria-label', nuovoStato ? 'Chiudi menu di navigazione' : 'Apri menu di navigazione');
        this.classList.toggle('aperto', nuovoStato);

        // Aggiorna il testo visibile mantenendo l'icona (primo figlio <b>)
        if (etichetta && etichetta.nodeType === Node.TEXT_NODE) {
            etichetta.textContent = nuovoStato ? ' Chiudi' : ' Menu';
        }

        const nav = document.getElementById('menu-principale');
        const account = document.getElementById('autenticazione');
        if (nav) nav.classList.toggle('aperto', nuovoStato);
        if (account) account.classList.toggle('aperto', nuovoStato);
    });
})();
