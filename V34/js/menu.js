// Apertura/chiusura del menu su mobile

'use strict';

(function () {
    const bottone = document.querySelector('.menu-toggle');
    if (!bottone) {
        return;
    }

    //testo "Menu"
    const etichetta = bottone.lastChild;

    // Aggiunge o rimuove una classe
    function impostaClasse(elemento, classe, presente) {
        if (!elemento) return;
        // Lista delle classi correnti
        const attuali = elemento.className.split(' ');
        let classi = [];
        for (let i = 0; i < attuali.length; i++) {
            if (attuali[i] !== '' && attuali[i] !== classe) {
                classi.push(attuali[i]);
            }
        }
        if (presente) {
            classi.push(classe);
        }
        elemento.className = classi.join(' ');
    }

    bottone.addEventListener('click', function () {
        const era_aperto = this.getAttribute('aria-expanded') === 'true';
        const nuovo_stato = !era_aperto;
        this.setAttribute('aria-expanded', String(nuovo_stato));
        this.setAttribute('aria-label', nuovo_stato ? 'Chiudi menu di navigazione' : 'Apri menu di navigazione');
        impostaClasse(this, 'aperto', nuovo_stato);

        // if inserito solo per evitare errori nel codice
        if (etichetta && etichetta.nodeType === Node.TEXT_NODE) {
            etichetta.textContent = nuovo_stato ? '✕ Chiudi' : '☰ Menu';
        }

        const nav = document.getElementById('menu-principale');
        const account = document.getElementById('autenticazione');
        const tema = document.getElementById('controlli-testata');
        impostaClasse(nav, 'aperto', nuovo_stato);
        impostaClasse(account, 'aperto', nuovo_stato);
        impostaClasse(tema, 'aperto', nuovo_stato);
    });
})();
