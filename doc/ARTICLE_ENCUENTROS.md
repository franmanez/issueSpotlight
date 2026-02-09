# Implementación de Inteligencia Artificial Generativa en Ecosistemas Editoriales: El Plugin IssueSpotlight para Open Journal Systems (OJS)

**Autor:** [Nombre del Autor]  
**Puesto de trabajo:** [Puesto de trabajo, ej. Ingeniero de Software / Editor Técnico]  
**Filiación:** [Institución / Universidad]  

---

## Resumen

El presente artículo describe el desarrollo e implementación técnica de **IssueSpotlight IA**, un plugin innovador para la plataforma de gestión editorial Open Journal Systems (OJS) que integra modelos de lenguaje de gran tamaño (LLM), específicamente Google Gemini, para la extracción automatizada de conocimiento y el enriquecimiento de metadatos académicos. A diferencia de las extensiones tradicionales de OJS, IssueSpotlight ofrece cuatro dimensiones de análisis avanzado: una síntesis editorial automatizada, un radar de innovación basado en conceptos tecnológicos y metodológicos, una evaluación de impacto alineada con los Objetivos de Desarrollo Sostenible (ODS) de la ONU y un mapeo geográfico institucional normalizado. El artículo detalla la arquitectura de "modificación cero del núcleo", la lógica de *prompt engineering* multilingüe y los desafíos técnicos de la normalización bibliométrica mediante IA. Los resultados sugieren que la integración de IA en el flujo editorial no solo mejora la visibilidad de la producción científica, sino que transforma los metadatos estáticos en herramientas interactivas de descubrimiento para la comunidad académica.

**Palabras clave:** OJS, Inteligencia Artificial, Google Gemini, Gestión Editorial, Bibliometría, ODS, Innovación Educativa.

---

## 1. Introducción

La gestión de revistas científicas ha experimentado una transformación digital radical en las últimas dos décadas, con **Open Journal Systems (OJS)** consolidándose como el estándar *de facto* para la publicación en acceso abierto. Sin embargo, a pesar de su robustez, los ecosistemas editoriales tradicionales suelen presentar la información de los números de forma estática, limitando la capacidad de los lectores para identificar tendencias emergentes o colaboraciones institucionales de un vistazo.

Con la eclosión de la Inteligencia Artificial Generativa (GenAI), se abre una oportunidad sin precedentes para redefinir el valor de los metadatos académicos. No obstante, la integración de la IA en OJS ha sido, hasta la fecha, escasa y limitada a tareas administrativas internas. El plugin **IssueSpotlight IA** nace con el objetivo de cerrar esta brecha, proporcionando una capa de análisis inteligente que "cobra vida" cada vez que se publica un nuevo número, transformando títulos y resúmenes en conocimiento estructurado y visual.

## 2. Fundamentación Teórica y Estado del Arte

### 2.1. La Inteligencia Artificial en el Flujo Editorial: De la Automatización a la Generación
Históricamente, la aplicación de la informática en la gestión editorial se ha limitado a sistemas de gestión de flujo de trabajo (*workflow management systems*), como OJS, que automatizan la recepción, revisión por pares y publicación. Sin embargo, la llegada de los Modelos de Lenguaje de Gran Tamaño (LLMs) marca un cambio de paradigma: pasamos de sistemas que "mueven documentos" a sistemas que "entienden y sintetizan contenido".

El estado del arte actual en la integración de IA en revistas académicas se centra principalmente en:
1. **Detección de Plagio y Fraude:** Herramientas como Similarity Check (Crossref/iThenticate) que utilizan algoritmos de coincidencia de patrones.
2. **Selección de Revisores:** Algoritmos que cruzan palabras clave del manuscrito con bases de datos de expertos (ej: Jane - Journal/Author Name Estimator).
3. **Resúmenes Automáticos:** Aunque existen herramientas externas, su integración nativa en plataformas editoriales sigue siendo incipiente.

IssueSpotlight trasciende estas aplicaciones centrándose en el **descubrimiento de conocimiento post-publicación**. Mientras que la mayoría de los esfuerzos de IA se concentran en la fase de revisión, nuestro enfoque pone el foco en el lector y en la visibilidad del conocimiento ya certificado.

### 2.2. Bibliometría de Tercera Generación y Cienciometría Visual
La bibliometría tradicional se basa en el recuento de citas e índices de impacto (como el factor de impacto de JCR o el índice h). La "segunda generación" introdujo el análisis de redes de citas. No obstante, estamos entrando en lo que algunos autores denominan **Bibliometría Semántica o de Tercera Generación**, donde la unidad de medida no es la cita, sino el concepto y su contexto.

