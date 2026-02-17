# IssueSpotlight AI for OJS 3.3+

![OJS Compatibility](https://img.shields.io/badge/OJS-3.3%2B-blue.svg) ![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg) ![License](https://img.shields.io/badge/License-GPLv3-green.svg) ![Status](https://img.shields.io/badge/Status-Beta-orange.svg)

> **[🇪🇸 Ver documentación en Español](README_es.md)**

**IssueSpotlight AI** is a native Open Journal Systems (OJS) plugin that leverages generative artificial intelligence (**Google Gemini 2.0/2.5 Flash**) to transform static issue metadata into an interactive and visual discovery dashboard.

---

## 🚀 Key Features

The plugin automatically analyzes article titles, abstracts, and author affiliations to generate four layers of added value:

### 1. 📝 Intelligent Editorial Synthesis
Acts as an editorial agent that reads all abstracts in an issue and drafts a **thematic narrative**.
*   Identifies common threads across articles.
*   Groups contributions by discipline or methodology.
*   Generates semantic HTML ready for publication.

### 2. 📡 Innovation Radar
Interactive visualization (*Packed Bubble Chart*) that detects technological and methodological concepts.
*   **Trend Classification**: Dynamically tags concepts as **New**, **Rising**, or **Stable**.
*   **Semantic Cleaning**: Uses specificity rules to filter out generic terms ("Research", "Analysis") and prioritize technical *bigrams* ("Deep Learning", "Urban Sustainability").

### 3. 🌍 Global Institutional Map
Advanced geolocation of authorship.
*   **Normalization**: AI unifies institution names (e.g., "UPC", "Polytechnic Univ.", "Universitat Politècnica de Catalunya" -> Single Node).
*   **Visualization**: Interactive map (Leaflet.js) with **spiral jittering** to prevent overlap in high-density cities.

### 4. 🎯 SDG Impact (Agenda 2030)
Evaluates the issue's alignment with the UN **Sustainable Development Goals**.
*   Assigns relevance percentages to detected SDGs.
*   Generates a qualitative **reasoning** explaining why the research contributes to global goals.

---

## 🛠️ Installation & Configuration

### Prerequisites
*   OJS 3.3.0 or higher.
*   PHP 7.4+ with `cURL` extension enabled.
*   A **Google Gemini API Key** (Free Tier available).

### Step 1: Installation
1.  Download the `.tar.gz` file from the latest release.
2.  In OJS, go to **Website Settings > Plugins > Upload A New Plugin**.
3.  Upload the file. The plugin will appear under "Generic Plugins".
4.  Enable the plugin using the checkbox.

### Step 2: Get Your API Key
1.  Go to [Google AI Studio](https://aistudio.google.com/).
2.  Sign in and click "Get API key".
3.  Create a free API key.

### Step 3: Configuration
1.  In the OJS plugin list, find **IssueSpotlight AI**.
2.  Click the blue arrow > **Settings**.
3.  Paste your API Key and save.

---

## 📖 Usage Guide (For Editors)

The analysis is NOT executed automatically to avoid costs or unwanted publishing.

1.  Go to **Issues > Future Issues** (or Back Issues).
2.  Locate the issue you want to analyze.
3.  Click the **"IssueSpotlight AI"** button in the grid row.
4.  In the modal window, click **"START AI ANALYSIS"**.
5.  Wait a few seconds (10-20s). Once finished, the analysis is stored in the database and becomes instantly visible on the public issue page.

---

## 🤓 Technical Details (Architecture & Prompts)

This section is intended for developers and system administrators.

### "Zero-Core Modification" Architecture
The plugin strictly follows PKP's Hook system. It does **not modify any core OJS files**.
*   **Frontend**: Injection via `TemplateManager::display`.
*   **Backend**: Custom Controllers (`LoadHandler`).
*   **Persistence**: Custom table `issue_ai_analysis`.

### Persistence Strategy (Batch Processing)
To mitigate latency and API quota limits:
1.  **On-Demand Analysis**: Only the editor triggers the process.
2.  **SQL Storage**: The resulting JSON is stored in the `issue_ai_analysis` table.
3.  **Fast Read**: Readers view cached data from the DB. **Read traffic does NOT consume API quota.**

### Prompt Engineering
The system uses 4 specialized prompts designed to prevent hallucinations and ensure strict format compliance:

| Component | Prompt Strategy |
| :--- | :--- |
| **Radar** | **Exclusion Rules**: Blacklist of stop words ("Study", "Data"). **Bigram Enforcing**: Demands compound concepts (2+ words). |
| **Editorial** | **Role-Playing**: "Act as Editor-in-Chief". **Format Restriction**: Strict HTML output without Markdown headers. |
| **ODS** | **Data Anchoring**: Official UN HEX color codes are injected into the prompt to ensure visual consistency. |
| **Geo** | **Normalization**: AI acts as a data curator, inferring coordinates (Lat/Lng) from incomplete or varied institution names. |

### Known Limitations
*   **Language**: Analysis is performed and stored in the journal's **Primary Locale** to maximize the LLM context window and prevent JSON truncations.
*   **API Quota (Free Tier)**: Google Gemini imposes a limit of ~15-20 requests/day (approx. 5 full issues/day). The plugin handles `Model Overload` errors transparently.

---

## 🔒 Privacy & Data
*   The plugin sends the following to Google: Titles, Abstracts, and Institution Names (public metadata).
*   **It does NOT send**: Email addresses, unpublished articles, or peer review data.

---

**Developed by:** Fran Máñez - Universitat Politècnica de Catalunya (UPC)
