# IssueSpotlight IA - Technical Documentation & Architecture

This document details the architectural decisions, design patterns, and specific implementation strategies used in the development of the **IssueSpotlight IA** plugin for OJS 3.3. It serves as a comprehensive guide for developers wishing to understand, maintain, or extend the plugin.

---

## 1. Architectural Philosophy

The plugin is designed under the **"Zero-Core-Modification"** principle. All functionalities are injected via the standard OJS Hook / Plugin system. This ensures that:
- OJS updates (minor versions) do not break the plugin.
- The OJS core code remains pristine.
- Installation is as simple as dropping the folder into `plugins/generic/`.

## 2. Directory Structure & Key Files

The plugin follows a strict MVC-like structure adapted for OJS plugins:

```text
plugins/generic/issueSpotlight/
├── IssueSpotlightPlugin.inc.php       # [ENTRY POINT] Main registration class
├── classes/
│   ├── IssueSpotlightService.inc.php  # [SERVICE] Logic for API calls & Data handling
│   ├── IssueSpotlightGridFeature.inc.php # [UI HELPER] Injects buttons into Backend Grids
│   ├── IssueSpotlightSettingsForm.inc.php # [FORM] Settings management
│   └── handlers/                      # [NEW] Custom Request Handlers
│       ├── IssueSpotlightGridHandler.inc.php # [BACKEND HANDLER] Handles AJAX requests from the dashboard
│       └── IssueSpotlightHandler.inc.php     # [FRONTEND HANDLER - TODO] Handles public page requests
├── controllers/grid/ ...              # (Legacy path for GridHandler)
├── templates/
│   ├── analysis.tpl                   # [VIEW] Backend modal interface
│   └── frontend/                      
│       └── analysis_view.tpl          # [VIEW - TODO] Public frontend interface
├── locale/                            # Localization files (en_US, es_ES, ca_ES)
├── schema.xml                         # Database schema definition using ADODB
└── index.php                          # Initializer
```

## 3. Backend Implementation (The Management Interface)

### 3.1. Grid Injection Strategy
To add the "IssueSpotlight IA" button to the Back Issues and Future Issues grids without modifying their templates, we use the `GridFeature` system.

*   **Hook**: `IssueGridHandler::initFeatures` (and variants for Back/Future).
*   **Logic**: The plugin intercepts the grid initialization and pushes a new `IssueSpotlightGridFeature` instance into the features array.
*   **Technique**: This feature adds a `LinkAction` to every row.

### 3.2. Communication & Routing (The Component Router)
OJS 3.3 has strict component routing rules. We bypassed the standard `SettingsPluginGridHandler` limitations by implementing a custom GridHandler.

*   **Handler**: `IssueSpotlightGridHandler.inc.php`
*   **Routing Hook**: `LoadComponentHandler`. We intercept this hook to tell OJS: "When someone asks for `plugins.generic.issueSpotlight.controllers.grid.IssueSpotlightGridHandler`, load this specific file."
*   **Purpose**: This handler receives the AJAX requests to run the AI analysis, communicates with Gemini, and saves the results.

## 4. AI & Data Persistence

### 4.1. The "Gemini" Integration
The core logic resides in `IssueSpotlightGridHandler::runAnalysisReal`.
1.  **Preparation**: Aggregates all article titles and abstracts from the issue into a single text block.
2.  **API Call**: Uses `curl` to send this block to Google's `gemini-2.0-flash-lite` model.
3.  **Prompt Engineering**: Uses 3 distinct prompts (Radar, Editorial, Experts) in a single workflow to maximize efficiency.

### 4.2. Database Storage
Data is persisted in a custom table `issue_ai_analysis`.
*   **Schema**: `issue_id` (Unique Key), `editorial_draft`, `thematic_clusters`, `expert_suggestions`.
*   **Logic**: Uses an "UPSERT" strategy (Check if exists -> Update / Else -> Insert) to ensure idempotency.

## 5. Frontend Implementation (Public Visibility)

### 5.1. The "Public Page" Strategy (Recommended Option)
This approach creates a dedicated, SEO-friendly page for the analysis results.

*   **URL Pattern**: `.../index.php/[JOURNAL]/issueSpotlight/view/[ISSUE_ID]`
*   **Hook 1: Visual Injection (`TemplateManager::display`)**:
    *   Target: `frontend/pages/issue.tpl` (The issue landing page).
    *   **Technique**: We register a **Smarty Output Filter**. This PHP callback intercepts the final HTML before it's sent to the browser.
    *   Logic: It searches for the issue description block (`<div class="description">`) and injects the "View AI Analysis" button HTML immediately after it using `preg_replace`. This ensures server-side compatibility without relying on client-side JS injection.
*   **Hook 2: Routing (`LoadHandler`)**:
    *   Target: When OJS processes the URL.
    *   Action: If the page request matches `issueSpotlight`, the plugin intercepts it and loads `pages/IssueSpotlightHandler.inc.php`.

### 5.2. The Frontend Handler
The `IssueSpotlightHandler` (in `pages/`) extends `PKPHandler` and acts as a standard page controller:
1.  **Authorize**: Validates that the requested issue ID exists, belongs to the current context, and is published.
2.  **Fetch Data**: Queries the `issue_ai_analysis` table for the specific `issue_id`.
3.  **Render**: Loads the `templates/frontend/analysis_view.tpl` file, passing the decoded JSON data (Radar) and HTML blocks (Editorial, Experts) to Smarty.

### 5.3. The Frontend View
The view (`analysis_view.tpl`) uses standard OJS frontend components (`header.tpl`, `footer.tpl`) to inherit the journal's theme. It implements a tabbed interface using simple CSS/JS to switch between:
*   **Editorial Layout**: Displays the AI-generated HTML draft.
*   **Radar Grid**: Iterates over the JSON clusters to render colorful cards indicating topic relevance and status.
*   **Experts List**: Displays the suggested reviewers.

### 5.4. Resilience Strategy (Template Loading)
To prevent `Call to member function on null` errors when `PluginRegistry` fails to return the plugin instance during a generic dispatched request, the handler implements a **path fallback mechanism**.
1.  It attempts to load the template via the standard `$plugin->getTemplateResource()`.
2.  If `$plugin` is null, it falls back to a calculated absolute path (`dirname(__FILE__) . '/path/to/tpl'`) and uses Smarty's `file:` protocol. This ensures the view always renders.

---

## 6. How to Extend/Debug

*   **Logs**: Check PHP error logs (Apache/Nginx) for `Call to undefined method` or API connection errors.
*   **Database**: Verify the `issue_ai_analysis` table for raw data inspection.
*   **Cache**: Always clear the "Data Cache" and "Template Cache" in the OJS Administration panel after making changes to templates or hooks.
