# IssueSpotlight IA - Implementation Summary

This plugin enables OJS 3.3 editors to gain a comprehensive global overview of a magazine issue using Artificial Intelligence (Google Gemini).

## Core Functionalities

1.  **Innovation Matrix**: Generates a scatter plot positioning the issue's topics based on Academic Maturity and Potential Impact, helping to identify future stakes and classics.
2.  **Editorial Synthesizer**: Drafts an editorial proposal that identifies common threads and trends among the works in the issue.
3.  **SDG Impact**: Calculates and visualizes (Donut Chart + Official Icons) the percentage contribution of the issue to the UN Sustainable Development Goals.

## Created File Structure

*   `IssueSpotlightPlugin.inc.php`: Core class that registers OJS hooks and injects functionality.
*   `classes/IssueSpotlightService.inc.php`: Handles article metadata extraction and Gemini API communication (`gemini-2.0-flash-lite` model).
*   `classes/IssueSpotlightGridFeature.inc.php`: Dynamically injects the "IssueSpotlight IA" button into the Issues grid rows (Future and Back issues).
*   `classes/IssueSpotlightSettingsForm.inc.php`: Settings form class to save the API Key per context.
*   `templates/analysis.tpl`: User interface to display the analysis results in tabs.
*   `templates/settingsForm.tpl`: Interface for the plugin configuration.
*   `schema.xml`: Creates the `issue_ai_analysis` table for data persistence.
*   `locale/*/locale.xml`: Complete translations in English, Spanish, and Catalan.
*   `locale/*/locale.po`: Translation files in PO format for OJS 3.3+.

## AI Workflow

The plugin follows an automated process to generate results using the **gemini-2.0-flash-lite** model:
1. **Data Extraction**: Dynamically retrieves titles and abstracts from all issue articles.
2. **Specialized Prompts**: Three parallel requests are executed with precise instructions:
   - **Innovation Matrix**: Asks the AI to evaluate each concept on two axes (Maturity and Impact) returning JSON for a scatter plot.
   - **Editorial**: Role-based instruction as "Editor-in-Chief" to generate structured HTML content.
   - **SDG**:  Alignment analysis with the Sustainable Development Goals (2030 Agenda).
3. **Persistence**: Results are saved in the database, optimizing token consumption and response time for subsequent visits.

## API Key Storage

The Gemini API Key is securely stored in the **`plugin_settings`** table of the OJS database. The records are associated using the following criteria:
*   `plugin_name`: `issuespotlightplugin`
*   `setting_name`: `apiKey`
*   `context_id`: The ID of the corresponding journal.

## Database Creation (Manual)

Since we are not using the OJS web installer, you must run this SQL command in your database to create the necessary table:

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

## Final Activation Steps

1.  **Activate the Plugin**: Go to *Settings > Website > Plugins* and check the **IssueSpotlight IA** box.
2.  **Configure the API Key**: 
    *   In the plugin list, click the blue triangle next to IssueSpotlight IA.
    *   Select "Settings".
    *   Enter your **Google Gemini API Key**.
3.  **Run the Analysis**:
    *   Navigate to *Issues > Future Issues* or *Back Issues*.
    *   Look for the new blue **"IssueSpotlight IA"** button in the row of the issue you wish to analyze.
    *   Click it and select "Start AI Analysis".

---
## Notes on Quotas (Gemini Flash Lite 2.5)
*   **Consumption**: Each full analysis performs **3 requests** to the API.
*   **Limits**: Be aware that some free accounts have strict daily limits (e.g., 20 requests/day), allowing you to fully analyze about 6 issues per day.

---
*Note: If the database table is not created after activation, use the "Upgrade" option for the plugin in the OJS gallery.*

## Implementation Details v1.0

The plugin currently features a fully integrated workflow with Google Gemini, designed for stability and clear feedback:

*   **Dual-Button Interface**:
    *   **Test DB (Dummy)**: A diagnostic button that simulates the process. It picks a random real title from the issue and generates fake "Loren Ipsum" data to verify database write permissions without consuming API quotas.
    *   **REAL Analysis (Gemini)**: The main blue button that triggers the actual AI processing.
*   **Data Preparation**: Before calling the AI, the plugin efficiently aggregates all titles and abstracts from the issue's submissions into a single text payload.
*   **Gemini Integration**: Connects to the `gemini-2.0-flash-lite` model using the API Key stored securely in the `PluginSettingsDAO`.
*   **Triple Analysis Workflow**:
    1.  **Innovation Matrix**: Extracts key concepts and assigns scores (0-100) for Maturity and Impact to visualize a strategic quadrant map.
    2.  **Editorial**: Generates a professional HTML draft acting as an Editor-in-Chief, weaving the selected articles into a coherent narrative.
    3.  **SDG Impact**: Returns JSON with SDG/Percentage/Color and **qualitative reasoning** to render a Donut Chart and cards with official UN icons.
*   **Persistence**: All generated data (Matrix JSON, Editorial HTML, SDG JSON) is stored in the `issue_ai_analysis` table via UPSERT (Insert or Update) logic, ensuring the latest analysis is always available.