El desafío de la Ciencia Abierta (Open Science) no es solo el acceso gratuito a los PDFs, sino la **"inteligibilidad abierta"**. Con miles de artículos publicados diariamente, el acceso no garantiza el descubrimiento. Las herramientas de visualización cienciométrica, como los mapas de densidad y los radares de tendencias, actúan como filtros cognitivos necesarios para que la comunidad científica pueda navegar la infoxicación académica.

## 3. Descripción Funcional del Plugin

IssueSpotlight IA se presenta como un panel de control interactivo integrado tanto en el *backend* administrativo como en la vista pública del número. Su funcionalidad se desglosa en cuatro pilares críticos:

### 2.1. Síntesis Editorial Inteligente
Mediante un agente de IA que emula el rol de un Editor Jefe, el sistema analiza la totalidad de las contribuciones de un número para redactar automáticamente un borrador editorial. Esta síntesis no es un simple resumen acumulativo, sino un análisis temático que identifica hilos conductores y agrupa los artículos por su relevancia disciplinar.

### 2.2. Radar de Innovación (Innovation Radar)
Utilizando una visualización de tipo *Packed Bubble Chart*, el radar extrae conceptos tecnológicos, metodológicos y teóricos. La IA clasifica cada concepto según su tendencia detectada:
- **Consolidados (Stable):** Tecnologías o métodos base de la disciplina.
- **En Alza (Rising):** Tendencias con crecimiento significativo.
- **Novedades (New):** Conceptos disruptivos o emergentes.

### 2.3. Impacto ODS (Objetivos de Desarrollo Sostenible)
El plugin evalúa la contribución de la investigación a la Agenda 2030 de la ONU. La IA asigna porcentajes de relevancia a los ODS impactados y proporciona una justificación cualitativa para cada uno, permitiendo a las instituciones medir el impacto social de su producción.

### 2.4. Mapa Global Institucional
Un sistema de geolocalización avanzada que mapea la procedencia de los autores. A diferencia de un mapa estándar, este componente utiliza la IA para normalizar nombres de instituciones (ej. convirtiendo "UPC" en "Universitat Politècnica de Catalunya") y calcular la densidad investigadora en el mapa.

## 4. Implementación Técnica y Arquitectura

### 3.1. Filosofía de "Modificación Cero"
Uno de los mayores desafíos técnicos en el desarrollo de plugins para OJS es la compatibilidad con futuras actualizaciones. IssueSpotlight se ha implementado siguiendo estrictamente el sistema de *Hooks* de PKP, lo que significa que no se ha modificado ni una sola línea del núcleo (*core*) de OJS. La inyección de la interfaz se realiza mediante el gancho `TemplateManager::display`, y la persistencia de datos utiliza el motor ADODB integrado.

### 3.2. Integración con la API de Google Gemini
Se ha seleccionado el modelo **gemini-2.5-flash-lite** por su equilibrio entre latencia, precisión en la generación de JSON y generosa ventana de contexto. La comunicación se realiza mediante llamadas cURL cifradas, procesando payloads de hasta 30.000 caracteres de metadatos por bloque de análisis. Técnicamente, cada análisis completo de un número se desglosa en **cuatro llamadas al LLM de Gemini** (Síntesis, Radar, ODS y Geonormalización), lo que permite optimizar los prompts para cada tarea específica y garantizar la integridad de las respuestas JSON.

### 3.3. Flujo de Datos y Persistencia
El proceso de generación de análisis sigue un flujo asíncrono diseñado para evitar el bloqueo del servidor PHP:
1. **Extracción:** Recopilación de títulos, abstracts y afiliaciones locales.
2. **Normalización:** Envío de datos a la IA para limpieza y estructuración.
3. **Persistencia Multilingüe:** Los resultados se almacenan en una tabla dedicada (`issue_ai_analysis`), indexada por `issue_id` y `locale`, permitiendo que un mismo número ofrezca análisis nativos en inglés, español o catalán de forma instantánea al lector.

| Campo | Tipo de Dato | Función |
| :--- | :--- | :--- |
| `issue_id` | BIGINT | Identificador del número en OJS |
| `locale` | VARCHAR(14) | Código de idioma (es_ES, en_US, etc.) |
| `editorial_draft`| LONGTEXT | Contenido HTML generado |
| `radar_analysis` | LONGTEXT | JSON con etiquetas y tendencias |
| `ods_analysis` | LONGTEXT | JSON con alineación ODS |
| `geo_analysis` | LONGTEXT | JSON de instituciones y coordenadas |

*Tabla 1: Estructura simplificada de la base de datos de IssueSpotlight.*

