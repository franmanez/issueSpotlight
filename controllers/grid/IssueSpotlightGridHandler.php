<?php
/**
 * @file plugins/generic/issueSpotlight/controllers/grid/IssueSpotlightGridHandler.php
 *
 * Copyright (c) 2026 UPC - Universitat Politècnica de Catalunya
 * Author: Fran Máñez <fran.upc@gmail.com>, <francisco.manez@upc.edu>
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class IssueSpotlightGridHandler
 * @ingroup plugins_generic_issueSpotlight
 *
 * @brief Backend handler for AI analysis execution. Manages API calls to Google Gemini,
 *        processes multilingual prompts, and persists analysis results to the database.
 */

namespace APP\plugins\generic\issueSpotlight\controllers\grid;

use APP\core\Application;
use APP\facades\Repo;
use PKP\controllers\grid\GridHandler;
use PKP\core\JSONMessage;
use PKP\db\DAO;
use PKP\db\DAORegistry;
use PKP\security\authorization\ContextAccessPolicy;
use PKP\security\Role;
use Illuminate\Support\Facades\DB;

class IssueSpotlightGridHandler extends GridHandler {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			[Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
			['analysis', 'runAnalysisDummy', 'runAnalysisReal']
		);
	}

	/**
	 * @copydoc PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		$this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));

		import('classes.security.authorization.OjsIssueRequiredPolicy');
		$this->addPolicy(new \OjsIssueRequiredPolicy($request, $args));

		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Handle analysis request (View)
	 */
	function analysis($args, $request) {
		$issue = $this->getAuthorizedContextObject(\APP\core\Application::ASSOC_TYPE_ISSUE);
		$context = $request->getContext();
		$router = $request->getRouter();
		
		// URLs para acciones
		$realUrl = $router->url($request, null, 'plugins.generic.issueSpotlight.controllers.grid.IssueSpotlightGridHandler', 'runAnalysisReal', null, array('issueId' => $issue->getId()));

		// Comprobar si ya existe un análisis
		$analysisExists = DB::table('issue_ai_analysis')
			->where('issue_id', (int) $issue->getId())
			->exists();

		// Obtener artículos y autores
		$submissions = Repo::submission()->getCollector()
			->filterByContextIds([$context->getId()])
			->filterByIssueIds([$issue->getId()])
			->getMany();
		
		$articlesRows = '';
		$authorsRows = '';
		$articleCount = 0;
		$uniqueAuthors = [];

		foreach ($submissions as $submission) {
			$publication = $submission->getCurrentPublication();
			$title = $publication ? $publication->getLocalizedTitle() : $submission->getLocalizedTitle();
			$titleStr = $title ? htmlspecialchars($title) : '<em>(Sin título)</em>';
			
			$articlesRows .= '<tr><td style="padding:6px 10px; border-bottom:1px solid #eee; font-size: 0.9em;">' . $titleStr . '</td></tr>';
			
			if ($publication) {
				$authors = $publication->getData('authors');
				if ($authors) {
					foreach ($authors as $author) {
						$email = $author->getEmail();
						if (!in_array($email, $uniqueAuthors)) {
							$uniqueAuthors[] = $email;
						}
						$aff = $author->getLocalizedAffiliation();
						$affStr = $aff ? htmlspecialchars($aff) : '<span style="color:#999">-</span>';
						$authorsRows .= '<tr>
							<td style="padding:6px 10px; border-bottom:1px solid #eee; font-size: 0.9em; width: 40%;"><strong>' . htmlspecialchars($author->getFullName()) . '</strong></td>
							<td style="padding:6px 10px; border-bottom:1px solid #eee; font-size: 0.9em; color:#666;">' . $affStr . '</td>
						</tr>';
					}
				}
			}
			$articleCount++;
		}
		$totalAuthors = count($uniqueAuthors);

		// Tablas (ahora ocultas por defecto)
		$articlesTable = '<table class="pkp_table" style="width:100%; border-collapse: collapse;"><tbody>' . $articlesRows . '</tbody></table>';
		$authorsTable = '<table class="pkp_table" style="width:100%; border-collapse: collapse;"><tbody>' . $authorsRows . '</tbody></table>';

		// Script JS unificado
		$jsScript = "
		<script>
			function toggleDetails() {
				var details = document.getElementById('details_container');
				var btn = document.getElementById('btn_toggle_details');
				if (details.style.display === 'none') {
					details.style.display = 'block';
					btn.innerHTML = 'Ocultar detalles ▲';
				} else {
					details.style.display = 'none';
					btn.innerHTML = 'Ver detalles del contenido ▼';
				}
			}

			function switchAnalysisTab(tabName) {
				document.getElementById('tab_articles').style.display = 'none';
				document.getElementById('tab_authors').style.display = 'none';
				document.getElementById('btn_tab_articles').classList.remove('pkp_button_primary');
				document.getElementById('btn_tab_authors').classList.remove('pkp_button_primary');
				document.getElementById('tab_' + tabName).style.display = 'block';
				document.getElementById('btn_tab_' + tabName).classList.add('pkp_button_primary');
			}

			function triggerAnalysis() {
				var btnId = 'btnRunReal';
				var url = '$realUrl';
				var btn = document.getElementById(btnId);
				var statusDiv = document.getElementById('analysisStatus');
				
				btn.disabled = true;
				btn.innerHTML = 'Analizando...';
				statusDiv.innerHTML = '<div class=\"pkp_spinner\"></div> Procesando número con Gemini IA. Esto puede tardar unos segundos...';

				$.ajax({
					url: url,
					dataType: 'json',
					success: function(data) {
						btn.disabled = false;
						btn.innerHTML = 'REINICIAR ANÁLISIS CON IA';
						if(data.status) {
							statusDiv.innerHTML = '<div class=\"pkp_notification\" style=\"margin-top:10px; padding:15px; background:#e6fffa; border:1px solid #2c832c; color:#2c832c;\">' + data.content + '</div>';
						} else {
							statusDiv.innerHTML = '<div class=\"pkp_notification\" style=\"margin-top:10px; padding:15px; background:#ffe6e6; border:1px solid #d9534f; color:#d9534f;\"><strong>Error:</strong> ' + data.content + '</div>';
						}
					},
					error: function(xhr, textStatus, errorThrown) {
						btn.disabled = false;
						btn.innerHTML = 'INTENTAR DE NUEVO';
						statusDiv.innerHTML = '<div class=\"pkp_notification\" style=\"margin-top:10px; padding:15px; background:#ffe6e6; border:1px solid #d9534f; color:#d9534f;\"><strong>Error de red:</strong> ' + errorThrown + '</div>';
					}
				});
			}
		</script>";

		// Status UI
		$statusHtml = $analysisExists 
			? '<div style="background: #f0fdf4; color: #166534; padding: 10px; border-radius: 6px; border: 1px solid #bbf7d0; margin-bottom: 20px; font-size: 0.9em; display: flex; align-items: center; gap: 8px;">' .
			  '<span style="font-size: 1.2em;">✅</span> Este número ya cuenta con un análisis de IA generado.</div>'
			: '<div style="background: #fffbeb; color: #92400e; padding: 10px; border-radius: 6px; border: 1px solid #fde68a; margin-bottom: 20px; font-size: 0.9em; display: flex; align-items: center; gap: 8px;">' .
			  '<span style="font-size: 1.2em;">⏳</span> Este número está pendiente de análisis inicial.</div>';

		// Main Content
		$content = '<div style="padding: 10px 20px; font-family: sans-serif;">' .
			'<h3 style="margin-bottom:10px; color:#006798;">' . $issue->getIssueIdentification() . '</h3>' .
			$statusHtml .
			
			'<div style="background: #f9fafb; border: 1px solid #eee; border-radius: 8px; padding: 20px; margin-bottom: 20px;">' .
				'<h4 style="margin: 0 0 15px 0; font-size: 1rem; color: #374151;">Resumen de contenidos interactuables:</h4>' .
				'<div style="display: flex; gap: 40px; margin-bottom: 15px;">' .
					'<div><span style="font-size: 2rem; display: block; margin-bottom: 5px;">📄</span> <strong>' . $articleCount . '</strong> Artículos</div>' .
					'<div><span style="font-size: 2rem; display: block; margin-bottom: 5px;">👥</span> <strong>' . $totalAuthors . '</strong> Autores únicos</div>' .
				'</div>' .
				'<p style="font-size: 0.9rem; color: #6b7280; line-height: 1.5; margin: 0;">' .
					__('plugins.generic.issueSpotlight.analysisProcessDescription') .
				'</p>' .
			'</div>' .
 
			'<div style="text-align: right; margin-bottom: 10px;">' .
				'<a href="#" id="btn_toggle_details" onclick="toggleDetails(); return false;" style="color: #006798; font-size: 0.85rem; text-decoration: none; font-weight: 600;">Ver detalles del contenido ▼</a>' .
			'</div>' .
			
			'<div id="details_container" style="display: none; border: 1px solid #ddd; border-radius: 4px; background:#fff; margin-bottom: 20px;">' .
				'<div style="padding: 10px; background: #f8f9fa; border-bottom: 1px solid #ddd; display: flex; gap: 5px;">' .
					'<button id="btn_tab_articles" class="pkp_button pkp_button_primary" style="font-size:0.8rem;" onclick="switchAnalysisTab(\'articles\')">Artículos</button> ' .
					'<button id="btn_tab_authors" class="pkp_button" style="font-size:0.8rem;" onclick="switchAnalysisTab(\'authors\')">Autores</button>' .
				'</div>' .
				'<div id="tab_articles" style="max-height: 200px; overflow-y: auto;">' . $articlesTable . '</div>' .
				'<div id="tab_authors" style="display:none; max-height: 200px; overflow-y: auto;">' . $authorsTable . '</div>' .
			'</div>' .
 
			'<div style="margin-top: 25px; text-align: center; border-top: 1px solid #eee; padding-top: 25px;">' .
				'<button id="btnRunReal" class="pkp_button pkp_button_primary" style="padding: 10px 25px; font-weight: 600; font-size: 0.95rem;" onclick="triggerAnalysis()">' . 
					($analysisExists ? 'VOLVER A GENERAR ANÁLISIS' : 'INICIAR ANÁLISIS CON IA') . 
				'</button>' .
				'<div id="analysisStatus" style="margin-top: 20px;"></div>' .
			'</div>' .
			$jsScript .
			'</div>';
 
		// Limpiamos cualquier buffer de salida (como Notices de OJS) para asegurar JSON válido
		if (ob_get_length()) ob_clean();
		return new JSONMessage(true, $content);
	}

	/**
	 * Handle dummy analysis execution
	 */
	function runAnalysisDummy($args, $request) {
		$issue = $this->getAuthorizedContextObject(\APP\core\Application::ASSOC_TYPE_ISSUE);
		$contextId = $request->getContext()->getId();
		
		$submissions = Repo::submission()->getCollector()
			->filterByContextIds([$contextId])
			->filterByIssueIds([$issue->getId()])
			->getMany();

		$titles = [];
		foreach ($submissions as $s) {
			$t = $s->getLocalizedTitle() ?: ($s->getCurrentPublication() ? $s->getCurrentPublication()->getLocalizedTitle() : '');
			if ($t) $titles[] = $t;
		}
		$randomTitle = !empty($titles) ? $titles[array_rand($titles)] : "Sin artículos";
		
		$this->_persistAnalysisData(
			$issue->getId(), 
			"<h3>Editorial Dummy</h3><p>Prueba con: $randomTitle</p>", 
			json_encode([['tag' => 'DummyTag', 'count' => 10, 'trend' => 'stable']]), 
			json_encode([['ods' => 4, 'name' => 'Educación', 'percentage' => 100, 'color' => '#C5192D', 'reasoning' => 'Test']]),
			json_encode(['institutions' => []])
		);
 
		if (ob_get_length()) ob_clean();
		return new JSONMessage(true, "<strong>Test OK!</strong> Datos dummy guardados usando: <em>$randomTitle</em>");
	}

	/**
	 * Handle REAL analysis execution with Gemini
	 */
	function runAnalysisReal($args, $request) {
		$issue = $this->getAuthorizedContextObject(\APP\core\Application::ASSOC_TYPE_ISSUE);
		$context = $request->getContext();
		$contextId = $context->getId();

		// 1. Obtener API Key
		$apiKey = $context->getData('issueSpotlightApiKey'); // Asumiendo que se guardó así o vía plugin settings
        if (!$apiKey) {
            $pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
            $apiKey = $pluginSettingsDao->getSetting($contextId, 'issuespotlightplugin', 'apiKey');
        }

		if (!$apiKey) {
			if (ob_get_length()) ob_clean();
			return new JSONMessage(false, "Falta la API Key de Gemini en los ajustes del plugin.");
		}

		// 2. Construir Payload
		$submissions = Repo::submission()->getCollector()
			->filterByContextIds([$contextId])
			->filterByIssueIds([$issue->getId()])
			->getMany();

		$payload = "";
		foreach ($submissions as $submission) {
			$pub = $submission->getCurrentPublication();
			$title = $submission->getLocalizedTitle() ?: ($pub ? $pub->getLocalizedTitle() : '');
			$abstract = $pub ? strip_tags($pub->getLocalizedData('abstract')) : '';
			if ($title) $payload .= "TÍTULO: $title | RESUMEN: $abstract\n\n";
		}

		if (empty(trim($payload))) {
			if (ob_get_length()) ob_clean();
			return new JSONMessage(false, "El número no tiene artículos con contenido válido para analizar.");
		}

		// Use only the primary locale for the analysis to save tokens and prevent truncations
		$primaryLocale = $context->getPrimaryLocale();

		// 3. Llamadas a Gemini (Secuenciales)
		// Prompt 2: Radar de Innovación (Tags + Count + Trend) en multidioma (Refined for specificity)
		$promptRadar = "Analiza la siguiente lista de Títulos y Resúmenes de artículos académicos de un número de revista.
        Extrae los conceptos tecnológicos, metodológicos o teóricos más relevantes.
        
        REGLAS DE ESPECIFICIDAD:
        1. BIGRAMAS Y TRIGRAMAS: Evita términos de una sola palabra (ej. 'Diseño', 'Educación'). Prioriza conceptos compuestos de 2 o 3 palabras que definan el nicho (ej. 'Diseño Especulativo', 'Educación Híbrida'). Es decir, que se centre en el Problema-Solución o Método-Contexto. Ej: \"Cada concepto debe representar una intersección clara. En lugar de Sostenibilidad, usa Sostenibilidad Urbana o Materiales Bio-basados.\"
        2. TÉRMINOS PROHIBIDOS: No utilices conceptos genéricos como: Tecnología, Innovación, Análisis, Diseño, Desarrollo, Investigación, Estudio, Ciencia, Sistema, Método, Aplicación, Resultado, Datos.
        3. NORMALIZACIÓN: Une sinónimos bajo el término más técnico.
        
        Para cada concepto:
        - Cuenta su frecuencia estimada. Cuenta su frecuencia de aparición en los artículos (estimada).
        - Determina su tendencia ('trend'): 'new', 'rising', 'stable' según su impacto en el texto.
		 - 'new': Si se presenta como novedad, innovación o emergente.
		 - 'rising': Si se menciona como tendencia creciente o muy relevante.
		 - 'stable': Si es una tecnología/método base, comparativa o estándar.
        
        Devuelve la respuesta SOLAMENTE en idioma INGLÉS (en_US), ya que es el estándar internacional para conceptos científicos.
        IMPORTANTE: Devuelve SOLAMENTE un array JSON válido con los 30 conceptos más importantes (No menos de 25 y no más de 30) con este formato exacto:
        [{\"tag\": \"Concepto en inglés\", \"count\": 5, \"trend\": \"rising\"}, {\"tag\": \"Concepto en inglés\", \"count\": 3, \"trend\": \"new\"}, ...]
        ";

		$radarContentRaw = $this->_callGemini($apiKey, $promptRadar, $payload);
		if (strpos($radarContentRaw, 'ERROR:') !== false) {
			if (ob_get_length()) ob_clean();
			return new JSONMessage(false, $radarContentRaw);
		}

		$radarContent = preg_replace('/```json|```/', '', $radarContentRaw);
		$radarContent = trim(preg_replace('/^[^\{\[]*|[^\}\]]*$/s', '', $radarContent)); 
		$radarData = json_decode($radarContent, true);
		
		// If the AI returned an object with locale keys instead of a flat array, pick the relevant one
		if (is_array($radarData) && !isset($radarData[0])) {
			if (isset($radarData[$primaryLocale])) {
				$radarData = $radarData[$primaryLocale];
			} else {
				// Fallback: pick the first element if it's an array
				$firstVal = reset($radarData);
				if (is_array($firstVal)) $radarData = $firstVal;
			}
		}
		if (!$radarData || !is_array($radarData)) $radarData = [];
		
		// Prompt Editorial en multidioma
		$promptEditorial = "Actúa como Editor Jefe. Escribe una editorial corta (max 250 palabras) en HTML (usando <p>, <h3>, <ul>). Agrupa los artículos por temáticas comunes y destaca tendencias. Sé profesional y académico.
        REGLA CRÍTICA: NO incluyas ninguna cabecera inicial como 'Editorial', 'Editorial del Editor Jefe' o similares. Empieza directamente con el análisis del contenido.
        Devuelve la respuesta SOLAMENTE en el idioma: {$primaryLocale}.
        IMPORTANTE: Devuelve SOLAMENTE el texto HTML plano (sin envolver en JSON) que representa la editorial. NO respondas con nada más que el HTML generado.
        ";
		$editorialContent = $this->_callGemini($apiKey, $promptEditorial, $payload);
		if (strpos($editorialContent, 'ERROR:') !== false) {
			if (ob_get_length()) ob_clean();
			return new JSONMessage(false, $editorialContent);
		}
		// Limpiamos posible markdown o ruido si el modelo lo añade
		$editorialContent = preg_replace('/```html|```/', '', $editorialContent);
		$editorialContent = trim($editorialContent);

		// Prompt ODS en multidioma
		$promptODS = "Analiza el contenido de los artículos y determina su contribución a los Objetivos de Desarrollo Sostenible (ODS) de la ONU.
		Distribuye un total de 100% entre los ODS más relevantes (mínimo 3, máximo 7).
		Utiliza estrictamente esta tabla de colores oficiales hex para cada ODS:
		ODS 1: #E5243B, ODS 2: #DDA63A, ODS 3: #4C9F38, ODS 4: #C5192D, ODS 5: #FF3A21, 
		ODS 6: #26BDE2, ODS 7: #FCC30B, ODS 8: #A21942, ODS 9: #FD6925, ODS 10: #DD1367, 
		ODS 11: #FD9D24, ODS 12: #BF8B2E, ODS 13: #3F7E44, ODS 14: #0A97D9, ODS 15: #56C02B, 
		ODS 16: #00689D, ODS 17: #19486A.
		
		Devuelve la respuesta SOLAMENTE en el idioma: {$primaryLocale}.
        IMPORTANTE: Devuelve SOLAMENTE un array JSON válido de ODS con este formato exacto:
		[{\"ods\": 4, \"name\": \"Educación\", \"percentage\": 30, \"color\": \"#C5192D\", \"reasoning\": \"...\"}, ...]
        ";

		$odsContentRaw = $this->_callGemini($apiKey, $promptODS, $payload);
		if (strpos($odsContentRaw, 'ERROR:') !== false) {
			if (ob_get_length()) ob_clean();
			return new JSONMessage(false, $odsContentRaw);
		}

		$odsContent = preg_replace('/```json|```/', '', $odsContentRaw);
		$odsContent = trim(preg_replace('/^[^\{\[]*|[^\}\]]*$/s', '', $odsContent));
		$odsData = json_decode($odsContent, true);

		// Handle accidental multilang wrapping
		if (is_array($odsData) && !isset($odsData[0])) {
			if (isset($odsData[$primaryLocale])) {
				$odsData = $odsData[$primaryLocale];
			} else {
				$firstVal = reset($odsData);
				if (is_array($firstVal)) $odsData = $firstVal;
			}
		}
		if (!$odsData || !is_array($odsData)) $odsData = [];

		// --- GEO-ANALYSIS (RESTORING PREVIOUS LOGIC) ---
		$affiliations = [];
		$submissionsGeo = Repo::submission()->getCollector()
			->filterByContextIds([$contextId])
			->filterByIssueIds([$issue->getId()])
			->getMany();

		foreach ($submissionsGeo as $submission) {
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
			// No affiliations found -> Empty Geo Analysis
			$geoJson = ['institutions' => [], 'collaborations' => []];
		} else {
			$affPayload = implode("\n", $uniqueAffiliations);

			$promptGeo = "Actúa como un experto en geología institucional y bibliometría.
			Analiza la siguiente lista de afiliaciones de autores.
			1. Normaliza las instituciones (ej: 'UPC' -> 'Universitat Politècnica de Catalunya').
			2. Para cada institución única, encuentra su Ciudad, País y Coordenadas aproximadas (Latitud y Longitud).
			3. Devuelve SOLAMENTE un JSON con este formato exacto:
			{
				\"institutions\": [
					{\"name\": \"Nombre Real\", \"city\": \"Ciudad\", \"country\": \"País\", \"lat\": 0.0, \"lng\": 0.0, \"count\": número_de_autores}
				]
			}";

			$geoContentRaw = $this->_callGemini($apiKey, $promptGeo, $affPayload);
			if (strpos($geoContentRaw, 'ERROR:') !== false) {
				if (ob_get_length()) ob_clean();
				return new JSONMessage(false, $geoContentRaw);
			}

			$geoContent = preg_replace('/```json|```/', '', $geoContentRaw);
			$geoContent = trim(preg_replace('/^[^\{\[]*|[^\}\]]*$/s', '', $geoContent));
			$geoJson = json_decode($geoContent, true);
			if (!$geoJson) $geoJson = ['institutions' => [], 'collaborations' => []];
		}

		// 4. Guardar en Base de Datos (Estrategia de Registro Único)
		$this->_persistAnalysisData(
			$issue->getId(), 
			$editorialContent, 
			json_encode($radarData, JSON_UNESCAPED_UNICODE), 
			json_encode($odsData, JSON_UNESCAPED_UNICODE), 
			json_encode($geoJson, JSON_UNESCAPED_UNICODE)
		);

		if (ob_get_length()) ob_clean();
		return new JSONMessage(true, __('plugins.generic.issueSpotlight.analysisCompleted', array('issueId' => $issue->getIssueIdentification())));
	}

	/**
	 * Helper: Persistir datos por idioma
	 */
	private function _persistAnalysisData($issueId, $editorial, $radar, $ods, $geo) {
		$dao = new DAO();
		$result = $dao->retrieve(
			'SELECT count(*) as c FROM issue_ai_analysis WHERE issue_id = ?',
			[(int)$issueId]
		);
		$row = (object) $result->current();
		$date = date('Y-m-d H:i:s');

		if ($row && isset($row->c) && $row->c > 0) {
			$dao->update(
				'UPDATE issue_ai_analysis 
				 SET editorial_draft = ?, 
				     radar_analysis = ?, 
				     ods_analysis = ?, 
				     geo_analysis = ?, 
				     date_generated = ? 
				 WHERE issue_id = ?',
				[$editorial, $radar, $ods, $geo, $date, (int)$issueId]
			);
		} else {
			$dao->update(
				'INSERT INTO issue_ai_analysis 
				 (issue_id, editorial_draft, radar_analysis, ods_analysis, geo_analysis, date_generated) 
				 VALUES (?, ?, ?, ?, ?, ?)',
				[(int)$issueId, $editorial, $radar, $ods, $geo, $date]
			);
		}
	}

	/**
	 * Helper: Llamada cURL a Gemini
	 */
	private function _callGemini($apiKey, $systemPrompt, $userContent) {
		$url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash-lite:generateContent?key=" . $apiKey;

		$truncatedContent = function_exists('mb_substr') 
			? mb_substr($userContent, 0, 100000, 'UTF-8') 
			: substr($userContent, 0, 100000);

		$data = [
			"contents" => [
				[
					"parts" => [
						["text" => $systemPrompt . "\n\nDATOS A ANALIZAR:\n" . $truncatedContent]
					]
				]
			]
		];

		$jsonData = json_encode($data);
		if ($jsonData === false) {
			return "ERROR: Fallo al codificar los datos en JSON. Probablemente hay caracteres inválidos.";
		}

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
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
