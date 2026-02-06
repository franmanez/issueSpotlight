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
    editorial_draft LONGTEXT,           -- HTML content for editorial
    radar_analysis LONGTEXT,            -- JSON for bubble chart
    ods_analysis LONGTEXT,              -- JSON for SDG impact
    geo_analysis LONGTEXT,              -- JSON for Map (institutions + collaborations)
    global_seo_description TEXT,        -- Metadata for search engines
    tokens_consumed INT,                -- Tracking (optional)
    date_generated DATETIME,
    PRIMARY KEY (issue_id)
);
```

## 4. Key Implementation Details

### 4.1. Innovation Radar (Highcharts)
Replaced the old scatter plot with a **Packed Bubble Chart**.
*   **Physics**: Custom gravity and friction settings to avoid overlap.
*   **Logic**: AI groups concepts and assigns trends (`rising`, `new`, `stable`).

### 4.2. Global Map (Leaflet & AntPath)
*   **Geocoding**: Gemini acts as a geocoder to normalize institution names and find coordinates.
*   **Visualization**:
    *   **Bezier Curves**: Collaboration lines are rendered as animated paths.
    *   **Spiral Jittering**: Prevents overlapping markers when multiple institutions share the same coordinates.
    *   **Interactive Layers**: Tooltips on markers and lines showing institution names and collaboration types.

### 4.3. Data Persistence (The UPSERT pattern)
The `IssueSpotlightGridHandler` implements a strict persistence logic ensuring that all 4 analysis blocks (Editorial, Radar, ODS, Geo) are saved together only if all API calls succeed, preventing inconsistent data states.

## 5. Integration with Gemini API
*   **Model**: `gemini-2.0-flash-lite` (optimized for speed and JSON reliability).
*   **Prompts**: Specialized system instructions for each analysis type, ensuring strict JSON output when required.
*   **Error Handling**: Real-time detection of API Quota limits, displaying clear red notifications in the UI.

## 6. Frontend Extension
The `IssueSpotlightHandler` fetches not only the AI results but also queries OJS core tables to provide a "Real-time Authors Directory" in the Global Map tab, merging AI-generated insights with official metadata.
