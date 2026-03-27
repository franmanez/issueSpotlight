<?php
/**
 * @file plugins/generic/issueSpotlight/classes/IssueSpotlightGridFeature.php
 *
 * Copyright (c) 2026 UPC - Universitat Politècnica de Catalunya
 * Author: Fran Máñez <fran.upc@gmail.com>, <francisco.manez@upc.edu>
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class IssueSpotlightGridFeature
 * @ingroup plugins_generic_issueSpotlight
 *
 * @brief Grid feature to inject the "IssueSpotlight IA" action button into the backend issue grid.
 */

namespace APP\plugins\generic\issueSpotlight\classes;

use APP\core\Application;
use PKP\controllers\grid\feature\GridFeature;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;

class IssueSpotlightGridFeature extends GridFeature {
	/** @var \APP\plugins\generic\issueSpotlight\IssueSpotlightPlugin */
	var $_plugin;

	/**
	 * Constructor.
	 * @param $plugin \APP\plugins\generic\issueSpotlight\IssueSpotlightPlugin
	 */
	function __construct($plugin) {
		parent::__construct('issueSpotlight');
		$this->_plugin = $plugin;
	}

	/**
	 * @copydoc GridFeature::getInitializedRowInstance()
	 */
	function getInitializedRowInstance($args) {
		$row =& $args['row'];
		$issue = $row->getData();
		$request = Application::get()->getRequest();
		$router = $request->getRouter();

		if (is_a($issue, 'OJS\issue\Issue')) {
			$row->addAction(
				new LinkAction(
					'issueSpotlight',
					new AjaxModal(
						$router->url($request, null, null, 'manage', null, array(
							'verb' => 'analysis',
							'issueId' => $issue->getId(),
							'plugin' => $this->_plugin->getName(),
							'category' => 'generic'
						)),
						__('plugins.generic.issueSpotlight.displayName') . ': ' . $issue->getIssueIdentification(),
						'modal_information'
					),
					__('plugins.generic.issueSpotlight.displayName'),
					'information'
				)
			);
		}
	}
}
