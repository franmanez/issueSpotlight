<?php
/**
 * @file plugins/generic/issueSpotlight/classes/IssueSpotlightSettingsForm.inc.php
 *
 * Copyright (c) 2026 UPC
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class IssueSpotlightSettingsForm
 * @brief Form for plugin settings.
 */

import('lib.pkp.classes.form.Form');
import('lib.pkp.classes.form.validation.FormValidator');
import('lib.pkp.classes.form.validation.FormValidatorPost');

class IssueSpotlightSettingsForm extends Form {

	/** @var int Context ID */
	public $_contextId;

	/** @var IssueSpotlightPlugin Plugin object */
	public $_plugin;

	/**
	 * Constructor
	 * @param $plugin IssueSpotlightPlugin
	 * @param $contextId int
	 */
	public function __construct($plugin, $contextId) {
		$this->_contextId = $contextId;
		$this->_plugin = $plugin;

		parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));

		$this->addCheck(new FormValidator($this, 'apiKey', 'required', 'plugins.generic.issueSpotlight.settings.apiKeyRequired'));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	/**
	 * Initialize form data
	 */
	public function initData() {
		$this->_data = array(
			'apiKey' => $this->_plugin->getSetting($this->_contextId, 'apiKey'),
		);
	}

	/**
	 * Assign form data to user-submitted data
	 */
	public function readInputData() {
		$this->readUserVars(array('apiKey'));
	}

	/**
	 * @copydoc Form::fetch()
	 */
	function fetch($request, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->_plugin->getName());
		return parent::fetch($request, $template, $display);
	}

	/**
	 * Save settings
	 */
	public function execute(...$functionArgs) {
		$this->_plugin->updateSetting($this->_contextId, 'apiKey', $this->getData('apiKey'));
		parent::execute(...$functionArgs);
	}
}
