// Applica il tema salvato

'use strict';

(function () {
    // Lettura dal localStorage
    var tema = window.localStorage ? localStorage.getItem('tema') : null;
    if (tema === 'chiaro' || tema === 'scuro') {
        document.documentElement.setAttribute('data-tema', tema);
    } else {
        document.documentElement.removeAttribute('data-tema');
    }
})();
