// GattiComponent.jsx — Componente React: lista, filtro, ordinamento e
// selezione gatti.
//
// La struttura HTML della card è volutamente IDENTICA a quella prodotta dal
// renderer PHP condiviso (includes/card_gatto.php), così i "Nuovi arrivi"
// della home e le card della pagina adozioni condividono lo stesso stile
// (.card-gatto, .card-gatto-corpo, ecc.) senza duplicare il CSS.
//
// La selezione viene comunicata al form Vanilla JS con un CustomEvent
// ('gattiSelezionatiAggiornati'): disaccoppia React e Vanilla senza variabili
// globali e resta testabile in isolamento.
'use strict';

(function () {
    const { useState, useEffect, useCallback } = React;

    /* Utilità ------------------------------------------------------- */

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
            detail: { gatti },
            bubbles: true,
        }));
    }

    /* Card singola — struttura identica al renderer PHP ------------- */

    function CardGatto({ gatto, selezionabile, selezionata, onToggle }) {
        const etichetta_sesso = gatto.sesso === 'M' ? 'Maschio' : 'Femmina';

        function gestisciClick() { if (selezionabile) onToggle(gatto); }
        function gestisciTasto(evento) {
            if (selezionabile && (evento.key === 'Enter' || evento.key === ' ')) {
                evento.preventDefault();
                onToggle(gatto);
            }
        }

        return (
            <li>
                <article
                    className={'card-gatto' + (selezionata ? ' selezionata' : '')}
                    onClick={gestisciClick}
                    onKeyDown={gestisciTasto}
                    tabIndex={selezionabile ? 0 : undefined}
                    role={selezionabile ? 'checkbox' : undefined}
                    aria-checked={selezionabile ? selezionata : undefined}
                    aria-labelledby={'gatto-' + gatto.id}
                    aria-label={
                        selezionabile
                            ? gatto.nome + (selezionata ? ' — selezionato' : ' — clicca per selezionare')
                            : undefined
                    }
                >
                    {/* Badge selezione — disegnato via CSS, nessuna emoji */}
                    <mark className="card-badge-selezione" aria-hidden="true"></mark>

                    <figure>
                        <img
                            src={gatto.img}
                            alt={'Sagoma stilizzata — foto di ' + gatto.nome + ' non ancora disponibile'}
                            width="320"
                            height="240"
                            loading="lazy"
                            decoding="async"
                        />
                        <figcaption className="sr-solo">Placeholder foto per {gatto.nome}</figcaption>
                    </figure>

                    <div className="card-gatto-corpo">
                        <h3 id={'gatto-' + gatto.id}>{gatto.nome}</h3>

                        <ul className="card-gatto-meta" aria-label="Caratteristiche principali">
                            <li className="tag">{etichetta_sesso}</li>
                            <li className="tag">{etaInParole(gatto.eta)}</li>
                            <li className="tag">{gatto.colore_mantello}</li>
                            <li className="tag">{gatto.lunghezza_pelo}</li>
                            <li className="tag">{gatto.razza}</li>
                        </ul>

                        <p className="card-gatto-descr">{gatto.descrizione}</p>

                        <dl>
                            <dt>Peso</dt>
                            <dd><data value={gatto.peso}>{gatto.peso} kg</data></dd>
                            <dt>Occhi</dt>
                            <dd>{gatto.colore_occhi}</dd>
                            <dt>Arrivato il</dt>
                            <dd>
                                <time dateTime={gatto.data_arrivo}>
                                    {new Date(gatto.data_arrivo).toLocaleDateString('it-IT')}
                                </time>
                            </dd>
                        </dl>
                    </div>
                </article>
            </li>
        );
    }

    /* Componente principale ----------------------------------------- */

    function GattiApp({ utenteLoggato, isAdmin }) {
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
            fetch('api/gatti.php', { credentials: 'same-origin' })
                .then(function (r) {
                    if (!r.ok) throw new Error('Risposta server: ' + r.status);
                    return r.json();
                })
                .then(function (dati) {
                    if (dati.errore) throw new Error(dati.errore);
                    setGatti(dati.gatti || []);
                })
                .catch(function (err) {
                    console.error('[GattiComponent] errore:', err.message);
                    setErrore('Impossibile caricare i gatti: ' + err.message);
                })
                .finally(function () { setCaricamento(false); });
        }, []);

        // A ogni cambio di selezione notifica il form Vanilla JS.
        useEffect(function () {
            emettiSelezione(gatti.filter(function (g) { return selezionati.has(g.id); }));
        }, [selezionati, gatti]);

        const cambiaSelezione = useCallback(function (gatto) {
            setSelezionati(function (precedenti) {
                const nuovi = new Set(precedenti);
                if (nuovi.has(gatto.id)) { nuovi.delete(gatto.id); } else { nuovi.add(gatto.id); }
                return nuovi;
            });
        }, []);

        // Filtro per testo + ordinamento.
        const gatti_visibili = gatti
            .filter(function (g) {
                if (!ricerca.trim()) return true;
                const termine = ricerca.trim().toLowerCase();
                return g.nome.toLowerCase().includes(termine) || g.descrizione.toLowerCase().includes(termine);
            })
            .sort(function (a, b) {
                switch (ordinamento) {
                    case 'eta_asc': return a.eta - b.eta;
                    case 'eta_desc': return b.eta - a.eta;
                    case 'colore_asc': return a.colore_mantello.localeCompare(b.colore_mantello);
                    case 'data_arrivo_asc': return new Date(a.data_arrivo) - new Date(b.data_arrivo);
                    default: return new Date(b.data_arrivo) - new Date(a.data_arrivo);
                }
            });

        if (caricamento) {
            return (
                <p className="caricamento" role="status" aria-live="polite">
                    Caricamento schede gatti in corso…
                </p>
            );
        }

        if (errore) {
            return (
                <output className="messaggio messaggio-errore" role="alert" aria-live="assertive">
                    <strong className="messaggio-tag" aria-hidden="true">Errore</strong> {errore}
                </output>
            );
        }

        return (
            <section className="react-gatti-wrap" aria-labelledby="titolo-lista-gatti">
                <h2 id="titolo-lista-gatti" className="sr-solo">Lista gatti disponibili</h2>

                {/* Barra ricerca e ordinamento (form di sola interazione) */}
                <form
                    className="barra-controlli"
                    role="search"
                    aria-label="Filtra e ordina i gatti"
                    onSubmit={function (e) { e.preventDefault(); }}
                >
                    <label htmlFor="ricerca-gatto">
                        Cerca per nome o descrizione
                        <input
                            type="search"
                            id="ricerca-gatto"
                            value={ricerca}
                            onChange={function (e) { setRicerca(e.target.value); }}
                            placeholder="Es. giocoso, bianco…"
                            aria-controls="lista-gatti"
                        />
                    </label>
                    <label htmlFor="ordina-gatti">
                        Ordina per
                        <select
                            id="ordina-gatti"
                            value={ordinamento}
                            onChange={function (e) { setOrdinamento(e.target.value); }}
                        >
                            <option value="data_arrivo_desc">Data arrivo (più recente)</option>
                            <option value="data_arrivo_asc">Data arrivo (meno recente)</option>
                            <option value="eta_asc">Età (più giovane)</option>
                            <option value="eta_desc">Età (più vecchio)</option>
                            <option value="colore_asc">Colore mantello (A-Z)</option>
                        </select>
                    </label>
                </form>

                {/* Stato per screen reader */}
                <p aria-live="polite" className="sr-solo" role="status">
                    {gatti_visibili.length === gatti.length
                        ? gatti.length + ' gatti disponibili.'
                        : gatti_visibili.length + ' gatti trovati su ' + gatti.length + '.'}
                    {selezionati.size > 0 ? ' ' + selezionati.size + ' selezionati.' : ''}
                </p>

                {/* Istruzione selezione */}
                {utenteLoggato && !isAdmin && (
                    <p className="aiuto-campo" aria-live="polite">
                        Clicca su una card per selezionare il gatto.
                        {selezionati.size > 0 && (
                            <strong> {selezionati.size} {selezionati.size === 1 ? 'gatto selezionato' : 'gatti selezionati'}.</strong>
                        )}
                    </p>
                )}

                {gatti_visibili.length === 0 ? (
                    <p role="status" aria-live="polite">
                        Nessun gatto corrisponde alla ricerca «{ricerca}».
                    </p>
                ) : (
                    <ul id="lista-gatti" className="griglia-gatti"
                        aria-label="Elenco gatti disponibili">
                        {gatti_visibili.map(function (gatto) {
                            return (
                                <CardGatto
                                    key={gatto.id}
                                    gatto={gatto}
                                    selezionabile={utenteLoggato && !isAdmin}
                                    selezionata={selezionati.has(gatto.id)}
                                    onToggle={cambiaSelezione}
                                />
                            );
                        })}
                    </ul>
                )}
            </section>
        );
    }

    /* Mount --------------------------------------------------------- */

    const radice = document.getElementById('react-gatti-root');
    if (!radice) {
        console.error('[GattiComponent] elemento root non trovato');
        return;
    }

    const utente_loggato = radice.getAttribute('data-utente-loggato') === 'true';
    const is_admin = radice.getAttribute('data-is-admin') === 'true';
    radice.removeAttribute('aria-busy');

    ReactDOM.createRoot(radice).render(
        React.createElement(GattiApp, { utenteLoggato: utente_loggato, isAdmin: is_admin })
    );
})();