## 5. Ingeniería de Prompts (Prompt Engineering)

La calidad del análisis depende críticamente de cómo se formulan las instrucciones a la IA. Se han desarrollado cuatro sistemas de prompts especializados.

### 4.1. El Prompt del Radar: Reglas de Especificidad
Para evitar que la IA devuelva términos genéricos e inútiles como "Investigación" o "Datos", se han implementado **Reglas de Especificidad** estrictas:
- **Bigramas y Trigramas:** Se fuerza a la IA a buscar conceptos compuestos (ej: "Aprendizaje Federado" en lugar de "Aprendizaje").
- **Términos Prohibidos:** Se incluye una lista negra de 15 términos vacíos de contenido técnico.
- **Clasificación de Tendencia:** Lógica booleana para diferenciar entre una "Novedad" (mencionada como futura línea) y un "Consolidado" (estándar de la industria).

```php
// Ejemplo simplificado de la lógica de especificidad en el código
$promptRadar = "Extrae conceptos... REGLAS: 1. BIGRAMAS/TRIGRAMAS obligatorios. 2. PROHIBIDOS: Tecnología, Análisis, Sistema... 3. NORMALIZAR bajo término técnico.";
```

### 4.2. Prompt de Geonormalización
La IA actúa como un experto en geología institucional, resolviendo el problema de las afiliaciones mal escritas o abreviadas mediante un proceso de *match* con bases de datos internas del conocimiento de Gemini.

## 6. Visualización y Experiencia del Usuario (UI/UX)

La interfaz se ha construido utilizando bibliotecas modernas de JavaScript para asegurar que el "Spotlight" sea visualmente impactante:
- **Highcharts (Packed Bubble):** Para el radar de innovación, permitiendo una exploración fluida de los conceptos.
- **Leaflet.js:** Para el mapa interactivo, con técnicas de *spiral jittering* para evitar el solapamiento de instituciones en la misma ciudad.
- **CSS Avanzado:** Uso de gradientes, efectos de cristalografía (*glassmorphism*) y tipografía moderna para diferenciar la sección de IA del resto del tema clásico de OJS.

> **[PANTALLAZO 1: Vista general del Radar de Innovación con burbujas de colores]**
> *Preparar captura del panel de análisis en la pestaña "Radar de Innovación".*

> **[PANTALLAZO 2: Mapa Global de Instituciones con popups interactivos]**
> *Preparar captura del mapa Leaflet mostrando los círculos proporcionales en diferentes países.*

## 7. Estudio de Caso y Análisis de Resultados

### 7.1. Simulación en un número de temática tecnológica
Para validar la eficacia del plugin, se realizó una simulación utilizando los metadatos de un número ficticio centrado en "IA y Sociedad". El corpus bibliográfico consistió en 15 artículos de investigación con sus respectivos títulos, resúmenes y afiliaciones institucionales de 8 países diferentes.

#### Resultados del Radar de Innovación
Tras la ejecución del análisis, el radar identificó 24 conceptos clave. Los resultados mostraron una alta precisión en la aplicación de las reglas de especificidad:
- **Conceptos Rising:** "Algoritmos Éticos", "Privacidad Diferencial", "Sesgo Automatizado".
- **Conceptos New:** "IA Generativa Multimodal", "Gobernanza de Agentes".
- **Conceptos Stable:** "Redes Neuronales", "Procesamiento de Lenguaje".

La IA agrupó correctamente términos técnicos compuestos, evitando palabras vacías y proporcionando un mapa terminológico coherente con el estado actual de la disciplina.

#### Resultados de la Editorial
El borrador generado por la IA (≈ 450 palabras) estructuró el número en tres bloques temáticos claros: 1) Desafíos Éticos, 2) Innovación Técnica y 3) Marco Regulatorio. Se observó que el tiempo necesario para obtener una estructura narrativa coherente se redujo de horas de lectura manual a menos de 45 segundos de procesamiento.

### 7.2. Evaluación del Mapa Institucional
El proceso de geonormalización resolvió 12 inconsistencias en los nombres de las instituciones (ej. normalizando "U. de Barcelona" y "Universidad de Barcelona" como una única entidad). El mapa Leaflet visualizó la densidad de autores, destacando un clúster de investigación en el sur de Europa y colaboraciones emergentes con instituciones de Latinoamérica.

| Métrica | Humano (Estimado) | IssueSpotlight IA |
| :--- | :--- | :--- |
| Tiempo de extracción de conceptos | 120 min | 0.5 min |
| Normalización de instituciones | 45 min | 1 min |
| Redacción de borrador editorial | 180 min | 0.7 min |
| **Total tiempo invertido** | **345 min** | **2.2 min** |

