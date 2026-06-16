// Gestione tema: applica il tema iniziale e abilita il selettore a 3 stati

'use strict';

(function () {
    // Applica il tema salvato
    var tema = window.localStorage ? localStorage.getItem('tema') : null;
    if (tema === 'chiaro' || tema === 'scuro') {
        document.documentElement.setAttribute('data-tema', tema);
    } else {
        document.documentElement.removeAttribute('data-tema');
    }
})();

(function () {
    function inizializzaTema() {
        const radice = document.querySelector('html');
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
            const salvato = window.localStorage ? localStorage.getItem('tema') : null;
            if (STATI.indexOf(salvato) !== -1) {
                return salvato;
            }
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
            if (testo) {
                testo.textContent = ETICHETTE[stato];
            }
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
    }

    if (document.readyState !== 'loading') {
        inizializzaTema();
    } else {
        document.addEventListener('DOMContentLoaded', inizializzaTema);
    }
})();
