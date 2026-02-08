# IssueSpotlight IA - Guia d'Usuari i Funcionalitats

Aquest plugin permet als editors d'OJS 3.3 transformar les metadades d'un número de revista en una experiència interactiva d'anàlisi basada en Intel·ligència Artificial (Google Gemini).

## Funcionalitats Principals

1.  **Resum Editorial**: Un algorisme d'IA actua com a "Editor Cap" per redactar un esborrany d'editorial (en HTML) que sintetitza els temes clau, identifica fils conductors i destaca les contribucions més rellevants del número.
2.  **Radar d'Innovació**: Un gràfic interactiu de bombolles (Bubble Chart) que visualitza conceptes tecnològics i metodològics. La mida indica la freqüència i el color representa la tendència (En Alça, Novetat o Consolidat).
3.  **Impacte ODS (Agenda 2030)**: Avaluació automàtica de l'alineació dels articles amb els Objectius de Desenvolupament Sostenible de l'ONU, incloent justificacions qualitatives i visualització mitjançant gràfics i targetes oficials.
4.  **Mapa Global i Xarxa de Col·laboració**: 
    *   **Geolocalització**: Mapatge de totes les institucions participants.
    *   **Anàlisi de Col·laboració**: Visualització de vincles nacionals i internacionals entre institucions mitjançant línies corbes animades.
    *   **Directori d'Autors**: Llistat complet d'autors, afiliacions i articles vinculats per a una transparència total.

## Flux de Treball

El plugin utilitza el model **Gemini 2.0 Flash Lite** per processar la informació en quatre etapes:
1.  **Extracció**: Obté títols i resums de tots els articles publicats.
2.  **Anàlisi Geogràfica**: Normalitza afiliacions i geocodifica institucions per al mapa.
3.  **Generació de Coneixement**: Executa prompts especialitzats per al Radar, l'Editorial i els ODS.
4.  **Persistència**: Guarda els resultats a la base de dades per a accés instantani per part dels lectors.

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

4.  **Execució**: Al llistat de números (Futurs o Publicats), utilitza el botó blau **"IssueSpotlight IA"** per iniciar el procés.

## Notes d'Ús
*   **Privadesa**: Només s'envien a la IA els títols, resums i afiliacions (dades públiques).
*   **Quotes**: Una anàlisi completa realitza diverses peticions a l'API. Assegura't de tenir quota disponible al teu pla de Gemini.