*Tabla 2: Comparativa de eficiencia en tareas de síntesis editorial.*

## 8. Discusión y Mejoras respecto al Estado del Arte

Actualmente, no existen en el repositorio oficial de plugins de PKP soluciones que integren IA generativa para el análisis bibliométrico a nivel de número. IssueSpotlight IA ofrece tres ventajas competitivas claras:
1. **Reducción del Tiempo de Síntesis:** Un editor puede tardar horas en redactar una editorial; la IA ofrece una base sólida en segundos.
2. **Visibilidad de Impacto Social:** La traducción automática a ODS permite a la revista alinearse con las políticas de ciencia abierta y sostenible de la Unión Europea. Esta funcionalidad es especialmente relevante en el marco del programa Horizonte Europa, que exige la monitorización del impacto social de la investigación financiada con fondos públicos.
3. **Normalización Geográfica:** Elimina el ruido en los datos de afiliación, permitiendo mapas precisos sin necesidad de bases de datos externas de pago (como Scopus o Web of Science).

Sin embargo, es necesario reconocer las limitaciones actuales. La dependencia de modelos externos como Gemini implica una conexión a Internet constante y la aceptación de las políticas de uso de Google. Aunque el plugin utiliza un enfoque de "zero-core-modification", la evolución de las APIs de IA es mucho más rápida que los ciclos de actualización de PKP, lo que requiere un mantenimiento proactivo del código de integración.

### 8.1. Desafíos Operacionales y Gestión de Errores en Producción

La integración de servicios de IA en tiempo real introduce nuevos vectores de fallo que no existen en los sistemas editoriales tradicionales. Durante el despliegue en entornos de producción, se han identificado dos categorías principales de errores que requieren estrategias de mitigación específicas:

#### Error de Sobrecarga del Modelo (Model Overload)
El error `"The model is overloaded. Please try again later"` se produce cuando los servidores de Google Gemini experimentan picos de demanda que exceden su capacidad de procesamiento instantáneo. Este fenómeno es especialmente frecuente durante las horas pico de uso global (14:00-18:00 UTC) y refleja una limitación inherente a los servicios de IA basados en la nube compartida.

**Estrategia de Mitigación Implementada:**  
El plugin no implementa un sistema de reintentos automáticos (*retry logic*) para evitar agravar la congestión del servicio. En su lugar, se ha optado por una estrategia de **transparencia hacia el usuario**: cuando se detecta este error, el sistema muestra un mensaje claro en la interfaz indicando que se trata de un problema temporal del proveedor y sugiere volver a ejecutar el análisis manualmente tras unos minutos. Esta decisión de diseño prioriza la honestidad operacional sobre la automatización opaca, permitiendo que los editores comprendan las limitaciones reales de la infraestructura de IA.

#### Error de Límite de Cuota (Quota Limit Exceeded)
El plan gratuito de Google Gemini impone un límite de **20 llamadas diarias** por clave API. Dado que cada análisis completo de un número consume **4 llamadas al LLM** (una por cada dimensión: Editorial, Radar, ODS y Geo), esto se traduce en una capacidad máxima de **5 análisis completos por día** en el tier gratuito.

**Implicaciones para la Gestión Editorial:**  
Este límite tiene consecuencias prácticas para revistas con alta frecuencia de publicación. Una revista que publique semanalmente podría agotar su cuota en un solo día si regenera análisis múltiples veces. Para mitigar este problema, se han implementado dos medidas:

1. **Documentación Proactiva:** El sistema de configuración del plugin incluye advertencias claras sobre el consumo de cuota, calculando automáticamente el número máximo de análisis diarios disponibles.
2. **Persistencia de Resultados:** Los análisis se almacenan permanentemente en la base de datos, permitiendo su visualización ilimitada sin consumir llamadas adicionales. Solo la regeneración explícita consume cuota.

**Consideraciones de Escalabilidad:**  
Para revistas institucionales con volúmenes de publicación elevados, se recomienda la migración a planes de pago de Google Gemini o, alternativamente, la exploración de modelos de IA auto-hospedados (*self-hosted*) como Llama 3 o Mistral mediante infraestructuras como Ollama. La arquitectura modular del plugin facilita esta transición sin necesidad de reescribir la lógica de negocio.


## 9. Discusión Ética, Privacidad y Soberanía del Dato

La integración de LLMs en el ecosistema científico no está exenta de controversia ética. Durante el desarrollo de IssueSpotlight, se han abordado tres dimensiones críticas:

