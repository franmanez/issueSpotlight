# IssueSpotlight IA - Implementation Summary

This plugin enables OJS 3.3 editors to gain a comprehensive global overview of a magazine issue using Artificial Intelligence (Google Gemini).

## Core Functionalities

1.  **Innovation Radar**: Analyzes all articles in the issue to generate a thematic analysis grouped by relevance and status (Novel, Rising, Stable).
2.  **Editorial Synthesizer**: Drafts an editorial proposal that identifies common threads and trends among the works in the issue.
3.  **Expert Identifier**: Suggests a list of potential expert reviewers based on the quality and topics of the authors published in the issue.

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
   - **Radar**: Prompts designed to return pure JSON with normalized tags and counts.
   - **Editorial**: Role-based instruction as "Editor-in-Chief" to generate structured HTML content.
   - **Experts**: Semantic analysis of the issue's authors for peer-review suggestions.
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
*Note: If the database table is not created after activation, use the "Upgrade" option for the plugin in the OJS gallery.*
