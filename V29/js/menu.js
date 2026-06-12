// Apertura/chiusura del menu su mobile

'use strict';

(function () {
    const bottone = document.querySelector('.menu-toggle');
    if (!bottone) return;

    //testo "Menu"
    const etichetta = bottone.lastChild;

    bottone.addEventListener('click', function () {
        const era_aperto = this.getAttribute('aria-expanded') === 'true';
        const nuovo_stato = !era_aperto;
        this.setAttribute('aria-expanded', String(nuovo_stato));
        this.setAttribute('aria-label', nuovo_stato ? 'Chiudi menu di navigazione' : 'Apri menu di navigazione');
        this.classList.toggle('aperto', nuovo_stato);

        // if inserito solo per evitare errori nel codice
        if (etichetta && etichetta.nodeType === Node.TEXT_NODE) {
            etichetta.textContent = nuovo_stato ? '✕ Chiudi' : '☰ Menu';
        }

        const nav = document.getElementById('menu-principale');
        const account = document.getElementById('autenticazione');
        const tema = document.getElementById('toggle-tema');
        if (nav) nav.classList.toggle('aperto', nuovo_stato);
        if (account) account.classList.toggle('aperto', nuovo_stato);
        if (tema) tema.classList.toggle('aperto', nuovo_stato);
    });
})();
