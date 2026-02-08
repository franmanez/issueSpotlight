# IssueSpotlight IA - Guía de Usuario y Funcionalidades

Este plugin permite a los editores de OJS 3.3 transformar los metadatos de un número de revista en una experiencia interactiva de análisis basada en Inteligencia Artificial (Google Gemini).

## Funcionalidades Principales

1.  **Resumen Editorial**: Un algoritmo de IA actúa como "Editor Jefe" para redactar un borrador de editorial (en HTML) que sintetiza los temas clave, identifica hilos conductores y destaca las contribuciones más relevantes del número.
2.  **Radar de Innovación**: Un gráfico interactivo de burbujas (Bubble Chart) que visualiza conceptos tecnológicos y metodológicos. El tamaño indica la frecuencia y el color representa la tendencia (En Alza, Novedad o Consolidado).
3.  **Impacto ODS (Agenda 2030)**: Evaluación automática de la alineación de los artículos con los Objetivos de Desarrollo Sostenible de la ONU, incluyendo justificaciones cualitativas y visualización mediante gráficos y tarjetas oficiales.
4.  **Mapa Global y Red de Colaboración**: 
    *   **Geolocalización**: Mapeo de todas las instituciones participantes.
    *   **Análisis de Colaboración**: Visualización de vínculos nacionales e internacionales entre instituciones mediante líneas curvas animadas.
    *   **Directorio de Autores**: Listado completo de autores, afiliaciones y artículos vinculados para una transparencia total.

## Flujo de Trabajo

El plugin utiliza el modelo **Gemini 2.0 Flash Lite** para procesar la información en cuatro etapas:
1.  **Extracción**: Obtiene títulos y resúmenes de todos los artículos publicados.
2.  **Análisis Geográfico**: Normaliza afiliaciones y geocodifica instituciones para el mapa.
3.  **Generación de Conocimiento**: Ejecuta prompts especializados para el Radar, la Editorial y los ODS.
4.  **Persistencia**: Guarda los resultados en la base de datos para acceso instantáneo por parte de los lectores.

## Configuración y Activación

1.  **Instalación**: Copia la carpeta del plugin en `plugins/generic/issueSpotlight`.
2.  **Base de Datos**: Asegúrate de que la tabla `issue_ai_analysis` existe (ver sección técnica).
### Cómo obtener la Clave API de Gemini (Gratuita)

Para que el plugin funcione, necesitas una clave de acceso a la inteligencia artificial de Google:

1.  Accede a **[Google AI Studio](https://aistudio.google.com/)** e inicia sesión con tu cuenta de Google.
2.  En el menú lateral izquierdo, haz clic en el icono de la llave o en el botón **"Get API key"** (Obtener clave API).
3.  Pulsa en el botón azul **"Create API key"** (Crear clave API) y selecciona un proyecto (o crea uno nuevo).
4.  Copia la clave alfanumérica generada.
5.  En tu OJS, ve a **Ajustes > Website > Plugins**, busca "IssueSpotlight IA" y pulsa en **Ajustes** para pegar la clave.

4.  **Ejecución**: En el listado de números (Futuros o Publicados), utiliza el botón azul **"IssueSpotlight IA"** para iniciar el proceso.

## Notas de Uso
*   **Privacidad**: Solo se envían a la IA los títulos, resúmenes y afiliaciones (datos públicos).
*   **Cuotas**: Un análisis completo realiza varias peticiones a la API. Asegúrate de tener cuota disponible en tu plan de Gemini.
