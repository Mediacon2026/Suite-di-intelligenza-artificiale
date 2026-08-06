# Mediacon One — Live Site Migration Audit

Data audit: 6 agosto 2026

Branch: `feature/mediacon-one-theme`

Destinazione: `https://mediacon.org`

## Esito

Il tema **non deve essere dichiarato pronto né attivato** finché il Preflight in
**Aspetto > Mediacon One** non è stato eseguito sul sito live e tutte le
criticità bloccanti non sono state valutate. Il Preflight è esclusivamente in
lettura: non crea pagine, non riscrive contenuti, non cambia URL, permalink,
menu, opzioni o database.

## Perimetro verificato

La mappa richiesta è definita in `themes/mediacon-one/inc/site-config.php` ed è
configurabile in codice tramite il filtro `mediacon_one_page_map`:

| Risorsa | Tipo | Sorgente prevista |
| --- | --- | --- |
| home | Pagina iniziale statica | WordPress `the_content()` |
| la-mediazione-2 | Pagina | WordPress `the_content()` |
| cose-e-come-funziona-la-mediazione-2 | Pagina | WordPress `the_content()` |
| istanza-di-mediazione | Pagina | WordPress `the_content()` |
| modello-di-adesione-alla-mediazione | Pagina | WordPress `the_content()` |
| costi-della-mediazione | Pagina | Preventivo Enterprise, solo se modulo e frontend sono attivi; altrimenti WordPress |
| mediazione-online | Pagina | WordPress `the_content()` |
| formazione | Pagina | WordPress `the_content()` |
| contatti | Pagina | WordPress `the_content()` |
| il-team-dei-nostri-mediatori | Pagina | WordPress `the_content()` |
| le-nostre-sedi | Pagina | WordPress `the_content()` |
| category/blog | Categoria | Archivio WordPress |
| category/sentenze-e-giurisprudenza-sulla-mediazione | Categoria | Archivio WordPress |
| category/prossimi-corsi | Categoria | Archivio WordPress |

## Criticità osservate sul sito pubblico

L’audit pubblico ha rilevato condizioni che il Preflight deve confermare sul
database reale prima dell’attivazione:

1. **Costi — critica.** La pagina pubblica presenta contemporaneamente più H1,
   un calcolatore legacy, il concetto ambiguo di “totale pratica” e una
   ripartizione interna tra sede operativa e sede principale. Il tema sopprime
   gli shortcode legacy noti soltanto nel fallback e non salva la rimozione. Il
   Preventivo Enterprise ora espone esclusivamente preventivi autonomi per
   singola parte e non mostra totali generali o quote interne.
2. **Titoli — alta.** Costi e Team Mediatori mostrano H1 duplicati nella pagina
   pubblica indicizzata. Mediacon One mantiene un solo H1 di documento e presenta
   gli H1 presenti nel contenuto come H2, senza aggiornare il post. Il Preflight
   segnala comunque gli H1 salvati affinché siano corretti editorialmente.
3. **Sedi — alta.** `contatti` descrive Pachino come “informazioni in
   aggiornamento”, mentre `le-nostre-sedi` pubblica l’indirizzo di Via Fratelli
   Bandiera 82. Napoli è indicata come futura/in corso di attivazione. La sorgente
   configurabile unica conserva Casarano e Pachino come operative e Napoli come
   `activation_pending`; il Preflight segnala assenze e indirizzi incoerenti tra
   le pagine senza modificarle.
4. **Adesione — critica se il link è incompleto.** La pagina deve continuare a
   usare il contenuto WordPress originale. Tutti gli anchor a Word/PDF/ODT sono
   preservati e presentati come link documento; il Preflight segnala `href=""`
   e `href="#"`.
5. **Archivi editoriali — media.** Blog, Sentenze e Prossimi Corsi richiedono una
   griglia uniforme. Le card del tema hanno immagine 16:10, altezza uniforme,
   titolo ed estratto limitati a due righe, CTA coerente e stato corso
   futuro/concluso. La data corso usa, in ordine, i meta
   `_mediacon_course_end_date`, `course_end_date`, `data_fine_corso`, poi l’anno
   nel titolo come fallback.

Fonti pubbliche consultate:

- <https://mediacon.org/costi-della-mediazione/>
- <https://mediacon.org/modello-di-adesione-alla-mediazione/>
- <https://mediacon.org/contatti/>
- <https://mediacon.org/le-nostre-sedi/>
- <https://mediacon.org/il-team-dei-nostri-mediatori/>
- <https://mediacon.org/category/prossimi-corsi/>

## Sorgente sedi

La sorgente unica è `mediacon_one_offices()` ed è configurabile tramite
`mediacon_one_offices`. I dati non vengono copiati nel database. Il footer e il
Preflight leggono la stessa sorgente. Prima dell’attivazione, un responsabile
deve confermare indirizzi e stato operativo, in particolare Pachino e Napoli.

## Contratto di rendering

- Il fallback è sempre il contenuto WordPress filtrato normalmente da
  `the_content()`.
- Adesione non usa un template statico e non sostituisce il contenuto.
- Costi passa al template Preventivo soltanto se plugin, modulo, frontend e ID
  pagina sono coerenti; in ogni altro caso usa WordPress.
- Gli shortcode dei calcolatori legacy configurati dal filtro
  `mediacon_one_legacy_calculator_shortcodes` non vengono eseguiti nel fallback
  Costi.
- Il tema non registra procedure automatiche di migrazione o correzione.

## Controlli Preflight

Il report amministrativo elenca criticità, pagina, gravità, sorgente di
rendering, plugin coinvolto e soluzione consigliata. Verifica:

- tutte le pagine e categorie richieste, stato di pubblicazione e contenuti vuoti;
- assegnazione dei menu primary, footer e mega;
- presenza di Enterprise e attivazione effettiva di Preventivo e Ricerca;
- corrispondenza tra pagina Costi e pagina configurata nel Preventivo;
- H1 presenti nel contenuto e quindi potenzialmente duplicati;
- link documento vuoti o puntati a `#`;
- tracce del calcolatore legacy, “totale pratica” e ripartizioni interne;
- presenza e coerenza delle sedi tra Contatti e Le nostre sedi.

## Procedura manuale di attivazione

1. Installare la ZIP del tema senza attivarla.
2. Eseguire **Aspetto > Mediacon One** sul sito live.
3. Esportare o copiare il report e assegnare ogni criticità a un responsabile.
4. Correggere manualmente contenuti, menu e configurazioni plugin approvate.
5. Rieseguire il Preflight e completare i test visuali desktop, tablet e mobile.
6. Eseguire un backup e definire il rollback.
7. Attivare il tema soltanto con approvazione esplicita; il codice non lo attiva.

## Criterio di uscita

Il pacchetto è tecnicamente distribuibile quando lint e test passano. La
**prontezza del sito live** richiede inoltre un Preflight eseguito sul database
reale, con tutte le criticità individuate, descritte e accettate o risolte
manualmente. Questo documento non costituisce autorizzazione all’attivazione.
