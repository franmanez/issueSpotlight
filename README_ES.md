# IssueSpotlight IA - Resumen de Implementación

Este plugin permite a los editores de OJS 3.3 obtener una visión global de un número completo de la revista utilizando Inteligencia Artificial (Google Gemini).

## Funcionalidades Principales

1.  **Matriz de Innovación**: Genera un gráfico de dispersión (Scatter Plot) posicionando los temas del número según su Madurez Académica e Impacto Potencial, ayudando a identificar apuestas de futuro y clásicos.
2.  **Sintetizador Editorial**: Redacta un borrador de editorial que identifica hilos conductores y tendencias comunes entre los trabajos del número.
3.  **Impacto ODS**: Calcula y visualiza (Gráfico Donut + Iconos Oficiales) el porcentaje de contribución del número a los Objetivos de Desarrollo Sostenible de la ONU.

## Estructura de Archivos Creados

*   `IssueSpotlightPlugin.inc.php`: Clase principal que registra los hooks de OJS e inyecta la funcionalidad.
*   `classes/IssueSpotlightService.inc.php`: Gestiona la extracción de metadatos de los artículos y la comunicación con la API de Gemini (modelo `gemini-2.0-flash-lite`).
*   `classes/IssueSpotlightGridFeature.inc.php`: Inyecta dinámicamente el botón "IssueSpotlight IA" en las filas del grid de Números (Futuros y Publicados).
*   `classes/IssueSpotlightSettingsForm.inc.php`: Formulario de configuración para guardar la API Key por revista.
*   `templates/analysis.tpl`: Interfaz de usuario para mostrar los resultados del análisis en pestañas.
*   `templates/settingsForm.tpl`: Interfaz para la configuración del plugin.
*   `schema.xml`: Crea la tabla `issue_ai_analysis` para persistir los resultados.
*   `locale/*/locale.xml`: Traducciones completas en Español, Catalán e Inglés.
*   `locale/*/locale.po`: Ficheros de traducción en formato PO para OJS 3.3+.

## Flujo de Trabajo de la IA

El plugin sigue un proceso automatizado para generar los resultados utilizando el modelo **gemini-2.0-flash-lite**:
1. **Extracción de Datos**: Recupera dinámicamente títulos y resúmenes de todos los artículos del número.
2. **Prompts Especializados**: Se ejecutan tres peticiones paralelas con instrucciones precisas:
   - **Matriz de Innovación**: Pide a la IA evaluar cada concepto en dos ejes (Madurez e Impacto) devolviendo JSON para un scatter plot.
   - **Editorial**: Instrucción de rol como "Editor Jefe" para generar contenido HTML estructurado.
   - **ODS**: Análisis de alineación con los Objetivos de Desarrollo Sostenible (Agenda 2030).
3. **Persistencia**: Los resultados se guardan en la base de datos, optimizando el consumo de tokens y el tiempo de respuesta en visitas posteriores.

## Almacenamiento de la Clave API

La clave API de Gemini se guarda de forma segura en la tabla **`plugin_settings`** de la base de datos de OJS. Los registros se asocian bajo los siguientes criterios:
*   `plugin_name`: `issuespotlightplugin`
*   `setting_name`: `apiKey`
*   `context_id`: El ID de la revista correspondiente.

## Creación de la Base de Datos (Manual)

Al no usar el instalador web de OJS, debes ejecutar este comando SQL en tu base de datos para crear la tabla necesaria:

```sql
CREATE TABLE issue_ai_analysis (
    issue_id BIGINT NOT NULL,
    editorial_draft TEXT,
    thematic_clusters TEXT,
    expert_suggestions TEXT,
    global_seo_description TEXT,
    tokens_consumed INT,
    date_generated DATETIME,
    UNIQUE KEY issue_ai_analysis_issue_id (issue_id)
);
```

## Pasos Finales para la Activación

1.  **Activar el Plugin**: Ve a *Ajustes > Website > Plugins* y activa el checkbox de **IssueSpotlight IA**.
2.  **Configurar la API Key**: 
    *   En la lista de plugins, haz clic en el triángulo azul al lado de IssueSpotlight IA.
    *   Selecciona "Configuración".
    *   Introduce tu **API Key de Google Gemini**.
3.  **Ejecutar el Análisis**:
    *   Ve a *Números > Números Futuros* o *Números Publicados*.
    *   Busca el nuevo botón azul **"IssueSpotlight IA"** en la fila del número que desees analizar.
    *   Haz clic y selecciona "Iniciar análisis con IA".

---
## Notas sobre Cuotas (Gemini Flash Lite 2.5)
*   **Consumo**: Cada análisis consume **3 peticiones** a la API.
*   **Límites**: Ten en cuenta que algunas cuentas gratuitas tienen límites diarios estrictos (ej. 20 peticiones/día), permitiendo analizar unos 6 números diarios.

---
*Nota: Si la tabla de base de datos no aparece tras activar, utiliza la opción "Actualizar" del plugin en la galería de OJS.*

## Detalles de Implementación v1.0

El plugin cuenta actualmente con un flujo de trabajo totalmente integrado con Google Gemini, diseñado para ofrecer estabilidad y feedback claro:

*   **Interfaz de Doble Botón**:
    *   **Test DB (Dummy)**: Un botón de diagnóstico que simula el proceso. Selecciona un título real aleatorio del número y genera datos falsos ("Lorem Ipsum") para verificar los permisos de escritura en la base de datos sin consumir cuota de la API.
    *   **Análisis REAL (Gemini)**: El botón azul principal que activa el procesamiento real de la IA.
*   **Preparación de Datos**: Antes de llamar a la IA, el plugin agrega eficientemente todos los títulos y resúmenes de los envíos del número en un único payload de texto.
*   **Integración con Gemini**: Se conecta al modelo `gemini-2.0-flash-lite` utilizando la API Key almacenada de forma segura en `PluginSettingsDAO`.
*   **Flujo de Triple Análisis**:
    1.  **Matriz Impacto/Madurez**: Extrae conceptos clave y les asigna puntuaciones (0-100) en Madurez y Impacto para visualizar un mapa estratégico de cuadrantes.
    2.  **Editorial**: Genera un borrador HTML profesional actuando como Editor Jefe, entrelazando los artículos seleccionados en una narrativa coherente.
    3.  **Impacto ODS**: Devuelve JSON con ODS/Porcentaje/Color y **justificación cualitativa** para renderizar un Gráfico de Anillo y tarjetas con los iconos oficiales de la ONU.
*   **Persistencia**: Todos los datos generados (JSON del Radar, HTML del Editorial, HTML de Expertos) se almacenan en la tabla `issue_ai_analysis` mediante lógica UPSERT (Insertar o Actualizar), asegurando que el último análisis esté siempre disponible.
