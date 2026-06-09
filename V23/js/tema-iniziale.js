// Applica subito il tema salvato per evitare il flash (FOUC) prima del render
// del body. Va nel <head> senza defer/async. 'chiaro'/'scuro' forzano il tema;
// qualunque altro valore (o preferenza assente) = tema di sistema.
//
// La preferenza è conservata nel localStorage (chiave "tema"): è una
// impostazione tecnica di sola interfaccia, quindi è utilizzabile anche senza
// consenso ai cookie.
'use strict';

(function () {
    // Lettura diretta dal localStorage (Web Storage API): operazione di sola
    // lettura, senza necessità di gestione delle eccezioni.
    var tema = window.localStorage ? localStorage.getItem('tema') : null;
    if (tema === 'chiaro' || tema === 'scuro') {
        document.documentElement.setAttribute('data-tema', tema);
    } else {
        document.documentElement.removeAttribute('data-tema');
    }
})();