### 9.1. La Alucinación en el Contexto Académico
Uno de los riesgos inherentes a los modelos generativos es la invención de datos ("alucinaciones"). Para mitigar este riesgo en un entorno donde la veracidad es innegociable, el plugin utiliza un sistema de **anclaje al texto fuente** (*Grounding*). Los prompts están diseñados para prohibir explícitamente a la IA inferir información que no esté presente en los metadatos proporcionados. En el caso del radar, si un concepto no aparece en los títulos o abstracts, la IA no puede inventarlo.

### 9.2. Privacidad y Datos de Afiliación
El envío de datos a servidores externos genera dudas razonables sobre la privacidad. Es importante destacar que IssueSpotlight solo envía a la API de Gemini información que ya es **pública y accesible** en OJS: títulos, resúmenes y nombres de instituciones. En ningún caso se envían correos electrónicos de autores, datos de revisiones por pares (ciegas) o metadatos de artículos no publicados.

### 9.3. Soberanía del Dato vs. Modelos Comerciales
El uso de Google Gemini plantea el debate sobre la soberanía tecnológica de las revistas académicas. Aunque hoy en día los modelos comerciales ofrecen una facilidad de integración superior, la arquitectura del plugin se ha diseñado de forma modular. Esto permitiría, en futuras iteraciones, sustituir la llamada a la API de Google por una conexión a modelos *open source* alojados en servidores locales (como Llama 3 o Mistral) mediante infraestructuras como Ollama o vLLM, garantizando una autonomía total para las instituciones que así lo requieran.

## 10. Impacto en el SEO Académico y la Interoperabilidad de Metadatos

El valor de una revista científica no solo reside en la calidad de sus artículos, sino en su capacidad para ser descubierta por los motores de búsqueda generalistas (Google) y académicos (Google Scholar, Semantic Scholar). IssueSpotlight IA potencia el SEO (*Search Engine Optimization*) académico mediante tres mecanismos:

1. **Enriquecimiento de Palabras Clave:** Al extraer bigramas y trigramas específicos para el radar, el plugin genera un corpus de términos técnicos que los editores pueden utilizar para refinar las etiquetas de metadatos de OJS, mejorando el indexado semántico.
2. **Generación de Contenido Dinámico:** Google valora positivamente las páginas que ofrecen contenido estructurado y frecuente. La editorial generada por IA proporciona un texto rico en palabras clave relevantes para el número, actuando como una "landing page" optimizada para rastreadores.
3. **Interoperabilidad OAI-PMH:** Aunque actualmente el plugin almacena los análisis de forma interna, su diseño contempla la futura exportación de estas etiquetas de IA a través del protocolo OAI-PMH, permitiendo que repositorios externos consuman los metadatos enriquecidos y mejoren la precisión de sus búsquedas.

## 11. Conclusiones

La implementación de IssueSpotlight IA en OJS marca un hito en la evolución de las plataformas de publicación científica. Hemos demostrado que es posible integrar capacidades de razonamiento de nivel humano (Gemini) dentro de una arquitectura PHP tradicional sin comprometer la seguridad ni la estabilidad del sistema. Este plugin no solo automatiza tareas, sino que democratiza el acceso al análisis bibliométrico avanzado, poniéndolo al alcance de cualquier revista, independientemente de su presupuesto o tamaño.

El futuro de la edición académica pasa por convertir los datos en historias visuales, y la inteligencia artificial es el motor ideal para este cambio de paradigma. La transición hacia una "editorial inteligente" permitirá que las revistas científicas dejen de ser meros repositorios de PDFs para convertirse en centros interactivos de transferencia de conocimiento.

---

## Referencias

- PKP. (2023). *Open Journal Systems 3.3 Reference Guide*. Public Knowledge Project. https://docs.pkp.sfu.ca/
- Google. (2024). *Gemini API Documentation: Google AI SDK for PHP*. Google AI Studio. https://ai.google.dev/docs
- United Nations. (2015). *Transforming our world: the 2030 Agenda for Sustainable Development*. UN Department of Economic and Social Affairs. https://sdgs.un.org/2030agenda
- Highcharts API Reference. (2024). *Packed Bubble Series Options and Physics*. Highcharts.com. https://api.highcharts.com/highcharts/series.packedbubble
- PKP. (2022). *Plugin Development Guide for OJS and OMP*. PKP Docs. https://docs.pkp.sfu.ca/dev/plugin-guide/es/
- Willinsky, J. (2006). *The Access Principle: The Case for Open Access to Research and Scholarship*. MIT Press.
- PKP. (2021). *OAI-PMH and Metadata Interoperability in PKP Systems*. PKP Support.
- Highsoft. (2024). *Visualizing Complex Data Sets with Highcharts*. Whitepaper.
