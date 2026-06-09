// Applica subito il tema salvato per evitare il flash (FOUC) prima del render
// del body. Va nel <head> senza defer/async. 'chiaro'/'scuro' forzano il tema;
// qualunque altro valore (o cookie assente) = tema di sistema.
'use strict';

(function () {
    // Lettura diretta del cookie (Web API document.cookie): operazioni di sola
    // lettura su stringhe e DOM, senza necessità di gestione delle eccezioni.
    var corrispondenza = document.cookie.match(/(?:^|; *)tema=([^;]+)/);
    var tema = corrispondenza ? decodeURIComponent(corrispondenza[1]) : '';
    if (tema === 'chiaro' || tema === 'scuro') {
        document.documentElement.setAttribute('data-tema', tema);
    } else {
        document.documentElement.removeAttribute('data-tema');
    }
})();
