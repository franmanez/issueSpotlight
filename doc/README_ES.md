# IssueSpotlight IA - Guía de Usuario y Funcionalidades

Este plugin permite a los editores de OJS 3.3 transformar los metadatos de un número de revista en una experiencia interactiva de análisis basada en Inteligencia Artificial (Google Gemini).

## Funcionalidades Principales

1.  **Resumen Editorial**: Un algoritmo de IA actúa como "Editor Jefe" para redactar un borrador de editorial (en HTML) que sintetiza los temas clave, identifica hilos conductores y destaca las contribuciones más relevantes del número.
2.  **Radar de Innovación**: Un gráfico interactivo de burbujas (Bubble Chart) que visualiza conceptos tecnológicos y metodológicos. El tamaño indica la frecuencia y el color representa la tendencia (En Alza, Novedad o Consolidado).
3.  **Impacto ODS (Agenda 2030)**: Evaluación automática de la alineación de los artículos con los Objetivos de Desarrollo Sostenible de la ONU, incluyendo justificaciones cualitativas y visualización mediante gráficos y tarjetas oficiales.
4.  **Mapa Global e Institucional**: 
    *   **Geolocalización**: Mapeo de todas las instituciones participantes.
    *   **Análisis Institucional**: Visualización de la distribución geográfica de autores e instituciones representadas. El tamaño de los puntos indica la densidad de autores por centro.
    *   **Directorio de Autores**: Listado completo de autores, afiliaciones y artículos vinculados para una transparencia total.

## Flujo de Trabajo

El plugin utiliza el modelo **gemini-2.5-flash-lite** para procesar la información en cuatro etapas:
1.  **Extracción**: Obtiene títulos y resúmenes de todos los artículos publicados.
2.  **Análisis Geográfico**: Normaliza afiliaciones y geocodifica instituciones para el mapa.
3.  **Análisis Multilingüe**: Ejecuta prompts especializados para el Radar, la Editorial, los ODS y el Mapa Geo, generando respuestas en todos los idiomas configurados en la revista.
4.  **Persistencia**: Guarda los resultados por cada idioma en la base de datos para acceso instantáneo.

## Lógica de los Prompts (IA)

Cada sección del análisis se basa en una instrucción específica a la IA:
*   **Radar de Innovación**: Extrae conceptos técnicos evitando términos genéricos. Obliga a usar bigramas/trigramas para mayor precisión y clasifica su tendencia.
*   **Editorial**: Actúa como Editor Jefe para sintetizar el número en formato HTML estructurado.
*   **Impacto ODS**: Clasifica los artículos según los Objetivos de Desarrollo Sostenible de la ONU con justificaciones técnicas.
*   **Geo-Normalización**: Limpia y normaliza los nombres de las instituciones y las ubica en el mapa.

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

4.  **Ejecución**: En el listado de números (Futuros o Publicados), utiliza el botón azul **"IssueSpotlight IA"** para iniciar el proceso. Al iniciar el proceso, la IA analizará los títulos, resúmenes y afiliaciones para generar automáticamente el borrador editorial, el radar de innovación, el impacto ODS y el mapa de instituciones.

## Notas de Uso
*   **Privacidad**: Solo se envían a la IA los títulos, resúmenes y afiliaciones (datos públicos).
*   **Cuotas y Límites**: Cada análisis completo consume **4 llamadas** al LLM de Gemini. Si utilizas la versión gratuita, ten en cuenta que existe una limitación de **20 llamadas diarias**.
*   **Regeneración**: Si los resultados no son satisfactorios o has actualizado los artículos, puedes **regenerar el análisis** en cualquier momento. El nuevo proceso sustituirá los datos existentes por los nuevos.

## Solución de Problemas

Si encuentras errores durante el análisis, aquí están las causas más comunes y sus soluciones:

### Error: "The model is overloaded. Please try again later."
**Causa**: El modelo de Gemini está experimentando una alta demanda y está temporalmente sobrecargado.

**Solución**: Este es un error temporal del servicio de Google. Simplemente espera unos minutos y vuelve a ejecutar el análisis haciendo clic nuevamente en el botón **"IssueSpotlight IA"**. Puedes intentarlo varias veces hasta que funcione.

### Error: Límite de cuota superado
**Causa**: Has alcanzado el límite diario de llamadas al LLM de Gemini (20 llamadas diarias en el plan gratuito).

**Solución**: Dado que cada análisis consume 4 llamadas, puedes analizar un máximo de 5 números por día con el plan gratuito. Espera hasta el día siguiente para continuar, o considera actualizar a un plan de pago de Google Gemini si necesitas realizar más análisis.
