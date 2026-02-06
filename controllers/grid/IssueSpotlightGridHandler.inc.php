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
		$authorsRows = '';
		$count = 0;

		foreach ($submissionsIterator as $submission) {
			// Title Logic
			$publication = $submission->getCurrentPublication();
			$title = $publication ? $publication->getLocalizedTitle() : $submission->getLocalizedTitle();
			$titleStr = $title ? htmlspecialchars($title) : '<em>(Sin título)</em>';
			
			$idsList .= '<li><strong>ID ' . $submission->getId() . ':</strong> ' . $titleStr . '</li>';
			
			// Authors Logic
			if ($publication) {
				$authors = $publication->getData('authors');
				if ($authors) {
					foreach ($authors as $author) {
						$aff = $author->getLocalizedAffiliation();
						$affStr = $aff ? htmlspecialchars($aff) : '<span style="color:#999">-</span>';
						$authorsRows .= '<tr>
							<td style="padding:5px;">' . htmlspecialchars($author->getFullName()) . '</td>
							<td style="padding:5px;">' . $affStr . '</td>
							<td style="padding:5px; font-size:0.85em; color:#666;">' . $titleStr . '</td>
						</tr>';
					}
				}
			}
			$count++;
		}
		$idsList .= '</ul>';

		$authorsTable = '<table class="pkp_table" style="width:100%; border-collapse: collapse;">
			<thead>
				<tr style="background:#f0f0f0; border-bottom:1px solid #ddd; text-align:left;">
					<th style="padding:8px;">Autor</th>
					<th style="padding:8px;">Afiliación</th>
					<th style="padding:8px;">Artículo</th>
				</tr>
			</thead>
			<tbody>' . $authorsRows . '</tbody>
		</table>';

		// Script JS unificado
		$jsScript = "
		<script>
			function switchAnalysisTab(tabName) {
				// Hide all
				document.getElementById('tab_articles').style.display = 'none';
				document.getElementById('tab_authors').style.display = 'none';
				document.getElementById('btn_tab_articles').classList.remove('pkp_button_primary');
				document.getElementById('btn_tab_authors').classList.remove('pkp_button_primary');
				
				// Show selected
				document.getElementById('tab_' + tabName).style.display = 'block';
				document.getElementById('btn_tab_' + tabName).classList.add('pkp_button_primary');
			}

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
			
			// Tab Buttons
			'<div style="margin-bottom: 15px;">' .
				'<button id="btn_tab_articles" class="pkp_button pkp_button_primary" onclick="switchAnalysisTab(\'articles\')">Artículos (' . $count . ')</button> ' .
				'<button id="btn_tab_authors" class="pkp_button" onclick="switchAnalysisTab(\'authors\')">Autores y Afiliaciones</button>' .
			'</div>' .

			// Tab Content: Articles
			'<div id="tab_articles">' .
				$idsList .
			'</div>' .

			// Tab Content: Authors
			'<div id="tab_authors" style="display:none; max-height: 400px; overflow-y: auto; border: 1px solid #eee;">' .
				$authorsTable .
			'</div>' .

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
			json_encode([['tag' => 'DummyTag', 'count' => 10, 'trend' => 'stable']]), 
			json_encode([['ods' => 4, 'name' => 'Educación', 'percentage' => 100, 'color' => '#C5192D', 'reasoning' => 'Test']]),
			json_encode(['institutions' => [], 'collaborations' => []]),
			"Descripción SEO Dummy"
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
		// Prompt 2: Radar de Innovación (Tags + Count + Trend)
		$promptRadar = "Analiza la siguiente lista de Títulos y Resúmenes de artículos académicos de un número de revista.
        Extrae los conceptos tecnológicos, metodológicos o teóricos más relevantes.
        Para cada concepto:
        1. Normaliza el nombre (ej: 'AI', 'Inteligencia Artificial' -> 'AI').
        2. Cuenta su frecuencia de aparición en los artículos (estimada).
        3. Determina su tendencia ('trend') basándote en el enfoque de los artículos:
           - 'new': Si se presenta como novedad, innovación o emergente.
           - 'rising': Si se menciona como tendencia creciente o muy relevante.
           - 'stable': Si es una tecnología/método base, comparativa o estándar.
        
        Devuelve SOLAMENTE un JSON array válido con los 20-30 conceptos más importantes:
        [{\"tag\": \"Concepto\", \"count\": 5, \"trend\": \"rising\"}, ...]";

		$radarContentRaw = $this->_callGemini($apiKey, $promptRadar, $payload);
		if (strpos($radarContentRaw, 'ERROR:') !== false) return new JSONMessage(false, $radarContentRaw);

		$radarContent = preg_replace('/```json|```/', '', $radarContentRaw);
		$radarJson = json_decode($radarContent, true);
		if (!$radarJson) $radarJson = [];
		
		// Prompt Editorial
		$editorialHtml = $this->_callGemini($apiKey, "Actúa como Editor Jefe. Escribe una editorial corta (max 200 palabras) en HTML (usando <p>, <h3>, <ul>). Agrupa los artículos por temáticas comunes y destaca tendencias. Sé profesional y académico.", $payload);
		if (strpos($editorialHtml, 'ERROR:') !== false) return new JSONMessage(false, $editorialHtml);

		// Prompt SEO Description
		$seoDescription = $this->_callGemini($apiKey, "Genera una meta-descripción SEO (máximo 160 caracteres) que resuma los temas principales de este número para buscadores. Debe ser atractiva y profesional. Devuelve solo el texto plano.", $payload);
		if (strpos($seoDescription, 'ERROR:') !== false) return new JSONMessage(false, $seoDescription);
		
		// Prompt ODS (Objetivos de Desarrollo Sostenible)
		$promptODS = "Analiza el contenido de los artículos y determina su contribución a los Objetivos de Desarrollo Sostenible (ODS) de la ONU.
		Distribuye un total de 100% entre los ODS más relevantes (mínimo 3, máximo 6).
		Devuelve SOLAMENTE un JSON array válido con este formato:
		[{\"ods\": 4, \"name\": \"Educación de Calidad\", \"percentage\": 30, \"color\": \"#C5192D\", \"reasoning\": \"Breve justificación de 1 frase explicando por qué aplica (menciona temas clave)\"}, ...]";

		$odsContentRaw = $this->_callGemini($apiKey, $promptODS, $payload);
		if (strpos($odsContentRaw, 'ERROR:') !== false) return new JSONMessage(false, $odsContentRaw);

		$odsContent = preg_replace('/```json|```/', '', $odsContentRaw);
		$odsJson = json_decode($odsContent, true);
		if (!$odsJson) $odsJson = [];

		// --- GEO-ANALYSIS (RESTORING PREVIOUS LOGIC) ---
		$affiliations = [];
		$submissionsIteratorGeo = Services::get('submission')->getMany(['contextId' => $contextId, 'issueIds' => $issue->getId()]);
		foreach ($submissionsIteratorGeo as $submission) {
			$publication = $submission->getCurrentPublication();
			if ($publication) {
				$authors = $publication->getData('authors');
				if ($authors) {
					foreach ($authors as $author) {
						$aff = $author->getLocalizedAffiliation();
						if ($aff) $affiliations[] = $aff;
					}
				}
			}
		}
		$uniqueAffiliations = array_unique($affiliations);
		
		if (empty($uniqueAffiliations)) {
			$geoJson = ['institutions' => [], 'collaborations' => []];
		} else {
			$affPayload = implode("\n", $uniqueAffiliations);

			$promptGeo = "Actúa como un experto en geografía institucional y bibliometría.
			Analiza la siguiente lista de afiliaciones de autores.
			1. Normaliza las instituciones (ej: 'UPC' -> 'Universitat Politècnica de Catalunya').
			2. Para cada institución única, encuentra su Ciudad, País y Coordenadas aproximadas (Latitud y Longitud).
			3. Identifica colaboraciones internacionales o nacionales probables entre estas instituciones.
			4. Devuelve SOLAMENTE un JSON con este formato exacto:
			{
				\"institutions\": [
					{\"name\": \"Nombre Real\", \"city\": \"Ciudad\", \"country\": \"País\", \"lat\": 0.0, \"lng\": 0.0, \"count\": número_de_autores}
				],
				\"collaborations\": [
					{\"from_name\": \"Nombre Inst 1\", \"to_name\": \"Nombre Inst 2\", \"from_lat\": 0, \"from_lng\": 0, \"to_lat\": 0, \"to_lng\": 0, \"type\": \"international|national\"}
				]
			}";

			$geoContentRaw = $this->_callGemini($apiKey, $promptGeo, $affPayload);
			if (strpos($geoContentRaw, 'ERROR:') !== false) return new JSONMessage(false, $geoContentRaw);

			$geoContent = preg_replace('/```json|```/', '', $geoContentRaw);
			$geoContent = trim(preg_replace('/^[^{]*|[^}]*$/', '', $geoContent));
			$geoJson = json_decode($geoContent, true);
			if (!$geoJson) $geoJson = ['institutions' => [], 'collaborations' => []];
		}

		// 4. Guardar en Base de Datos de forma explícita
		$dataToPersist = [
			'editorial' => $editorialHtml,
			'radar'     => json_encode($radarJson, JSON_UNESCAPED_UNICODE),
			'ods'       => json_encode($odsJson, JSON_UNESCAPED_UNICODE),
			'geo'       => json_encode($geoJson, JSON_UNESCAPED_UNICODE),
			'seo'       => $seoDescription
		];

		$this->_persistAnalysisData(
			$issue->getId(), 
			$dataToPersist['editorial'], 
			$dataToPersist['radar'], 
			$dataToPersist['ods'], 
			$dataToPersist['geo'], 
			$dataToPersist['seo']
		);

		return new JSONMessage(true, "<strong>¡Análisis Completado!</strong> Los datos reales de Gemini se han guardado correctamente (incluyendo Mapa y ODS) para el número " . $issue->getIssueIdentification());
	}

	/**
	 * Helper: Persistir datos
	 */
	private function _persistAnalysisData($issueId, $editorial, $radar, $ods, $geo, $seo) {
		$dao = new DAO();
		$result = $dao->retrieve('SELECT count(*) as c FROM issue_ai_analysis WHERE issue_id = ?', [(int)$issueId]);
		$row = (object) $result->current();
		$date = Core::getCurrentDate();

		// Log para depuración extrema
		error_log("IssueSpotlight Debug: Persistiendo ID " . $issueId);
		error_log("IssueSpotlight Debug: SEO text: " . substr($seo, 0, 100));
		error_log("IssueSpotlight Debug: GEO JSON: " . substr($geo, 0, 100));

		if ($row && isset($row->c) && $row->c > 0) {
			$dao->update(
				'UPDATE issue_ai_analysis 
				 SET editorial_draft = ?, 
				     radar_analysis = ?, 
				     ods_analysis = ?, 
				     geo_analysis = ?, 
				     global_seo_description = ?, 
				     date_generated = ? 
				 WHERE issue_id = ?',
				[$editorial, $radar, $ods, $geo, $seo, $date, (int)$issueId]
			);
		} else {
			$dao->update(
				'INSERT INTO issue_ai_analysis 
				 (issue_id, editorial_draft, radar_analysis, ods_analysis, geo_analysis, global_seo_description, date_generated) 
				 VALUES (?, ?, ?, ?, ?, ?, ?)',
				[(int)$issueId, $editorial, $radar, $ods, $geo, $seo, $date]
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
		
		if (curl_errno($ch)) return "CURL ERROR: " . curl_error($ch);
		curl_close($ch);

		$json = json_decode($response, true);
		if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
			return $json['candidates'][0]['content']['parts'][0]['text'];
		}
		
		if (isset($json['error']['message'])) {
			return "API ERROR: " . $json['error']['message'];
		}
		
		return "UNKNOWN ERROR: Respuesta vacía o malformada.";
	}
}
