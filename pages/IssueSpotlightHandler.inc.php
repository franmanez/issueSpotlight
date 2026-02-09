<?php
/**
 * @file plugins/generic/issueSpotlight/pages/IssueSpotlightHandler.inc.php
 *
 * Copyright (c) 2026 UPC - Universitat Politècnica de Catalunya
 * Author: Fran Máñez <fran.upc@gmail.com>, <francisco.manez@upc.edu>
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class IssueSpotlightHandler
 * @ingroup plugins_generic_issueSpotlight
 *
 * @brief Frontend page handler. Serves the public-facing AI analysis dashboard with
 *        interactive visualizations (Innovation Radar, SDG charts, and institutional maps).
 */

import('classes.handler.Handler');
import('lib.pkp.classes.db.DAO');

class IssueSpotlightHandler extends Handler {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @copydoc PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		// Permitir acceso público explícito.
		// Al retornar true inmediatamente, saltamos la verificación estricta de roles.
		// Validaremos el contexto y publicación manualmente en el método view().
		return true;
	}

	/**
	 * View the AI Analysis page
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function view($args, $request) {
		$issueId = isset($args[0]) ? (int) $args[0] : 0;
		if (!$issueId) $request->redirect(null, 'index');

		// Validate Issue access (must be published)
		$issueDao = DAORegistry::getDAO('IssueDAO');
		$issue = $issueDao->getById($issueId, $request->getContext()->getId());
		
		if (!$issue || !$issue->getPublished()) {
			$request->redirect(null, 'index');
		}

		// Setup template manually (avoid calling setupTemplate which requires authorized context)
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON, LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APP_SUBMISSION);
		$templateMgr = TemplateManager::getManager($request);

		// Fetch Analysis Data
		$dao = new DAO();
		$currentLocale = AppLocale::getLocale();
		$result = $dao->retrieve(
			'SELECT * FROM issue_ai_analysis WHERE issue_id = ? AND locale = ?',
			[$issueId, $currentLocale]
		);
		$analysisData = (object) $result->current();

		if (!$analysisData || !isset($analysisData->issue_id)) {
			// Fallback: try to find any existing analysis for this issue if the current locale is missing
			$result = $dao->retrieve('SELECT * FROM issue_ai_analysis WHERE issue_id = ? LIMIT 1', [$issueId]);
			$analysisData = (object) $result->current();
		}

		if (!$analysisData || !isset($analysisData->issue_id)) {
			// If no analysis exists at all, redirect back to issue page
			$request->redirect(null, 'issue', 'view', $issueId);
		}

		// Fetch Authors and Affiliations for the table
		$authorsData = [];
		$submissionsIterator = Services::get('submission')->getMany([
			'contextId' => $request->getContext()->getId(),
			'issueIds' => $issueId,
		]);
		
		foreach ($submissionsIterator as $submission) {
			$publication = $submission->getCurrentPublication();
			if ($publication) {
				$authors = $publication->getData('authors');
				$title = $publication->getLocalizedTitle();
				if ($authors) {
					foreach ($authors as $author) {
						$authorsData[] = [
							'name' => $author->getFullName(),
							'affiliation' => $author->getLocalizedAffiliation(),
							'article' => $title
						];
					}
				}
			}
		}

		// Pass data to view
		$templateMgr->assign('issue', $issue);
		$templateMgr->assign('issueId', $issueId);
		$templateMgr->assign('editorialDraft', $analysisData->editorial_draft);
		$templateMgr->assign('thematicClusters', json_decode($analysisData->radar_analysis, true));
		$templateMgr->assign('expertSuggestions', json_decode($analysisData->ods_analysis, true));
		$templateMgr->assign('geoAnalysis', json_decode($analysisData->geo_analysis, true));
		$templateMgr->assign('authorsData', $authorsData);
		

		// Render
		// Intento 1: Vía Plugin (Estándar)
		$plugin = PluginRegistry::getPlugin('generic', 'issueSpotlight');
		if ($plugin) {
			$templateMgr->display($plugin->getTemplateResource('frontend/analysis_view.tpl'));
		} else {
			// Intento 2: Ruta Absoluta (Fallback)
			// Definimos ruta manual relativa a este archivo Handler
			$templatePath = dirname(dirname(__FILE__)) . '/templates/frontend/analysis_view.tpl';
			$templateMgr->display('file:' . $templatePath);
		}
	}
}
