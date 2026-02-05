{**
 * templates/frontend/analysis_view.tpl
 * Public view for AI Analysis
 *}
{include file="frontend/components/header.tpl" pageTitle="plugins.generic.issueSpotlight.analysisTitle"}

<div class="page page_issue_spotlight">
	{include file="frontend/components/breadcrumbs_issue.tpl" currentTitle=$issue->getIssueIdentification()}
	
	<h1>{translate key="plugins.generic.issueSpotlight.analysisTitle"}: {$issue->getIssueIdentification()}</h1>
	
	<div class="description" style="margin-bottom: 30px; border-bottom: 1px solid #ddd; padding-bottom: 20px;">
		<p>{translate key="plugins.generic.issueSpotlight.description"}</p>
	</div>

	{* TAB NAVIGATION *}
	<ul class="pkp_tabs_list" style="border-bottom: 1px solid #ddd; margin-bottom: 20px; padding: 0;">
		<li style="display:inline-block; margin-right: 10px;">
			<a href="#" onclick="switchTab('editorial'); return false;" class="pkp_button pkp_button_primary" id="tab_btn_editorial">Editorial AI</a>
		</li>
		<li style="display:inline-block; margin-right: 10px;">
			<a href="#" onclick="switchTab('radar'); return false;" class="pkp_button" id="tab_btn_radar">Matriz de Innovación</a>
		</li>
		<li style="display:inline-block;">
			<a href="#" onclick="switchTab('ods'); return false;" class="pkp_button" id="tab_btn_ods">Impacto ODS</a>
		</li>
	</ul>

	{* EDITORIAL CONTENT *}
	<div id="tab_content_editorial" class="analysis_tab_content">
		<div class="pkp_notification" style="background:#f5faff; border-left:4px solid #006798; padding:15px; margin-bottom:20px;">
			<strong> Nota:</strong> Este editorial ha sido generado automáticamente por Inteligencia Artificial basándose en los artículos del número.
		</div>
		<div class="content_body" style="font-size: 1.1em; line-height: 1.8;">
			{$editorialDraft|strip_unsafe_html}
		</div>
	</div>

	{* RADAR CONTENT (INNOVATION MATRIX) *}
	<div id="tab_content_radar" class="analysis_tab_content" style="display:none;">
		
		{* MATRIX LEGEND *}
		<div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 30px;">
			<h4 style="margin-top:0;">¿Cómo interpretar esta matriz?</h4>
			<p style="font-size: 0.9em; color: #555;">
				Esta visualización posiciona los conceptos clave del número en función de dos dimensiones evaluadas por IA:
			</p>
			<ul style="font-size: 0.9em; color: #555; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; list-style: none; padding: 0;">
				<li style="background: #f9f9f9; padding: 10px; border-radius: 4px;">
					<strong>Eje X: Madurez Académica</strong><br>
					Desde temas <em>Emergentes</em> (izquierda) hasta conceptos <em>Consolidados</em> (derecha).
				</li>
				<li style="background: #f9f9f9; padding: 10px; border-radius: 4px;">
					<strong>Eje Y: Impacto Potencial</strong><br>
					Desde temas de <em>Nicho</em> (abajo) hasta de alto <em>Interés Transversal</em> (arriba).
				</li>
			</ul>
			<div style="margin-top: 15px; font-size: 0.85em; display: flex; gap: 10px; flex-wrap: wrap;">
				<span style="padding: 2px 8px; background: #e6fffa; color: #2c832c; border: 1px solid #2c832c; border-radius: 10px;">⭐ Apuestas Futuras (Emergente + Impacto)</span>
				<span style="padding: 2px 8px; background: #f0f9ff; color: #006798; border: 1px solid #006798; border-radius: 10px;">💎 Clásicos de Valor (Consolidado + Impacto)</span>
			</div>
		</div>

		{* CHART SECTION *}
		<div style="margin-bottom: 40px; padding: 20px; background: #fafafa; border-radius: 8px; border: 1px solid #eee;">
			<h3 style="text-align:center; color:#333; margin-bottom: 20px;">Matriz de Impacto vs. Madurez Académica</h3>
			<div style="position: relative; height: 600px; width: 100%;">
				<div id="radarBubbleChart" style="width:100%; height:100%;"></div>
			</div>
		</div>

	</div>

	{* ODS CONTENT (DONUT + ICONS) *}
	<div id="tab_content_ods" class="analysis_tab_content" style="display:none;">
		<div style="background: #fafafa; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
			<h3 style="text-align:center; color:#333; margin-bottom:10px;">Contribución a los Objetivos de Desarrollo Sostenible</h3>
			<p style="text-align:center; color:#666; font-size:0.9em; margin-bottom: 30px;">
				Porcentaje de alineación temática del número con la Agenda 2030 de la ONU.
			</p>
			
			{* 1. DONUT CHART *}
			<div style="position: relative; height: 350px; width: 100%; margin-bottom: 40px;">
				<div id="odsDonutChart" style="width:100%; height:100%;"></div>
			</div>

			{* 2. ODS CARDS GRID *}
			<h4 style="border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; color: #444;">Detalle de Objetivos Impactados</h4>
			
			{* Container for dynamic cards inserted via JS *}
			<div id="odsCardsContainer" style="display: grid; grid-template-columns: 1fr; gap: 20px;">
				{* JS will inject content here *}
			</div>

		</div>
	</div>

	<div style="margin-top: 40px; text-align: center;">
		<a href="{url page="issue" op="view" path=$issueId}" class="pkp_button">Volver al Número</a>
	</div>
