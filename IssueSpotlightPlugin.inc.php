<?php
/**
 * @file plugins/generic/issueSpotlight/IssueSpotlightPlugin.inc.php
 *
 * Copyright (c) 2026 UPC - Universitat Politècnica de Catalunya
 * Author: Fran Máñez <fran.upc@gmail.com>, <francisco.manez@upc.edu>
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class IssueSpotlightPlugin
 * @ingroup plugins_generic_issueSpotlight
 *
 * @brief Main plugin class for IssueSpotlight IA. Integrates Google Gemini AI to generate 
 *        automated editorial synthesis, innovation radar, SDG impact analysis, and institutional mapping.
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
	/**
	 * @desc Inject "View AI Analysis" button into Issue TOC using specific hooks
	 */
	public function handleTemplateDisplay($hookName, $args) {
		$templateMgr = $args[0];
		$template = $args[1];

		// Registramos el filtro solo si estamos en la vista del número
		if ($template == 'frontend/pages/issue.tpl' || $template == 'frontend/objects/issue_toc.tpl') {
			$templateMgr->registerFilter('output', array($this, 'safeOutputFilter'));
		}
		return false;
	}

	/**
	 * @desc Filtro de salida seguro para inyectar el botón sin duplicados
	 */
	public function safeOutputFilter($output, $smarty) {
		static $locked = false;
		if ($locked) return $output;

		// Solo procesar si hay rastros del TOC o de la página del número
		if (strpos($output, 'obj_issue_toc') !== false || strpos($output, 'page_issue') !== false) {
			$issue = $smarty->get_template_vars('issue');
			if (!$issue) return $output;

			// Comprobar si hay análisis
			$dao = new DAO();
			$result = $dao->retrieve('SELECT count(*) as c FROM issue_ai_analysis WHERE issue_id = ?', [(int)$issue->getId()]);
			$row = (object) $result->current();

			if ($row && isset($row->c) && $row->c > 0) {
				$request = Application::get()->getRequest();
				$url = $request->url(null, 'issueSpotlight', 'view', $issue->getId());
				
				$btnHtml = '<div class="issue_spotlight_banner" style="' . 
							'background: #f0f7fb; ' . 
							'border: 1px solid #d1e6fa; ' . 
							'border-left: 5px solid #006798; ' . 
							'padding: 12px 20px; ' .
							'margin: 20px 0 40px 0; ' . 
							'border-radius: 4px; ' .
							'display: flex; ' .
							'justify-content: space-between; ' .
							'align-items: center; ' .
							'gap: 20px; ' .
							'">' .
								'<div style="flex: 1;">' .
									'<h4 style="margin: 0; color: #006798; font-size: 1.05em; font-weight: 600;">✨ ' . __("plugins.generic.issueSpotlight.banner.title") . '</h4>' .
								'</div>' .
								'<a href="' . $url . '" class="pkp_button" style="' . 
									'background: linear-gradient(135deg, #006798 0%, #111 100%); ' . 
									'color: white !important; ' . 
									'border: none; ' . 
									'padding: 10px 22px; ' . 
									'border-radius: 3px; ' . 
									'font-weight: bold; ' . 
									'text-decoration: none; ' . 
									'white-space: nowrap; ' .
									'display: inline-block;' .
									'transition: all 0.3s ease;' .
									'font-size: 0.95em;' .
									'box-shadow: 0 4px 12px rgba(0,0,0,0.25);' .
								'">🤖 ' . __("plugins.generic.issueSpotlight.banner.btn") . '</a>' .
						   '</div>';
				
				// Estrategia de Inyección Multinivel
				// 1. Después de la descripción (Ideal)
				if (strpos($output, 'class="description"') !== false) {
					$output = preg_replace('/(<div class="description">.*?<\/div>)/s', '$1' . $btnHtml, $output, 1);
					$locked = true;
				} 
				// 2. Al inicio del heading (Fallback 1)
				elseif (strpos($output, 'class="heading"') !== false) {
					$output = preg_replace('/(<div class="heading">)/', '$1' . $btnHtml, $output, 1);
					$locked = true;
				}
				// 3. Al inicio del contenedor TOC (Fallback 2)
				elseif (strpos($output, 'class="obj_issue_toc"') !== false) {
					$output = preg_replace('/(<div class="obj_issue_toc">)/', '$1' . $btnHtml, $output, 1);
					$locked = true;
				}
			}
		}

		return $output;
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
