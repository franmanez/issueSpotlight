# IssueSpotlight IA - Resumen de Implementación

Este plugin permite a los editores de OJS 3.3 obtener una visión global de un número completo de la revista utilizando Inteligencia Artificial (Google Gemini).

## Funcionalidades Principales

1.  **Radar de Innovación**: Analiza todos los artículos del número y genera un gráfico de burbujas (simulado en el listado) con las temáticas agrupadas por relevancia y estado (Novedoso, En auge, Estable).
2.  **Sintetizador Editorial**: Redacta un borrador de editorial que identifica hilos conductores y tendencias comunes entre los trabajos del número.
3.  **Identificador de Expertos**: Sugiere una lista de posibles revisores expertos basados en la calidad y temática de los autores que han publicado en el número.

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
   - **Radar**: Prompts diseñados para devolver JSON puro con etiquetas normalizadas y conteos.
   - **Editorial**: Instrucción de rol como "Editor Jefe" para generar contenido HTML estructurado.
   - **Expertos**: Análisis semántico de los autores del número para sugerencias de revisión por pares.
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
*Nota: Si la tabla de base de datos no aparece tras activar, utiliza la opción "Actualizar" del plugin en la galería de OJS.*