</div>

{* EXTERNAL LIBRARIES *}
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-more.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script>
	// Base URL for images
	// We need to construct the plugin path. Assuming standard structure:
	const pluginImgPath = '{$baseUrl}/plugins/generic/issueSpotlight/images/ods/';

{literal}
	// Tab Switching Logic
	function switchTab(tabName) {
		// Hide all
		document.getElementById('tab_content_editorial').style.display = 'none';
		document.getElementById('tab_content_radar').style.display = 'none';
		document.getElementById('tab_content_ods').style.display = 'none';
		
		// Reset buttons
		document.getElementById('tab_btn_editorial').className = 'pkp_button';
		document.getElementById('tab_btn_radar').className = 'pkp_button';
		document.getElementById('tab_btn_ods').className = 'pkp_button';
		
		// Show active
		document.getElementById('tab_content_' + tabName).style.display = 'block';
		document.getElementById('tab_btn_' + tabName).className = 'pkp_button pkp_button_primary';
		
		// Force Highcharts reflow
		if (tabName === 'radar' && window.radarChart) {
			setTimeout(() => window.radarChart.reflow(), 10);
		}
		if (tabName === 'ods' && window.odsChart) {
			setTimeout(() => window.odsChart.reflow(), 10);
		}
	}

	// Highcharts Logic
	document.addEventListener("DOMContentLoaded", function() {
{/literal}
		const rawData = {$thematicClusters|json_encode};
		const odsDataRaw = {$expertSuggestions|json_encode}; // ODS Data
{literal}
		
		// --- RADAR CHART (SCATTER) ---
		const scatterData = rawData.map(item => {
			return {
				name: item.concept || item.tag, 
				x: parseInt(item.maturity || 50),
				y: parseInt(item.impact || 50),
				category: item.category || 'General'
			};
		});

		window.radarChart = Highcharts.chart('radarBubbleChart', {
			chart: {
				type: 'scatter',
				zoomType: 'xy',
				backgroundColor: 'transparent',
				style: { fontFamily: 'inherit' }
			},
			title: { text: '' },
			credits: { enabled: false },
			xAxis: {
				title: { text: 'Grado de Madurez', style: { color: '#666' } },
				min: 0, max: 100,
				gridLineWidth: 1,
				plotLines: [{
					color: '#ccc', width: 2, value: 50, dashStyle: 'Dash', zIndex: 1
				}],
				labels: { format: '{value}%' }
			},
			yAxis: {
				title: { text: 'Impacto Potencial', style: { color: '#666' } },
				min: 0, max: 100,
				gridLineWidth: 1,
				plotLines: [{
					color: '#ccc', width: 2, value: 50, dashStyle: 'Dash', zIndex: 1
				}],
				labels: { format: '{value}%' }
			},
			legend: { enabled: false },
			plotOptions: {
				scatter: {
					marker: {
						radius: 8,
						states: { hover: { enabled: true, lineColor: 'rgb(100,100,100)' } }
					},
					tooltip: {
						headerFormat: '<b>{point.point.name}</b><br>',
						pointFormat: '{point.category}<br>Madurez: {point.x}<br>Impacto: {point.y}'
					},
					dataLabels: {
						enabled: true,
						format: '{point.name}',
						style: { textOutline: 'none', color: '#333' },
						y: -10 
					}
				}
			},
			series: [{
				name: 'Conceptos',
				color: 'rgba(0, 103, 152, 0.7)', 
				data: scatterData
			}],
			annotations: [{
				labels: [{
					point: { x: 5, y: 95, xAxis: 0, yAxis: 0 },
					text: '⭐ APUESTAS FUTURAS',
					backgroundColor: 'rgba(255,255,255,0.7)', style: {color: '#d9534f', fontSize: '10px'}
				}, {
					point: { x: 95, y: 95, xAxis: 0, yAxis: 0 },
					text: '💎 CLÁSICOS DE VALOR',
					backgroundColor: 'rgba(255,255,255,0.7)', style: {color: '#2c832c', fontSize: '10px'}
				}, {
					point: { x: 5, y: 5, xAxis: 0, yAxis: 0 },
					text: '🧪 EXPERIMENTAL',
					backgroundColor: 'rgba(255,255,255,0.7)', style: {color: '#777', fontSize: '10px'}
				}, {
					point: { x: 95, y: 5, xAxis: 0, yAxis: 0 },
					text: '📚 FUNDAMENTOS',
					backgroundColor: 'rgba(255,255,255,0.7)', style: {color: '#777', fontSize: '10px'}
				}]
			}]
		});

		// --- ODS LOGIC: DONUT + CARDS ---
		let odsData = [];
		try {
			odsData = (typeof odsDataRaw === 'string') ? JSON.parse(odsDataRaw) : odsDataRaw;
		} catch(e) { console.error("Error parsing ODS JSON", e); }

		if(odsData && odsData.length > 0) {
			// 1. Prepare Chart Data
			const donutData = odsData.map(item => {
				return {
					name: "ODS " + item.ods + " - " + item.name, // "ODS 4 - Educación..."
					y: parseInt(item.percentage),
					color: item.color || '#333',
					customOdsId: item.ods // Guardamos el ID por si acaso
				};
			});

			// 2. Render Donut
			window.odsChart = Highcharts.chart('odsDonutChart', {
				chart: { type: 'pie', backgroundColor: 'transparent' },
				title: { text: '' },
				tooltip: {
					pointFormat: '<b>{point.percentage:.1f}%</b>' // Añadido unidad %
				},
				plotOptions: {
					pie: {
						innerSize: '50%', // Makes it a Donut
						allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: true,
							format: '<b>{point.name}</b>: {point.percentage:.1f} %',
							style: { width: '150px' } // Limitar ancho para que haga wrap si es muy largo
						}
					}
				},
				series: [{
					name: 'Contribución',
					colorByPoint: true,
					data: donutData
				}],
				credits: { enabled: false }
			});

			// 3. Render Cards
			const container = document.getElementById('odsCardsContainer');
			let cardsHtml = '';
			
			odsData.forEach(item => {
				// Fallback color if not provided
				const color = item.color || '#006798';
				
				cardsHtml += `
					<div style="display: flex; align-items: center; background: white; border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
						<!-- ICON -->
						<div style="flex: 0 0 80px; margin-right: 15px;">
							<img src="${pluginImgPath}${item.ods}.png" alt="ODS ${item.ods}" style="width: 100%; border-radius: 4px;">
						</div>
						
						<!-- INFO -->
						<div style="flex: 1;">
							<h4 style="margin: 0 0 5px 0; color: #333; font-size: 1em;">${item.name}</h4>
							
							<!-- Reasoning -->
							<p style="font-size: 0.85em; color: #666; font-style: italic; margin: 0 0 10px 0; line-height: 1.4;">
								"${item.reasoning || 'Análisis de impacto basado en contenidos.'}"
							</p>

							<div style="font-size: 0.85em; color: #777; margin-bottom: 5px;">
								Contribución: <strong>${item.percentage}%</strong>
							</div>
							
							<!-- Progress Bar -->
							<div style="width: 100%; height: 6px; background: #eee; border-radius: 3px; overflow: hidden;">
								<div style="width: ${item.percentage}%; height: 100%; background: ${color};"></div>
							</div>
						</div>
					</div>
				`;
			});
			container.innerHTML = cardsHtml;

		} else {
			document.getElementById('odsDonutChart').innerHTML = "<p style='text-align:center; padding-top:50px;'>No hay datos ODS disponibles a analizar.</p>";
		}
	});
{/literal}
</script>

{include file="frontend/components/footer.tpl"}
