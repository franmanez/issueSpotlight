# IssueSpotlight IA - Guia d'Usuari i Funcionalitats

Aquest plugin permet als editors d'OJS 3.3 transformar les metadades d'un número de revista en una experiència interactiva d'anàlisi basada en Intel·ligència Artificial (Google Gemini).

## Funcionalitats Principals

1.  **Resum Editorial**: Un algorisme d'IA actua com a "Editor Cap" per redactar un esborrany d'editorial (en HTML) que sintetitza els temes clau, identifica fils conductors i destaca les contribucions més rellevants del número.
2.  **Radar d'Innovació**: Un gràfic interactiu de bombolles (Bubble Chart) que visualitza conceptes tecnològics i metodològics. La mida indica la freqüència i el color representa la tendència (En Alça, Novetat o Consolidat).
3.  **Impacte ODS (Agenda 2030)**: Avaluació automàtica de l'alineació dels articles amb els Objectius de Desenvolupament Sostenible de l'ONU, incloent justificacions qualitatives i visualització mitjançant gràfics i targetes oficials.
4.  **Mapa Global i Institucional**: 
    *   **Geolocalització**: Mapatge de totes les institucions participants.
    *   **Anàlisi Institucional**: Visualització de la distribució geogràfica d'autors i institucions representades. La mida dels punts indica la densitat d'autors per centre.
    *   **Directori d'Autors**: Llistat complet d'autors, afiliacions i articles vinculats per a una transparència total.

## Flux de Treball

El plugin utilitza el model **gemini-2.5-flash-lite** per processar la informació en quatre etapes:
1.  **Extracció**: Obté títols i resums de tots els articles publicats.
2.  **Anàlisi Geogràfica**: Normalitza afiliacions i geocodifica institucions per al mapa.
3.  **Anàlisi Multilingüe**: Executa prompts especialitzats per al Radar, l'Editorial, els ODS i el Mapa Geo, generant respostes en tots els idiomes configurats a la revista.
4.  **Persistència**: Guarda els resultats per cada idioma a la base de dades per a accés instantani.

## Lògica dels Prompts (IA)

Cada secció de l'anàlisi es basa en una instrucció específica a la IA:
*   **Radar d'Innovació**: Extreu conceptes tècnics evitant termes genèrics. Obliga a usar bigrames/trigrames per a major precisió i classifica la seva tendència.
*   **Editorial**: Actua com a Editor Cap per sintetitzar el número en format HTML estructurat.
*   **Impacte ODS**: Classifica els articles segons els Objectius de Desenvolupament Sostenible de l'ONU amb justificacions tècniques.
*   **Geo-Normalització**: Neteja i normalitza els noms de les institucions i les ubica al mapa.

## Configuració i Activació

1.  **Instal·lació**: Copia la carpeta del plugin a `plugins/generic/issueSpotlight`.
2.  **Base de Dades**: Assegura't que la taula `issue_ai_analysis` existeix (veure secció tècnica).

### Com obtenir la Clau API de Gemini (Gratuïta)

Perquè el plugin funcioni, necessites una clau d'accés a la intel·ligència artificial de Google:

1.  Accedeix a **[Google AI Studio](https://aistudio.google.com/)** i inicia sessió amb el teu compte de Google.
2.  Al menú lateral esquerre, fes clic a l'icona de la clau o al botó **"Get API key"** (Obtenir clau API).
3.  Prem el botó blau **"Create API key"** (Crear clau API) i selecciona un projecte (o crea'n un de nou).
4.  Copia la clau alfanumèrica generada.
5.  Al teu OJS, ves a **Configuració > Lloc web > Plugins**, cerca "IssueSpotlight IA" i prem a **Ajustos** per enganxar la clau.

4.  **Execució**: Al llistat de números (Futurs o Publicats), utilitza el botó blau **"IssueSpotlight IA"** per iniciar el procés. En iniciar el procés, la IA analitzarà els títols, resums i afiliacions per generar automàticament l'esborrany editorial, el radar d'innovació, l'impacte ODS i el mapa d'institucions.

## Notes d'Ús
*   **Privacitat**: Només s'envien a la IA els títols, resums i afiliacions (dades públiques).
*   **Quotes i Límits**: Cada anàlisi completa consumeix **4 trucades** a l'LLM de Gemini. Si utilitzes la versió gratuïta, tingues en compte que hi ha una limitació de **20 trucades diàries**.
*   **Regeneració**: Si els resultats no són satisfactoris o has actualitzat els articles, pots **regenerar l'anàlisi** en qualsevol moment. El nou procés substituirà les dades existents per les noves.

## Solució de Problemes

Si trobes errors durant l'anàlisi, aquí tens les causes més comunes i les seves solucions:

### Error: "The model is overloaded. Please try again later."
**Causa**: El model de Gemini està experimentant una alta demanda i està temporalment sobrecarregat.

**Solució**: Aquest és un error temporal del servei de Google. Simplement espera uns minuts i torna a executar l'anàlisi fent clic novament al botó **"IssueSpotlight IA"**. Pots intentar-ho diverses vegades fins que funcioni.

### Error: Límit de quota superat
**Causa**: Has assolit el límit diari de trucades a l'LLM de Gemini (20 trucades diàries al pla gratuït).

**Solució**: Atès que cada anàlisi consumeix 4 trucades, pots analitzar un màxim de 5 números per dia amb el pla gratuït. Espera fins l'endemà per continuar, o considera actualitzar a un pla de pagament de Google Gemini si necessites fer més anàlisis.
