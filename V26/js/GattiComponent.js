// GattiComponent.jsx — Componente React: lista, filtro, ordinamento e
// selezione gatti.

'use strict';

(function () {
  const {
    useState,
    useEffect,
    useCallback
  } = React;
  function etaInParole(mesi) {
    if (mesi < 12) return mesi + ' ' + (mesi === 1 ? 'mese' : 'mesi');
    const anni = Math.floor(mesi / 12);
    const resto = mesi % 12;
    let testo = anni + ' ' + (anni === 1 ? 'anno' : 'anni');
    if (resto > 0) testo += ' e ' + resto + ' ' + (resto === 1 ? 'mese' : 'mesi');
    return testo;
  }
  function emettiSelezione(gatti) {
    document.dispatchEvent(new CustomEvent('gattiSelezionatiAggiornati', {
      detail: {
        gatti
      },
      bubbles: true
    }));
  }

  /* Card singola */

  function CardGatto({
    gatto,
    selezionabile,
    selezionata,
    onToggle,
    nuovo
  }) {
    const etichetta_sesso = gatto.sesso === 'M' ? 'Maschio' : 'Femmina';
    function gestisciClick() {
      if (selezionabile) onToggle(gatto);
    }
    function gestisciTasto(evento) {
      if (selezionabile && (evento.key === 'Enter' || evento.key === ' ')) {
        evento.preventDefault();
        onToggle(gatto);
      }
    }
    return /*#__PURE__*/React.createElement("li", null, /*#__PURE__*/React.createElement("article", {
      className: 'card-gatto' + (selezionata ? ' selezionata' : ''),
      onClick: gestisciClick,
      onKeyDown: gestisciTasto,
      tabIndex: selezionabile ? 0 : undefined,
      role: selezionabile ? 'checkbox' : undefined,
      "aria-checked": selezionabile ? selezionata : undefined,
      "aria-labelledby": 'gatto-' + gatto.id,
      "aria-label": selezionabile ? gatto.nome + (selezionata ? ' — selezionato' : ' — clicca per selezionare') : undefined
    }, /*#__PURE__*/React.createElement("svg", {
      className: "card-badge-selezione",
      viewBox: "0 0 32 32",
      width: "32",
      height: "32",
      "aria-hidden": "true",
      focusable: "false"
    }, /*#__PURE__*/React.createElement("circle", {
      cx: "16",
      cy: "16",
      r: "16",
      fill: "saddlebrown"
    }), /*#__PURE__*/React.createElement("path", {
      d: "M9 16.5l4.5 4.5L23 11",
      fill: "none",
      stroke: "white",
      strokeWidth: "3",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    })), /*#__PURE__*/React.createElement("picture", null, /*#__PURE__*/React.createElement("source", {
      srcset: gatto.foto
    }), /*#__PURE__*/React.createElement("img", {
      src: "img/placeholder-gatto.svg",
      alt: 'Placeholder di ' + gatto.nome,
      loading: "lazy",
      decoding: "async",
      class: "foto-gatto"
    })), /*#__PURE__*/React.createElement("section", {
      className: "card-gatto-corpo"
    }, /*#__PURE__*/React.createElement("h3", {
      id: 'gatto-' + gatto.id
    }, gatto.nome, nuovo && /*#__PURE__*/React.createElement("strong", {
      className: "badge-nuovo"
    }, "Nuovo")), /*#__PURE__*/React.createElement("ul", {
      className: "card-gatto-meta",
      "aria-label": "Caratteristiche principali"
    }, /*#__PURE__*/React.createElement("li", {
      className: "tag"
    }, etichetta_sesso), /*#__PURE__*/React.createElement("li", {
      className: "tag"
    }, etaInParole(gatto.eta)), /*#__PURE__*/React.createElement("li", {
      className: "tag"
    }, gatto.colore_mantello), /*#__PURE__*/React.createElement("li", {
      className: "tag"
    }, gatto.lunghezza_pelo), /*#__PURE__*/React.createElement("li", {
      className: "tag"
    }, gatto.razza)), /*#__PURE__*/React.createElement("p", {
      className: "card-gatto-descr"
    }, gatto.descrizione), /*#__PURE__*/React.createElement("dl", null, /*#__PURE__*/React.createElement("dt", null, "Peso"), /*#__PURE__*/React.createElement("dd", null, /*#__PURE__*/React.createElement("data", {
      value: gatto.peso
    }, gatto.peso, " kg")), /*#__PURE__*/React.createElement("dt", null, "Occhi"), /*#__PURE__*/React.createElement("dd", null, gatto.colore_occhi), /*#__PURE__*/React.createElement("dt", null, "Arrivato il"), /*#__PURE__*/React.createElement("dd", null, /*#__PURE__*/React.createElement("time", {
      dateTime: gatto.data_arrivo
    }, new Date(gatto.data_arrivo).toLocaleDateString('it-IT')))))));
  }

  /* Componente principale */

  function GattiApp({
    utenteLoggato,
    isAdmin
  }) {
    const [gatti, setGatti] = useState([]);
    const [caricamento, setCaricamento] = useState(true);
    const [errore, setErrore] = useState('');
    const [ricerca, setRicerca] = useState('');
    const [ordinamento, setOrdinamento] = useState('data_arrivo_desc');
    const [selezionati, setSelezionati] = useState(new Set());

    // Recupera i gatti dal backend al primo render.
    useEffect(function () {
      setCaricamento(true);
      setErrore('');
      // Errore di rete o risposta non valida
      function gestisciErrore(err) {
        console.error('[GattiComponent] errore:', err.message);
        setErrore('Impossibile caricare i gatti, riprova tra qualche minuto.');
        setCaricamento(false);
      }
      fetch('api/gatti.php', {
        credentials: 'same-origin'
      }).then(function (r) {
        if (!r.ok) return Promise.reject(new Error('Risposta server: ' + r.status));
        return r.json();
      }).then(function (dati) {
        if (dati.errore) return Promise.reject(new Error(dati.errore));
        setGatti(dati.gatti || []);
        setCaricamento(false);
      }, gestisciErrore);
    }, []);

    // A ogni cambio di selezione notifica il form Vanilla JS.
    useEffect(function () {
      emettiSelezione(gatti.filter(function (g) {
        return selezionati.has(g.id);
      }));
    }, [selezionati, gatti]);

    // A prenotazione avvenuta il form Vanilla JS chiede di azzerare la
    // selezione: cosi' le card tornano deselezionate
    useEffect(function () {
      function azzera() {
        setSelezionati(new Set());
      }
      document.addEventListener('gattiDeselezionaTutti', azzera);
      return function () {
        document.removeEventListener('gattiDeselezionaTutti', azzera);
      };
    }, []);
    const cambiaSelezione = useCallback(function (gatto) {
      setSelezionati(function (precedenti) {
        const nuovi = new Set(precedenti);
        if (nuovi.has(gatto.id)) {
          nuovi.delete(gatto.id);
        } else {
          nuovi.add(gatto.id);
        }
        return nuovi;
      });
    }, []);

    // Filtro per testo + ordinamento.
    const gatti_visibili = gatti.filter(function (g) {
      if (!ricerca.trim()) return true;
      const termine = ricerca.trim().toLowerCase();
      return g.nome.toLowerCase().includes(termine) || g.descrizione.toLowerCase().includes(termine);
    }).sort(function (a, b) {
      switch (ordinamento) {
        case 'eta_asc':
          return a.eta - b.eta;
        case 'eta_desc':
          return b.eta - a.eta;
        case 'colore_asc':
          return a.colore_mantello.localeCompare(b.colore_mantello);
        case 'data_arrivo_asc':
          return new Date(a.data_arrivo) - new Date(b.data_arrivo);
        default:
          return new Date(b.data_arrivo) - new Date(a.data_arrivo);
      }
    });

    // Gli ultimi 2 gatti arrivati (per data di arrivo) ricevono il badge "Nuovo"
    const id_nuovi = gatti.slice().sort(function (a, b) {
      return new Date(b.data_arrivo) - new Date(a.data_arrivo);
    }).slice(0, 2).map(function (g) {
      return g.id;
    });
    if (caricamento) {
      return /*#__PURE__*/React.createElement("p", {
        className: "caricamento",
        role: "status",
        "aria-live": "polite"
      }, "Caricamento schede gatti in corso\u2026");
    }
    if (errore) {
      return /*#__PURE__*/React.createElement("output", {
        className: "messaggio messaggio-errore",
        role: "alert",
        "aria-live": "assertive"
      }, /*#__PURE__*/React.createElement("strong", {
        className: "messaggio-tag",
        "aria-hidden": "true"
      }, "Errore"), " ", errore);
    }
    return /*#__PURE__*/React.createElement("section", {
      className: "react-gatti-wrap",
      "aria-labelledby": "titolo-lista-gatti"
    }, /*#__PURE__*/React.createElement("h2", {
      id: "titolo-lista-gatti",
      className: "sr-solo"
    }, "Lista gatti disponibili"), /*#__PURE__*/React.createElement("form", {
      className: "barra-controlli",
      role: "search",
      "aria-label": "Filtra e ordina i gatti",
      onSubmit: function (e) {
        e.preventDefault();
      }
    }, /*#__PURE__*/React.createElement("label", {
      htmlFor: "ricerca-gatto"
    }, "Cerca per nome o descrizione", /*#__PURE__*/React.createElement("input", {
      type: "search",
      id: "ricerca-gatto",
      value: ricerca,
      onChange: function (e) {
        setRicerca(e.target.value);
      },
      placeholder: "Es. giocoso, bianco\u2026",
      "aria-controls": "lista-gatti"
    })), /*#__PURE__*/React.createElement("label", {
      htmlFor: "ordina-gatti"
    }, "Ordina per", /*#__PURE__*/React.createElement("select", {
      id: "ordina-gatti",
      value: ordinamento,
      onChange: function (e) {
        setOrdinamento(e.target.value);
      }
    }, /*#__PURE__*/React.createElement("option", {
      value: "data_arrivo_desc"
    }, "Data arrivo (pi\xF9 recente)"), /*#__PURE__*/React.createElement("option", {
      value: "data_arrivo_asc"
    }, "Data arrivo (meno recente)"), /*#__PURE__*/React.createElement("option", {
      value: "eta_asc"
    }, "Et\xE0 (pi\xF9 giovane)"), /*#__PURE__*/React.createElement("option", {
      value: "eta_desc"
    }, "Et\xE0 (pi\xF9 vecchio)"), /*#__PURE__*/React.createElement("option", {
      value: "colore_asc"
    }, "Colore mantello (A-Z)")))), /*#__PURE__*/React.createElement("p", {
      "aria-live": "polite",
      className: "sr-solo",
      role: "status"
    }, gatti_visibili.length === gatti.length ? gatti.length + ' gatti disponibili.' : gatti_visibili.length + ' gatti trovati su ' + gatti.length + '.', selezionati.size > 0 ? ' ' + selezionati.size + ' selezionati.' : ''), utenteLoggato && !isAdmin && /*#__PURE__*/React.createElement("p", {
      "aria-live": "polite"
    }, "Clicca su una card per selezionare il gatto.", selezionati.size > 0 && /*#__PURE__*/React.createElement("strong", null, " ", selezionati.size, " ", selezionati.size === 1 ? 'gatto selezionato' : 'gatti selezionati', ".")), gatti_visibili.length === 0 ? /*#__PURE__*/React.createElement("p", {
      role: "status",
      "aria-live": "polite"
    }, "Nessun gatto corrisponde alla ricerca \xAB", ricerca, "\xBB.") : /*#__PURE__*/React.createElement("ul", {
      id: "lista-gatti",
      className: "griglia-gatti",
      "aria-label": "Elenco gatti disponibili"
    }, gatti_visibili.map(function (gatto) {
      return /*#__PURE__*/React.createElement(CardGatto, {
        key: gatto.id,
        gatto: gatto,
        selezionabile: utenteLoggato && !isAdmin,
        selezionata: selezionati.has(gatto.id),
        onToggle: cambiaSelezione,
        nuovo: id_nuovi.indexOf(gatto.id) !== -1
      });
    })));
  }

  /* Creazione */

  const radice = document.getElementById('react-gatti-root');
  if (!radice) {
    console.error('[GattiComponent] elemento root non trovato');
    return;
  }
  const utente_loggato = radice.getAttribute('data-utente-loggato') === 'true';
  const is_admin = radice.getAttribute('data-is-admin') === 'true';
  radice.removeAttribute('aria-busy');
  ReactDOM.createRoot(radice).render(React.createElement(GattiApp, {
    utenteLoggato: utente_loggato,
    isAdmin: is_admin
  }));
})();
