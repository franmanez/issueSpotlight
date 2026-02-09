# IssueSpotlight IA - User Guide & Features

This plugin allows OJS 3.3 editors to transform issue metadata into an interactive AI-driven analysis experience (powered by Google Gemini).

## Key Features

1.  **Editorial Summary**: An AI algorithm acts as an "Editor-in-Chief" to draft an editorial (in HTML) that synthesizes key themes, identifies common threads, and highlights the most relevant contributions of the issue.
2.  **Innovation Radar**: An interactive bubble chart that visualizes technological and methodological concepts. Size indicates frequency, and color represents the trend (Rising, New, or Stable).
3.  **SDG Impact (2030 Agenda)**: Automatic assessment of article alignment with the UN Sustainable Development Goals, including qualitative justifications and visualization through official charts and cards.
4.  **Global & Institutional Map**:
    *   **Geolocation**: Mapping of all participating institutions.
    *   **Institutional Analysis**: Visualization of the geographical distribution of authors and represented institutions. Bubble size indicates author density per center.
    *   **Authors Directory**: Full list of authors, affiliations, and linked articles for total transparency.

## Workflow

The plugin uses the **gemini-2.5-flash-lite** model to process information in four stages:
1.  **Extraction**: Retrieves titles and abstracts from all published articles.
2.  **Geographic Analysis**: Normalizes affiliations and geocodes institutions for the map.
3.  **Multilingual Analysis**: Executes specialized prompts for Radar, Editorial, SDGs, and Geo Map, generating responses for all configured journal locales.
4.  **Persistence**: Saves results per language in the database for instant access.

## AI Prompts Logic

Each analysis section is powered by a specific instruction to the AI:
*   **Innovation Radar**: Extracts technical concepts while avoiding generic terms. Enforces bigrams/trigrams for higher precision and classifies their trend.
*   **Editorial**: Acts as the Editor-in-Chief to synthesize the issue into structured HTML.
*   **SDG Impact**: Classifies articles according to UN Sustainable Development Goals with technical justifications.
*   **Geo-Normalization**: Cleanses and normalizes institution names and locates them on the map.

## Configuration & Activation

1.  **Installation**: Copy the plugin folder to `plugins/generic/issueSpotlight`.
2.  **Database**: Ensure the `issue_ai_analysis` table exists (see technical section).
### How to Obtain the Gemini API Key (Free)

For the plugin to work, you need an access key for Google's artificial intelligence:

1.  Go to **[Google AI Studio](https://aistudio.google.com/)** and sign in with your Google account.
2.  In the left sidebar, click on the key icon or the **"Get API key"** button.
3.  Click the blue **"Create API key"** button and select a project (or create a new one).
4.  Copy the generated alphanumeric key.
5.  In your OJS, go to **Settings > Website > Plugins**, find "IssueSpotlight IA," and click on **Settings** to paste the key.

4.  **Execution**: In the issue lists (Future or Back issues), use the blue **"IssueSpotlight IA"** button to start the process. Upon starting, the AI will analyze titles, abstracts, and affiliations to automatically generate the editorial draft, innovation radar, SDG impact, and institutional map.

## Usage Notes
*   **Privacy**: Only titles, abstracts, and affiliations (public data) are sent to the AI.
*   **Quotas and Limits**: Each complete analysis consumes **4 calls** to the Gemini LLM. If you use the free version, be aware of the **20 daily calls** limit.
*   **Regeneration**: If the results are not satisfactory or you have updated the articles, you can **regenerate the analysis** at any time. The new process will replace existing data with new results.

## Troubleshooting

If you encounter errors during analysis, here are the most common causes and their solutions:

### Error: "The model is overloaded. Please try again later."
**Cause**: The Gemini model is experiencing high demand and is temporarily overloaded.

**Solution**: This is a temporary Google service error. Simply wait a few minutes and run the analysis again by clicking the **"IssueSpotlight IA"** button. You can try multiple times until it works.

### Error: Quota limit exceeded
**Cause**: You have reached the daily Gemini LLM call limit (20 daily calls on the free plan).

**Solution**: Since each analysis consumes 4 calls, you can analyze a maximum of 5 issues per day with the free plan. Wait until the next day to continue, or consider upgrading to a paid Google Gemini plan if you need to perform more analyses.
