// Gestione tema: applica il tema iniziale e abilita il selettore a 3 stati

'use strict';

(function () {
    // Applica il tema salvato
    var tema = window.localStorage ? localStorage.getItem('tema') : null;
    console.group('[gestione_tema] Inizializzazione tema');
    console.info('Tema salvato nel localStorage:', tema || 'nessuno (sistema)');
    if (tema === 'chiaro' || tema === 'scuro') {
        document.documentElement.setAttribute('data-tema', tema);
        console.log('✓ Tema applicato:', tema);
    } else {
        document.documentElement.removeAttribute('data-tema');
        console.log('✓ Tema impostato a: sistema (default)');
    }
    console.groupEnd();
})();

(function () {
    function inizializzaTema() {
        const radice = document.querySelector('html');
        const bottone = document.getElementById('toggle-tema');
        if (!bottone) {
            console.warn('[gestione_tema] Bottone toggle-tema non trovato nella pagina');
            return;
        }

        console.group('[gestione_tema] Inizializzazione selettore tema');
        console.info('Bottone trovato:', bottone);

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
                console.info('[gestione_tema] Preferenza letta:', salvato);
                return salvato;
            }
            console.info('[gestione_tema] Nessuna preferenza valida, default: sistema');
            return 'sistema';
        }

        function salvaPreferenza(valore) {
            if (!window.localStorage) {
                console.warn('[gestione_tema] localStorage non disponibile');
                return;
            }
            if (valore === 'sistema') {
                localStorage.removeItem('tema');
                console.info('[gestione_tema] Preferenza rimossa (sistema)');
            } else {
                localStorage.setItem('tema', valore);
                console.info('[gestione_tema] Preferenza salvata:', valore);
            }
        }

        function applica(stato) {
            if (stato === 'sistema') {
                radice.removeAttribute('data-tema');
                console.log('[gestione_tema] Tema applicato: sistema');
            } else {
                radice.setAttribute('data-tema', stato);
                console.log('[gestione_tema] Tema applicato:', stato);
            }
            if (testo) {
                testo.textContent = ETICHETTE[stato];
            }
            bottone.setAttribute('aria-label', 'Cambia tema (attuale: ' + stato + ')');
            bottone.setAttribute('data-stato', stato);
        }

        applica(leggiPreferenza());
        console.log('✓ Selettore tema inizializzato');
        console.groupEnd();

        bottone.addEventListener('click', function () {
            const corrente = bottone.getAttribute('data-stato') || 'sistema';
            const prossimo = STATI[(STATI.indexOf(corrente) + 1) % STATI.length];
            console.info('[gestione_tema] Cambio tema:', corrente, '→', prossimo);
            applica(prossimo);
            salvaPreferenza(prossimo);
        });
    }

    if (document.readyState !== 'loading') {
        console.info('[gestione_tema] DOM già pronto, inizializzo subito');
        inizializzaTema();
    } else {
        console.info('[gestione_tema] In attesa di DOMContentLoaded');
        document.addEventListener('DOMContentLoaded', inizializzaTema);
    }
})();
