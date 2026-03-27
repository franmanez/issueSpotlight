# IssueSpotlight IA para OJS 3.3

![OJS Compatibility](https://img.shields.io/badge/OJS-3.3-blue.svg) ![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg) ![License](https://img.shields.io/badge/License-GPLv3-green.svg) ![Status](https://img.shields.io/badge/Status-Beta-orange.svg)

> **[🇺🇸 Read documentation in English](README.md)**

**IssueSpotlight IA** es un plugin nativo para Open Journal Systems (OJS) que integra la inteligencia artificial generativa (**Google Gemini 2.0/2.5 Flash**) para transformar los metadatos estáticos de un número en un panel de descubrimiento interactivo y visual.

---

## 🚀 Funcionalidades Principales

El plugin analiza automáticamente los títulos, resúmenes y afiliaciones de los artículos para generar cuatro capas de valor añadido:

### 1. 📝 Síntesis Editorial Inteligente
Actúa como un agente editorial que lee todos los *abstracts* del número y redacta una **narrativa temática**.
*   Identifica hilos conductores entre artículos.
*   Agrupa contribuciones por disciplinas.
*   Genera un texto HTML semántico listo para publicar.

### 2. 📡 Radar de Innovación (*Innovation Radar*)
Visualización interactiva (*Packed Bubble Chart*) que detecta conceptos tecnológicos y metodológicos.
*   **Clasificación de Tendencias**: Detecta si un concepto es **Novedad** (*New*), **En Alza** (*Rising*) o **Consolidado** (*Stable*).
*   **Limpieza Semántica**: Utiliza reglas de especificidad para ignorar términos genéricos ("Investigación", "Análisis") y priorizar *bigramas* técnicos ("Aprendizaje Profundo", "Sostenibilidad Urbana").

### 3. 🌍 Mapa Global Institucional
Geolocalización avanzada de la autoría de la revista.
*   **Normalización**: La IA unifica nombres de instituciones (ej: "UPC", "Univ. Politécnica", "Universitat Politècnica de Catalunya" -> Un solo nodo).
*   **Visualización**: Mapa interactivo (Leaflet.js) con dispersión en espiral (*jittering*) para evitar solapamientos en ciudades con alta densidad de autores.

### 4. 🎯 Impacto ODS (Agenda 2030)
Evalúa la alineación del número con los **Objetivos de Desarrollo Sostenible** de la ONU.
*   Asigna porcentajes de relevancia a los ODS detectados.
*   Genera una justificación cualitativa (*reasoning*) de por qué la investigación contribuye a metas globales.

---

## 🛠️ Instalación y Configuración

### Requisitos Previos
*   OJS 3.3.x (No compatible con OJS 3.4).
*   PHP 7.4+ con extensión `cURL` habilitada.
*   Una **API Key de Google Gemini** (Gratuita).

### Paso 1: Instalación
1.  Descarga el archivo `.tar.gz` de la última *release*.
2.  En OJS, ve a **Ajustes > Sitio Web > Plugins > Cargar Plugin**.
3.  Sube el archivo. El plugin aparecerá en la lista de "Plugins Genéricos".
4.  Activa la casilla azul para habilitarlo.

### Paso 2: Obtener API Key
1.  Ve a [Google AI Studio](https://aistudio.google.com/).
2.  Inicia sesión y pulsa en "Get API key".
3.  Crea una clave gratuita (Free Tier).

### Paso 3: Configuración
1.  En la lista de plugins de OJS, busca **IssueSpotlight IA**.
2.  Pulsa en la flecha azul > **Ajustes**.
3.  Pega tu API Key y guarda.

---

## 📖 Guía de Uso (Para Editores)

El análisis NO se ejecuta automáticamente para evitar costes o publicaciones no deseadas.

1.  Ve a **Números > Futuros** (o Anteriores).
2.  Localiza el número que quieres analizar.
3.  Pulsa el botón **"IssueSpotlight IA"** que aparece en la rejilla.
4.  En la ventana emergente, pulsa **"INICIAR ANÁLISIS CON IA"**.
5.  Espera unos segundos (10-20s). Una vez finalizado, el análisis se guarda en la base de datos y ya es visible públicamente en la página del número.

---

## 🤓 Detalles Técnicos (Architecture & Prompts)

Esta sección está destinada a desarrolladores y administradores de sistemas.

### Arquitectura de "Modificación Cero"
El plugin respeta estrictamente la arquitectura de Hooks de PKP. No modifica el núcleo de OJS.
*   **Frontend**: Inyección mediante `TemplateManager::display`.
*   **Backend**: Controladores propios (`LoadHandler`).
*   **Persistencia**: Tabla personalizada `issue_ai_analysis`.

### Estrategia de Persistencia (Batch Processing)
Para mitigar la latencia y los límites de cuota de la API:
1.  **Análisis bajo demanda**: Solo el editor dispara el proceso.
2.  **Almacenamiento SQL**: El JSON resultante se guarda en la tabla `issue_ai_analysis`.
3.  **Lectura Rápida**: Los lectores de la revista ven los datos cacheados en BBDD. **El tráfico de lectura NO consume cuota de la API.**

### Ingeniería de Prompts
El sistema utiliza 4 prompts especializados diseñados para evitar alucinaciones:

| Componente | Estrategia de Prompting |
| :--- | :--- |
| **Radar** | **Reglas de Exclusión**: Lista negra de palabras vacías ("Estudio", "Datos"). **Forzado de Bigramas**: Exige conceptos compuestos de 2+ palabras. |
| **Editorial** | **Role-Playing**: "Actúa como Editor Jefe". **Restricción de Formato**: Salida estricta en HTML sin cabeceras Markdown. |
| **ODS** | **Anclaje de Datos**: Se inyectan los códigos HEX oficiales de la ONU en el prompt para asegurar consistencia visual. |
| **Geo** | **Normalización**: La IA actúa como curadora de datos, infiriendo coordenadas (Lat/Lng) a partir de nombres de instituciones incompletos. |

### Limitaciones Conocidas
*   **Idioma**: El análisis se realiza y almacena en el **idioma principal** (*Primary Locale*) de la revista para maximizar la ventana de contexto del LLM y evitar cortes en la respuesta JSON.
*   **Cuota de API (Free Tier)**: Google Gemini impone un límite de ~20 peticiones/día (aprox. 5 números completos al día). El plugin gestiona el error `Model Overload` de forma transparente.

---

## 🔒 Privacidad y Datos
*   El plugin envía a Google: Títulos, Resúmenes y Nombres de Instituciones (datos públicos).
*   **NO se envían**: Correos electrónicos, artículos no publicados ni datos de revisión por pares.

---

**Desarrollado por:** Fran Máñez - Universitat Politècnica de Catalunya (UPC)
