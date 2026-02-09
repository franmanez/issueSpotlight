# IssueSpotlight IA - Technical Documentation & Architecture

This document details the architectural decisions, design patterns, and specific implementation strategies used in the development of the **IssueSpotlight IA** plugin for OJS 3.3.

## 1. Architectural Philosophy

The plugin follows the **"Zero-Core-Modification"** principle, ensuring compatibility with OJS updates. It uses the standard OJS Hook / Plugin system to inject UI elements and handle custom requests.

## 2. Updated Directory Structure

```text
plugins/generic/issueSpotlight/
├── IssueSpotlightPlugin.inc.php       # [ENTRY POINT] Main registration class
├── doc/                               # [NEW] Documentation folder (ES, EN, Technical)
├── classes/
│   ├── IssueSpotlightGridFeature.inc.php # [UI HELPER] Injects buttons into Backend Grids
│   ├── IssueSpotlightSettingsForm.inc.php # [FORM] Settings management
│   └── handlers/
│       ├── IssueSpotlightGridHandler.inc.php # [BACKEND] Handles AI generation AJAX calls
│       └── IssueSpotlightHandler.inc.php     # [FRONTEND] Handles public page delivery
├── pages/                             # Handler registration path
├── templates/
│   ├── analysis.tpl                   # [VIEW] Backend results modal
│   └── frontend/
│       └── analysis_view.tpl          # [VIEW] Public interactive dashboard
├── locale/                            # Multilingual support (ES, CA, EN)
├── schema.xml                         # Database definition (ADODB)
└── schema.sql                         # Manual SQL for quick deployment
```

## 3. Database Schema

The plugin uses a table named `issue_ai_analysis` to store analysis results.

```sql
CREATE TABLE issue_ai_analysis (
    issue_id BIGINT NOT NULL,
    locale VARCHAR(14) NOT NULL,        -- locale code (e.g., es_ES, en_US)
    editorial_draft LONGTEXT,           -- HTML content for editorial
    radar_analysis LONGTEXT,            -- JSON for bubble chart
    ods_analysis LONGTEXT,              -- JSON for SDG impact
    geo_analysis LONGTEXT,              -- JSON for Map (institutions + collaborations)
    date_generated DATETIME,
    PRIMARY KEY (issue_id, locale)
);
```

## 4. Key Implementation Details

### 4.1. Innovation Radar (Highcharts)
Replaced the old scatter plot with a **Packed Bubble Chart**.
*   **Specificity Rules**: The AI is strictly instructed to avoid generic terms (e.g., "Design", "Analysis") and prioritize bigrams or trigrams that define a specific niche (e.g., "Speculative Design").
*   **Physics**: Custom gravity and friction settings to maximize space utilization.
*   **Logic**: AI groups concepts and assigns trends (`rising`, `new`, `stable`) based on their contextual weight.
*   **Normalization**: Synonyms are grouped under the most technical term discovered.

### 4.2. Global Map (Leaflet)
*   **Geocoding**: Gemini acts as a geocoder to normalize institution names and find approximate coordinates.
*   **Visualization**:
    *   **Institutional Markers**: Renders institutions as circle markers.
    *   **Proportional Size**: Marker radius scales based on the number of authors (`count`).
    *   **Spiral Jittering**: Prevents overlapping markers when multiple institutions share the same coordinates.
*   **Simplified Logic**: Collaboration lines/networks were removed to focus on institutional density and avoid inconsistencies in AI-detected links.

### 4.3. Data Persistence (Multilingual UPSERT)
The `IssueSpotlightGridHandler` requests analysis for all active journal locales in a single LLM call per block. It stores one record per locale, ensuring the frontend displays the correct translation based on the user's selected language.

## 5. Detailed AI Prompts Logic

The plugin leverages specialized prompts for each analysis dimension:

### 5.1. Prompt Radar (Knowledge Discovery)
*   **Goal**: Extract high-value academic concepts.
*   **Rules**: 
    1.  **Specificity**: Mandatory use of 2-3 word concepts.
    2.  **Exclusion**: Forbidden generic terms list.
    3.  **Trend Logic**: 'new' (emergency), 'rising' (growing relevance), 'stable' (foundational method/tech).
*   **Output**: Multilingual JSON object keyed by locale.

### 5.2. Prompt Editorial (Synthesis)
*   **Persona**: Acts as "Editor-in-Chief".
*   **Goal**: Narrative synthesis of the issue.
*   **Format**: Direct HTML (`<h3>`, `<p>`, `<ul>`).
*   **Output**: Multilingual JSON object with localized HTML drafts.

### 5.3. Prompt ODS (Alignment)
*   **Goal**: Map research to UN Sustainable Development Goals.
*   **Constraint**: Strict use of official hex colors and numbering.
*   **Logic**: Distribute 100% impact among top 3-6 relevant goals with qualitative reasoning.

### 5.4. Prompt Geo (Geographical Normalization)
*   **Goal**: Clean affiliation data and geolocate.
*   **Logic**: Normalizes variations (e.g., "UPC" -> "Universitat Politècnica de Catalunya") and assigns City/Country/Coords.
*   **Note**: All references to collaboration links have been deprecated in favor of institutional distribution accuracy.

## 6. Integration with Gemini API
*   **Model**: `gemini-2.5-flash-lite` (optimized for speed and JSON reliability).
*   **Security**: Payloads are truncated to 30,000 characters to stay within safety limits while maintaining context.
*   **Architecture**: Each full issue analysis is orchestrated in **4 sequential Gemini LLM calls** (Editorial, Radar, ODS, and Geo/Institutions).
*   **Error Handling**: Real-time detection of API Quota limits, displaying clear red notifications in the UI. 

## 7. Frontend Extension
The `IssueSpotlightHandler` fetches the localized AI results and merges them with OJS core metadata (Authors directory) to provide a rich, interactive dashboard.
