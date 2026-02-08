# IssueSpotlight IA - User Guide & Features

This plugin allows OJS 3.3 editors to transform issue metadata into an interactive AI-driven analysis experience (powered by Google Gemini).

## Key Features

1.  **Editorial Summary**: An AI algorithm acts as an "Editor-in-Chief" to draft an editorial (in HTML) that synthesizes key themes, identifies common threads, and highlights the most relevant contributions of the issue.
2.  **Innovation Radar**: An interactive bubble chart that visualizes technological and methodological concepts. Size indicates frequency, and color represents the trend (Rising, New, or Stable).
3.  **SDG Impact (2030 Agenda)**: Automatic assessment of article alignment with the UN Sustainable Development Goals, including qualitative justifications and visualization through official charts and cards.
4.  **Global Map & Collaboration Network**:
    *   **Geolocation**: Mapping of all participating institutions.
    *   **Collaboration Analysis**: Visualization of national and international links between institutions via animated curved lines.
    *   **Authors Directory**: Full list of authors, affiliations, and linked articles for total transparency.

## Workflow

The plugin uses the **Gemini 2.0 Flash Lite** model to process information in four stages:
1.  **Extraction**: Retrieves titles and abstracts from all published articles.
2.  **Geographic Analysis**: Normalizes affiliations and geocodes institutions for the map.
3.  **Knowledge Generation**: Executes specialized prompts for the Radar, Editorial, and SDGs.
4.  **Persistence**: Saves results in the database for instant access by readers.

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

4.  **Execution**: In the issue lists (Future or Back issues), use the blue **"IssueSpotlight IA"** button to start the process.

## Usage Notes
*   **Privacy**: Only titles, abstracts, and affiliations (public data) are sent to the AI.
*   **Quotas**: A full analysis performs several API requests. Ensure you have available quota in your Gemini plan.
