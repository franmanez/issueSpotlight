<?php
/**
 * @file plugins/generic/issueSpotlight/IssueSpotlightPlugin.inc.php
 *
 * Copyright (c) 2026 UPC
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class IssueSpotlightPlugin
 * @brief AI-based issue analysis and synthesis plugin.
 */

import('lib.pkp.classes.plugins.GenericPlugin');
import('lib.pkp.classes.core.JSONMessage');

class IssueSpotlightPlugin extends GenericPlugin {

	/**
	 * @component IssueSpotlight
	 */

	/**
	 * @desc Initialize the plugin
	 */
	public function register($category, $path, $mainContextId = null) {
		if (parent::register($category, $path, $mainContextId)) {
			if ($this->getEnabled($mainContextId)) {
				AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON, LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APP_EDITOR, LOCALE_COMPONENT_PKP_MANAGER);
				
				// Standard OJS 3.3 hooks for grids are lowercase
				HookRegistry::register('futureissuegridhandler::initfeatures', array($this, 'handleInitFeatures'));
				HookRegistry::register('backissuegridhandler::initfeatures', array($this, 'handleInitFeatures'));
				HookRegistry::register('issuegridhandler::initfeatures', array($this, 'handleInitFeatures'));

				// Some versions might use CamelCase
				HookRegistry::register('FutureIssueGridHandler::initFeatures', array($this, 'handleInitFeatures'));
				HookRegistry::register('BackIssueGridHandler::initFeatures', array($this, 'handleInitFeatures'));
				HookRegistry::register('IssueGridHandler::initFeatures', array($this, 'handleInitFeatures'));

				// Workaround for BackIssueGridHandler which overrides initFeatures without calling parent
				HookRegistry::register('TemplateManager::fetch', array($this, 'handleTemplateFetch'));
				
				// Hook crítico para cargar nuestro controlador personalizado del Backend
				HookRegistry::register('LoadComponentHandler', array($this, 'handleLoadComponentHandler'));

				// Hook para el Frontend (Página pública)
				HookRegistry::register('LoadHandler', array($this, 'handleLoadHandler'));
				
				// Hook para inyectar botón en la vista del número
				HookRegistry::register('TemplateManager::display', array($this, 'handleTemplateDisplay'));
			}
			return true;
		}
		return false;
	}

	/**
	 * @desc Add the spotlight feature to the grid
	 */
	public function handleInitFeatures($hookName, $args) {
		$grid =& $args[0];
		$returner =& $args[3];

		$this->import('classes.IssueSpotlightGridFeature');
		$returner[] = new IssueSpotlightGridFeature($this);

		return false;
	}

	/**
	 * @desc Get the display name
	 */
	public function getDisplayName() {
		return __('plugins.generic.issueSpotlight.displayName');
	}

	/**
	 * @desc Get the description
	 */
	public function getDescription() {
		return __('plugins.generic.issueSpotlight.description');
	}

	/**
	 * @desc Get the actions
	 */
	public function getActions($request, $verb) {
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');

		return array_merge(
			$this->getEnabled()?array(
				new LinkAction(
					'settings',
					new AjaxModal(
						$router->url($request, null, null, 'manage', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic')),
						$this->getDisplayName()
					),
					__('manager.plugins.settings'),
					'settings'
				),
			):array(),
			parent::getActions($request, $verb)
		);
	}

	/**
	 * @desc Handle manage verbs
	 */
	public function manage($args, $request) {
		$context = $request->getContext();
		$verb = $request->getUserVar('verb');

		switch ($verb) {
			case 'settings':
				$this->import('classes.IssueSpotlightSettingsForm');
				$form = new IssueSpotlightSettingsForm($this, $context->getId());
				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						return new JSONMessage(true);
					}
				}
				$form->initData();
				return new JSONMessage(true, $form->fetch($request));

			case 'analysis':
				$issueId = (int) $request->getUserVar('issueId');
				$contextId = $request->getContext()->getId();
				// Devolvemos un HTML simple para que no falle el parseo de la modal
				return new JSONMessage(true, "<h3>Debug Info</h3><p>Journal ID: $contextId</p><p>Issue ID: $issueId</p><p>Estado: Comunicación funcional</p>");

			case 'runAnalysis':
				return new JSONMessage(true, "Análisis simulado OK");
		}
		return parent::manage($args, $request);
	}

	/**
	 * @desc Check if this plugin is enabled
	 */
	public function getEnabled($contextId = null) {
		return parent::getEnabled($contextId);
	}


	/**
	 * @desc Handle adding buttons to the issues grid
	 */
	public function handleIssueGridActions($hookName, $args) {
		$templateMgr = $args[1];
		$output = &$args[2];
		
		// Logic to inject the "Spotlight" button goes here.
		// For now, we just ensure the hook is active.
		return false;
	}

	/**
	 * @desc Interceptar carga de componentes para usar nuestro propio Handler
	 */
	public function handleLoadComponentHandler($hookName, $args) {
		$component =& $args[0];
		if ($component == 'plugins.generic.issueSpotlight.controllers.grid.IssueSpotlightGridHandler') {
			// Autorizamos la carga de esta clase específica
			import($component);
			return true;
		}
		return false;
	}

	/**
	 * @desc Handle Frontend Page Routing
	 */
	public function handleLoadHandler($hookName, $args) {
		$page = $args[0];
		$op = $args[1];
		$sourceFile = $args[2];

		if ($page === 'issueSpotlight') {
			$this->import('pages.IssueSpotlightHandler');
			define('HANDLER_CLASS', 'IssueSpotlightHandler');
			return true;
		}
		return false;
	}

	/**
	 * @desc Inject "View AI Analysis" button into Issue TOC
	 */
	public function handleTemplateDisplay($hookName, $args) {
		$templateMgr = $args[0];
		$template = $args[1];

		if ($template == 'frontend/pages/issue.tpl') {
			$issue = $templateMgr->get_template_vars('issue');
			if ($issue) {
				// Comprobamos si hay análisis para este número
				$dao = new DAO();
				$result = $dao->retrieve('SELECT count(*) as c FROM issue_ai_analysis WHERE issue_id = ?', [(int)$issue->getId()]);
				$row = (object) $result->current();

				if ($row && isset($row->c) && $row->c > 0) {
					// Preparamos el HTML del botón
					$request = Application::get()->getRequest();
					$url = $request->url(null, 'issueSpotlight', 'view', $issue->getId());
					$btnHtml = '<div class="issue_spotlight_promo" style="margin: 20px 0; text-align: center;"><a href="' . $url . '" class="pkp_button pkp_button_primary" style="background:#2c832c; border-color:#2c832c; color:white;"> ✨ Ver Análisis de Inteligencia Artificial</a></div>';
					
					// Registramos un Output Filter para inyectar el HTML
					$templateMgr->registerFilter('output', function($output, $smarty) use ($btnHtml) {
						// Buscamos el final de la descripción o, si no hay, del encabezado
						if (strpos($output, 'class="description"') !== false) {
							// Inyectar después del cierre del div description
							return preg_replace('/(<div class="description">.*?<\/div>)/s', '$1' . $btnHtml, $output, 1);
						} elseif (strpos($output, 'class="heading"') !== false) {
							// Fallback: Inyectar al final del heading
							return str_replace('</div>', $btnHtml . '</div>', $output); // Riesgoso si hay muchos divs, mejor ser específico
						}
						// Último recurso: inyectar antes de la lista de secciones
						return str_replace('<div class="sections">', $btnHtml . '<div class="sections">', $output);
					});
				}
			}
		}
		return false;
	}

	/**
	 * @desc Workaround to inject actions into grids that don't call the initFeatures hook
	 */
	public function handleTemplateFetch($hookName, $args) {
		$templateMgr = $args[0];
		$template = $args[1];

		if ($template == 'controllers/grid/gridRow.tpl') {
			$row = $templateMgr->get_template_vars('row');
			if (is_a($row, 'IssueGridRow')) {
				$issue = $row->getData();
				if (is_a($issue, 'Issue')) {
					$actions = $row->getActions(GRID_ACTION_POSITION_DEFAULT);
					if (!isset($actions['issueSpotlight'])) {
						$request = Application::get()->getRequest();
						$router = $request->getRouter();
						
						import('lib.pkp.classes.linkAction.LinkAction');
						import('lib.pkp.classes.linkAction.request.AjaxModal');

						$row->addAction(
							new LinkAction(
								'issueSpotlight',
								new AjaxModal(
									$router->url($request, null, 'plugins.generic.issueSpotlight.controllers.grid.IssueSpotlightGridHandler', 'analysis', null, array(
										'issueId' => $issue->getId()
									)),
									__('plugins.generic.issueSpotlight.analysisTitle'), // Título formal de ventana
									'modal_information'
								),
								__('plugins.generic.issueSpotlight.displayName'), // Título oficial del botón (IssueSpotlight IA)
								'information'
							)
						);
					}
				}
			}
		}
		return false;
	}
}
