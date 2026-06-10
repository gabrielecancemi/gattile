// Banner pubblicitari.
// ------------------------------------------------------------
// Due responsabilità, entrambe non invasive (toccano solo gli
// <aside> dei banner e una classe sull'elemento <html>):
//
//   1) Posizionamento dei banner laterali fissi: restano fissi
//      allo scroll (CSS position: fixed) e qui li "fermiamo"
//      appena sopra il footer, così non lo coprono mai.
//
//   2) Chiusura: il pulsante .banner-chiudi (presente su ogni
//      banner) nasconde TUTTE le pubblicità insieme, aggiungendo
//      la classe .pubblicita-chiuse su <html> (gestita dal CSS).
(function () {
    'use strict';

    // ---- 2) Chiusura di tutte le pubblicità ----
    document.querySelectorAll('.banner-chiudi').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.documentElement.classList.add('pubblicita-chiuse');
        });
    });

    // ---- 1) Posizionamento laterali rispetto al footer ----
    const laterali = Array.prototype.slice.call(
        document.querySelectorAll('.banner-laterale')
    );
    const footer = document.querySelector('.footer');
    if (laterali.length === 0 || !footer) {
        return;
    }

    // Margine di rispetto tra il bordo inferiore del banner e il footer.
    const MARGINE = 16;

    let inAttesa = false;

    function aggiorna() {
        inAttesa = false;

        // Se i banner laterali sono nascosti (versione orizzontale
        // attiva sotto la soglia, oppure pubblicità chiuse), non
        // c'è nulla da posizionare.
        const visibili = laterali.filter(function (el) {
            return getComputedStyle(el).display !== 'none';
        });
        if (visibili.length === 0) {
            laterali.forEach(function (el) {
                el.classList.remove('banner-fermo');
                el.style.removeProperty('--banner-top');
            });
            return;
        }

        const footerTop = footer.getBoundingClientRect().top;
        const viewport = window.innerHeight;

        visibili.forEach(function (el) {
            const altezza = el.offsetHeight;
            // Posizione "top" che terrebbe il banner centrato.
            const topCentrato = (viewport - altezza) / 2;
            // Limite massimo per non invadere il footer.
            const topMassimo = footerTop - MARGINE - altezza;

            if (footerTop < viewport && topMassimo < topCentrato) {
                // Il footer è in vista e "spinge" il banner verso l'alto.
                const top = Math.max(8, topMassimo);
                el.style.setProperty('--banner-top', top + 'px');
                el.classList.add('banner-fermo');
            } else {
                // Footer lontano: banner centrato come da CSS.
                el.classList.remove('banner-fermo');
                el.style.removeProperty('--banner-top');
            }
        });
    }

    function pianifica() {
        if (!inAttesa) {
            inAttesa = true;
            window.requestAnimationFrame(aggiorna);
        }
    }

    window.addEventListener('scroll', pianifica, { passive: true });
    window.addEventListener('resize', pianifica);
    window.addEventListener('load', aggiorna);
    aggiorna();
})();
