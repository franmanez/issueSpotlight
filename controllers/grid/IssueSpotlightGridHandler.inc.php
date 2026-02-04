<?php
import('lib.pkp.classes.controllers.grid.GridHandler');
import('lib.pkp.classes.db.DAO');
import('lib.pkp.classes.core.Core');

class IssueSpotlightGridHandler extends GridHandler {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			[ROLE_ID_MANAGER, ROLE_ID_SITE_ADMIN],
			['analysis', 'runAnalysisDummy', 'runAnalysisReal']
		);
	}

	/**
	 * @copydoc PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.ContextAccessPolicy');
		$this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));

		import('classes.security.authorization.OjsIssueRequiredPolicy');
		$this->addPolicy(new OjsIssueRequiredPolicy($request, $args));

		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Handle analysis request (View)
	 */
	function analysis($args, $request) {
		$issue = $this->getAuthorizedContextObject(ASSOC_TYPE_ISSUE);
		$context = $request->getContext();
		$router = $request->getRouter();
		
		// URLs para acciones
		$dummyUrl = $router->url($request, null, 'plugins.generic.issueSpotlight.controllers.grid.IssueSpotlightGridHandler', 'runAnalysisDummy', null, array('issueId' => $issue->getId()));
		$realUrl = $router->url($request, null, 'plugins.generic.issueSpotlight.controllers.grid.IssueSpotlightGridHandler', 'runAnalysisReal', null, array('issueId' => $issue->getId()));

		// Obtener artículos
		$submissionsIterator = Services::get('submission')->getMany([
			'contextId' => $context->getId(),
			'issueIds' => $issue->getId(),
		]);
		
		$idsList = '<ul style="line-height: 1.6;">';
		$count = 0;
		foreach ($submissionsIterator as $submission) {
			$title = $submission->getLocalizedTitle();
			if (!$title) {
				$publication = $submission->getCurrentPublication();
				if ($publication) $title = $publication->getLocalizedTitle();
			}
			$titleStr = $title ? htmlspecialchars($title) : '<em>(Sin título)</em>';
			
			$idsList .= '<li><strong>ID ' . $submission->getId() . ':</strong> ' . $titleStr . '</li>';
			$count++;
		}
		$idsList .= '</ul>';

		// Script JS unificado
		$jsScript = "
		<script>
			function triggerAnalysis(type) {
				var btnId = type === 'real' ? 'btnRunReal' : 'btnRunDummy';
				var url = type === 'real' ? '$realUrl' : '$dummyUrl';
				var btn = document.getElementById(btnId);
				var statusDiv = document.getElementById('analysisStatus');
				
				var originalText = btn.innerHTML;
				btn.disabled = true;
				btn.innerHTML = (type === 'real') ? 'Conectando con Gemini...' : 'Procesando inserción...';
				statusDiv.innerHTML = '<div class=\"pkp_spinner\"></div> Procesando, por favor espera...';

				$.ajax({
					url: url,
					dataType: 'json',
					success: function(data) {
						btn.disabled = false;
						btn.innerHTML = originalText;
						if(data.status) {
							statusDiv.innerHTML = '<div class=\"pkp_notification\" style=\"margin-top:10px; padding:15px; background:#e6fffa; border:1px solid #2c832c; color:#2c832c;\">' + data.content + '</div>';
						} else {
							statusDiv.innerHTML = '<div class=\"pkp_notification\" style=\"margin-top:10px; padding:15px; background:#ffe6e6; border:1px solid #d9534f; color:#d9534f;\"><strong>Error:</strong> ' + data.content + '</div>';
						}
					},
					error: function(xhr, textStatus, errorThrown) {
						btn.disabled = false;
						btn.innerHTML = originalText;
						statusDiv.innerHTML = '<div class=\"pkp_notification\" style=\"margin-top:10px; padding:15px; background:#ffe6e6; border:1px solid #d9534f; color:#d9534f;\"><strong>Error de red:</strong> ' + errorThrown + '</div>';
					}
				});
			}
		</script>";

		// Construir el HTML de respuesta
		$content = '<div style="padding: 20px;">' .
			'<h3>Datos del Número</h3>' .
			'<ul>' .
			'<li><strong>ID Revista:</strong> ' . $context->getId() . '</li>' .
			'<li><strong>ID Número:</strong> ' . $issue->getId() . '</li>' .
			'<li><strong>Título:</strong> ' . $issue->getIssueIdentification() . '</li>' .
			'</ul>' .
			'<hr>' .
			'<h4>Artículos Seleccionados (' . $count . ')</h4>' .
			$idsList .
			'<div style="margin-top: 20px; text-align: center; border-top: 1px solid #eee; padding-top: 20px;">' .
			'<button id="btnRunDummy" class="pkp_button" style="margin-right: 10px;" onclick="triggerAnalysis(\'dummy\')">Test DB (Dummy)</button>' .
			'<button id="btnRunReal" class="pkp_button pkp_button_primary" onclick="triggerAnalysis(\'real\')">Análisis REAL (Gemini)</button>' .
			'<div id="analysisStatus" style="margin-top: 15px;"></div>' .
			'</div>' .
			$jsScript .
			'</div>';

		return new JSONMessage(true, $content);
	}

	/**
	 * Handle dummy analysis execution
	 */
	function runAnalysisDummy($args, $request) {
		// (Mantengo la función dummy idéntica para referencia)
		$issue = $this->getAuthorizedContextObject(ASSOC_TYPE_ISSUE);
		$contextId = $request->getContext()->getId();
		
		// INSERTAR LÓGICA DUMMY (Ya probada)
		$submissionsIterator = Services::get('submission')->getMany(['contextId' => $contextId, 'issueIds' => $issue->getId()]);
		$titles = [];
		foreach ($submissionsIterator as $s) {
			$t = $s->getLocalizedTitle() ?: ($s->getCurrentPublication() ? $s->getCurrentPublication()->getLocalizedTitle() : '');
			if ($t) $titles[] = $t;
		}
		$randomTitle = !empty($titles) ? $titles[array_rand($titles)] : "Sin artículos";
		
		$this->_persistAnalysisData($issue->getId(), 
			"<h3>Editorial Dummy</h3><p>Prueba con: $randomTitle</p>", 
			json_encode([['tag' => 'DummyTag', 'count' => 10, 'status' => 'Estable']]), 
			"<ul><li>Experto Dummy</li></ul>"
		);

		return new JSONMessage(true, "<strong>Test OK!</strong> Datos dummy guardados usando: <em>$randomTitle</em>");
	}

	/**
	 * Handle REAL analysis execution with Gemini
	 */
	function runAnalysisReal($args, $request) {
		$issue = $this->getAuthorizedContextObject(ASSOC_TYPE_ISSUE);
		$context = $request->getContext();
		$contextId = $context->getId();

		// 1. Obtener API Key
		$pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
		$apiKey = $pluginSettingsDao->getSetting($contextId, 'issuespotlightplugin', 'apiKey');

		if (!$apiKey) {
			return new JSONMessage(false, "No se encontró la API Key en la configuración del plugin.");
		}

		// 2. Construir Payload
		$submissionsIterator = Services::get('submission')->getMany(['contextId' => $contextId, 'issueIds' => $issue->getId()]);
		$payload = "";
		foreach ($submissionsIterator as $submission) {
			$pub = $submission->getCurrentPublication();
			$title = $submission->getLocalizedTitle() ?: ($pub ? $pub->getLocalizedTitle() : '');
			$abstract = $pub ? strip_tags($pub->getLocalizedData('abstract')) : '';
			if ($title) $payload .= "TÍTULO: $title | RESUMEN: $abstract\n\n";
		}

		if (empty(trim($payload))) {
			return new JSONMessage(false, "El número no tiene artículos con contenido válido para analizar.");
		}

		// 3. Llamadas a Gemini (Secuenciales)
		// Prompt Radar
		$radarJson = $this->_callGemini($apiKey, "Analiza estos trabajos. Extrae 5-10 temas clave. Clasifica cada uno como 'Novedoso', 'En auge' o 'Estable'. Devuelve SOLO un array JSON válido: [{\"tag\":\"nombre\",\"count\":X,\"status\":\"tipo\"}]. No uses Markdown.", $payload);
		// Limpieza básica de JSON si Gemini añade ```json ... ```
		$radarJson = str_replace(['```json', '```'], '', $radarJson);

		// Prompt Editorial
		$editorialHtml = $this->_callGemini($apiKey, "Actúa como Editor Jefe. Escribe una editorial corta (max 200 palabras) en HTML (usando <p>, <h3>, <ul>). Agrupa los artículos por temáticas comunes y destaca tendencias. Sé profesional y académico.", $payload);
		
		// Prompt Expertos
		$expertsHtml = $this->_callGemini($apiKey, "Basándote en los temas de estos artículos, sugiere 5 perfiles de expertos ideales para revisarlos. Devuelve una lista HTML <ul> con los perfiles.", $payload);

		if (!$radarJson || !$editorialHtml || !$expertsHtml) {
			return new JSONMessage(false, "Fallo en la comunicación con Gemini. Verifica tu cuota o conexión.");
		}

		// 4. Guardar en Base de Datos
		$this->_persistAnalysisData($issue->getId(), $editorialHtml, $radarJson, $expertsHtml);

		return new JSONMessage(true, "<strong>¡Análisis Completado!</strong> Los datos reales de Gemini se han guardado correctamente para el número " . $issue->getIssueIdentification());
	}

	/**
	 * Helper: Persistir datos
	 */
	private function _persistAnalysisData($issueId, $editorial, $radar, $experts) {
		$dao = new DAO();
		$result = $dao->retrieve('SELECT count(*) as c FROM issue_ai_analysis WHERE issue_id = ?', [(int)$issueId]);
		$row = (object) $result->current();
		$date = Core::getCurrentDate();

		if ($row && isset($row->c) && $row->c > 0) {
			$dao->update(
				'UPDATE issue_ai_analysis SET editorial_draft = ?, thematic_clusters = ?, expert_suggestions = ?, date_generated = ? WHERE issue_id = ?',
				[$editorial, $radar, $experts, $date, (int)$issueId]
			);
		} else {
			$dao->update(
				'INSERT INTO issue_ai_analysis (issue_id, editorial_draft, thematic_clusters, expert_suggestions, date_generated) VALUES (?, ?, ?, ?, ?)',
				[(int)$issueId, $editorial, $radar, $experts, $date]
			);
		}
	}

	/**
	 * Helper: Llamada cURL a Gemini
	 */
	private function _callGemini($apiKey, $systemPrompt, $userContent) {
		$url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash-lite:generateContent?key=" . $apiKey;
		
		$data = [
			"contents" => [
				[
					"parts" => [
						["text" => $systemPrompt . "\n\nDATOS A ANALIZAR:\n" . substr($userContent, 0, 30000)] // Limitamos payload por seguridad
					]
				]
			]
		];

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		
		$response = curl_exec($ch);
		
		if (curl_errno($ch)) return false;
		curl_close($ch);

		$json = json_decode($response, true);
		if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
			return $json['candidates'][0]['content']['parts'][0]['text'];
		}
		
		return false;
	}
}
