<?php
/**
 * @file plugins/generic/issueSpotlight/classes/IssueSpotlightService.inc.php
 *
 * Copyright (c) 2026 UPC - Universitat Politècnica de Catalunya
 * Author: Fran Máñez <fran.upc@gmail.com>, <francisco.manez@upc.edu>
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class IssueSpotlightService
 * @ingroup plugins_generic_issueSpotlight
 *
 * @brief Service layer for AI analysis operations and data retrieval.
 */

class IssueSpotlightService {

	/** @var IssueSpotlightPlugin */
	var $_plugin;

	/**
	 * Constructor
	 * @param $plugin IssueSpotlightPlugin
	 */
	public function __construct($plugin) {
		$this->_plugin = $plugin;
	}

	/**
	 * Get all articles data for an issue
	 * @param $issueId int
	 * @return string
	 */
	public function getIssuePayload($issueId) {
		// In OJS 3.3, we use SubmissionDAO to get articles in an issue
		$submissionDao = DAORegistry::getDAO('SubmissionDAO');
		$submissionsFactory = $submissionDao->getByIssueId($issueId);
		
		$payload = "";
		while ($submission = $submissionsFactory->next()) {
			$id = $submission->getId();
			$publication = $submission->getCurrentPublication();
			
			if (!$publication) continue;

			$title = $publication->getLocalizedTitle();
			$abstract = strip_tags($publication->getLocalizedData('abstract'));
			
			$payload .= "ID: $id | TITULO: $title | RESUMEN: $abstract\n\n";
		}
		
		return $payload;
	}

	/**
	 * Call Gemini API
	 * @param $contextId int
	 * @param $prompt string
	 * @param $data string
	 * @return string|null
	 */
	public function callGemini($contextId, $prompt, $data) {
		$apiKey = $this->_plugin->getSetting($contextId, 'apiKey');
		if (!$apiKey) return null;

		$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent?key=" . $apiKey;
		
		$body = array(
			'contents' => array(
				array(
					'parts' => array(
						array('text' => $prompt . "\n\nDATOS:\n" . $data)
					)
				)
			)
		);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		// Skip SSL verification for local dev if needed, but better keep it for safety unless it fails
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($httpCode === 200) {
			$json = json_decode($response, true);
			if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
				return $json['candidates'][0]['content']['parts'][0]['text'];
			}
		}

		return null;
	}
}
