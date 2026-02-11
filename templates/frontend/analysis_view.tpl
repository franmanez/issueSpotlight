{**
 * plugins/generic/issueSpotlight/templates/frontend/analysis_view.tpl
 *
 * Copyright (c) 2026 UPC - Universitat Politècnica de Catalunya
 * Author: Fran Máñez <fran.upc@gmail.com>, <francisco.manez@upc.edu>
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Public-facing AI analysis dashboard with interactive visualizations (Innovation Radar,
 * SDG impact charts, institutional maps, and automated editorial synthesis).
 *}
{include file="frontend/components/header.tpl" pageTitle="plugins.generic.issueSpotlight.analysisTitle"}

<div class="page page_issue_spotlight">
	{include file="frontend/components/breadcrumbs_issue.tpl" currentTitle=$issue->getIssueIdentification()}
	
	<h1>{translate key="plugins.generic.issueSpotlight.analysisTitle"}: {$issue->getIssueIdentification()}</h1>
	
	<div class="description" style="margin-bottom: 30px; border-bottom: 1px solid #ddd; padding-bottom: 20px;">
		<p>{translate key="plugins.generic.issueSpotlight.detailedDescription"}</p>
	</div>

	{* MODERN TAB NAVIGATION *}
	<style>
		.analysis_tabs {
			display: flex;
			gap: 12px;
			margin-bottom: 30px;
			padding: 0;
			list-style: none;
		}
		.analysis_tab_btn {
			display: inline-block;
			padding: 12px 24px;
			background: #e9ecef;
			color: #495057;
			text-decoration: none;
			border-radius: 10px;
			font-weight: 600;
			font-size: 0.95rem;
			transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
			border: 1px solid #dee2e6;
		}
		.analysis_tab_btn:hover {
			background: #dee2e6;
			color: #006798;
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(0,0,0,0.05);
			text-decoration: none;
		}
		.analysis_tab_btn.active {
			background: #006798;
			color: #fff;
			border-color: #006798;
			box-shadow: 0 4px 15px rgba(0, 103, 152, 0.25);
		}
	</style>

	<ul class="analysis_tabs">
		<li>
			<a href="#" onclick="switchTab('editorial'); return false;" class="analysis_tab_btn active" id="tab_btn_editorial">{translate key="plugins.generic.issueSpotlight.tab.editorial"}</a>
		</li>
		<li>
			<a href="#" onclick="switchTab('radar'); return false;" class="analysis_tab_btn" id="tab_btn_radar">{translate key="plugins.generic.issueSpotlight.tab.radar"}</a>
		</li>
		<li>
			<a href="#" onclick="switchTab('ods'); return false;" class="analysis_tab_btn" id="tab_btn_ods">{translate key="plugins.generic.issueSpotlight.tab.ods"}</a>
		</li>
		<li>
			<a href="#" onclick="switchTab('geo'); return false;" class="analysis_tab_btn" id="tab_btn_geo">{translate key="plugins.generic.issueSpotlight.tab.geo"}</a>
		</li>
	</ul>

	{* EDITORIAL CONTENT *}
	<div id="tab_content_editorial" class="analysis_tab_content">
        
        <!-- Title -->
        <h4 style="
            color: #006798; 
            margin-bottom: 20px; 
            font-weight: 700; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            border-bottom: 2px solid #eee; 
            padding-bottom: 10px;
        ">{translate key="plugins.generic.issueSpotlight.tab.editorial"}</h4>

        <!-- Explanation Box -->
        <div class="pkp_notification" style="background:#f0f8ff; border: 1px solid #d1e6fa; border-left:4px solid #1E90FF; padding:15px; margin-bottom:20px; border-radius: 4px; font-size: 0.95em; color: #444;">
            <strong style="color: #004d71;">{translate key="plugins.generic.issueSpotlight.explanation.editorial.title"}</strong><br>
            {translate key="plugins.generic.issueSpotlight.explanation.editorial.desc"}
        </div>

		<div class="content_body" style="font-size: 1.1em; line-height: 1.8; color: #333;">
			{$editorialDraft|strip_unsafe_html}
		</div>
	</div>

	{* RADAR CONTENT (INNOVATION RADAR) *}
	<div id="tab_content_radar" class="analysis_tab_content" style="display:none;">
		
        <!-- Title -->
        <h4 style="
            color: #006798; 
            margin-bottom: 20px; 
            font-weight: 700; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            border-bottom: 2px solid #eee; 
            padding-bottom: 10px;
        ">{translate key="plugins.generic.issueSpotlight.tab.radar"}</h4>

        <!-- Explanation Box -->
        <div class="pkp_notification" style="background:#f0f8ff; border: 1px solid #d1e6fa; border-left:4px solid #1E90FF; padding:15px; margin-bottom:20px; border-radius: 4px; font-size: 0.95em; color: #444;">
            <strong style="color: #004d71;">{translate key="plugins.generic.issueSpotlight.explanation.radar.title"}</strong><br>
            {translate key="plugins.generic.issueSpotlight.explanation.radar.desc"}
        </div>

		{* CHART SECTION *}
		<div style="margin-bottom: 40px; padding: 10px 20px 20px 20px; background: #fafafa; border-radius: 8px; border: 1px solid #eee;">
            <div style="position: relative; min-height: 600px; width: 100%;">
				<div id="radarBubbleChart" style="width:100%; height:600px;"></div>
			</div>

            {* STATIC LEGEND *}
            <div class="d-flex flex-wrap justify-content-center gap-4 mt-2 p-3 border-top" style="display: flex; justify-content: center; gap: 20px; border-top: 1px solid #ddd; padding-top: 20px; margin-top: 20px; flex-wrap: wrap;">
                <div class="d-flex align-items-center gap-2 small text-muted" style="display: flex; align-items: center; gap: 8px; color: #666; font-size: 0.9em;">
                    <span class="dot size-indicator" style="width: 10px; height: 10px; border-radius: 50%; border: 1px solid #999; display: inline-block;"></span>
                    <span><b>{translate key="plugins.generic.issueSpotlight.bubble.size"}:</b> {translate key="plugins.generic.issueSpotlight.bubble.volumen"}</span>
                </div>
                
                <div class="interactive-legend-item static" style="display: flex; align-items: center; gap: 8px; font-size: 0.9em;">
                    <span class="dot color-rising" style="width: 10px; height: 10px; border-radius: 50%; background: #FF4757; display: inline-block;"></span>
                    <span>{translate key="plugins.generic.issueSpotlight.bubble.rising"}</span>
                </div>

                <div class="interactive-legend-item static" style="display: flex; align-items: center; gap: 8px; font-size: 0.9em;">
                    <span class="dot color-new" style="width: 10px; height: 10px; border-radius: 50%; background: #2ED573; display: inline-block;"></span>
                    <span>{translate key="plugins.generic.issueSpotlight.bubble.new"}</span>
                </div>

                <div class="interactive-legend-item static" style="display: flex; align-items: center; gap: 8px; font-size: 0.9em;">
                    <span class="dot color-stable" style="width: 10px; height: 10px; border-radius: 50%; background: #1E90FF; display: inline-block;"></span>
                    <span>{translate key="plugins.generic.issueSpotlight.bubble.stable"}</span>
                </div>
            </div>

            {* TRENDING CARDS LIST (3 COLUMNS) *}
            <div class="row" style="margin-top: 40px; margin-left: -10px; margin-right: -10px;">
                <!-- Column 1: STABLE -->
                <div class="col-md-4" style="padding: 0 10px;">
                    <div style="background-color: #f0f8ff; border-top: 3px solid #1E90FF; padding: 10px; border-radius: 4px 4px 0 0; margin-bottom: 15px;">
                        <h5 style="color: #1E90FF; text-transform: uppercase; margin: 0; font-weight: bold;">{translate key="plugins.generic.issueSpotlight.title.stable"}</h5>
                    </div>
                    <div id="radarList_stable" style="display: flex; flex-direction: column; gap: 15px;"></div>
                </div>
                
                <!-- Column 2: RISING -->
                <div class="col-md-4" style="padding: 0 10px;">
                    <div style="background-color: #fff0f1; border-top: 3px solid #FF4757; padding: 10px; border-radius: 4px 4px 0 0; margin-bottom: 15px;">
                        <h5 style="color: #FF4757; text-transform: uppercase; margin: 0; font-weight: bold;">{translate key="plugins.generic.issueSpotlight.title.rising"}</h5>
                    </div>
                    <div id="radarList_rising" style="display: flex; flex-direction: column; gap: 15px;"></div>
                </div>

                <!-- Column 3: NEW -->
                <div class="col-md-4" style="padding: 0 10px;">
                     <div style="background-color: #f0fff4; border-top: 3px solid #2ED573; padding: 10px; border-radius: 4px 4px 0 0; margin-bottom: 15px;">
                        <h5 style="color: #2ED573; text-transform: uppercase; margin: 0; font-weight: bold;">{translate key="plugins.generic.issueSpotlight.title.new"}</h5>
                    </div>
                    <div id="radarList_new" style="display: flex; flex-direction: column; gap: 15px;"></div>
                </div>
            </div>
		</div>

	</div>

	{* ODS CONTENT (DONUT + ICONS) *}
	<div id="tab_content_ods" class="analysis_tab_content" style="display:none;">
            <!-- Title -->
            <h4 style="
                color: #006798; 
                margin-bottom: 20px; 
                font-weight: 700; 
                text-transform: uppercase; 
                letter-spacing: 0.5px; 
                border-bottom: 2px solid #eee; 
                padding-bottom: 10px;
            ">{translate key="plugins.generic.issueSpotlight.tab.ods"}</h4>

            <!-- Explanation Box -->
            <div class="pkp_notification" style="background:#f0f8ff; border: 1px solid #d1e6fa; border-left:4px solid #1E90FF; padding:15px; margin-bottom:20px; border-radius: 4px; font-size: 0.95em; color: #444;">
                <strong style="color: #004d71;">{translate key="plugins.generic.issueSpotlight.explanation.ods.title"}</strong><br>
                {translate key="plugins.generic.issueSpotlight.explanation.ods.desc"}
            </div>

		    <div style="background: #fafafa; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
			{* 1. DONUT CHART *}
			<div style="position: relative; height: 350px; width: 100%; margin-bottom: 40px;">
				<div id="odsDonutChart" style="width:100%; height:100%;"></div>
			</div>

			{* 2. ODS CARDS GRID *}
			<h4 style="border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; color: #444;">{translate key="plugins.generic.issueSpotlight.ods.details"}</h4>
			
			{* Container for dynamic cards inserted via JS *}
			<div id="odsCardsContainer" style="display: grid; grid-template-columns: 1fr; gap: 20px;">
			</div>

		</div>
	</div>

	{* GEO CONTENT (MAP) *}
	<div id="tab_content_geo" class="analysis_tab_content" style="display:none;">
            <!-- Title -->
            <h4 style="
                color: #006798; 
                margin-bottom: 20px; 
                font-weight: 700; 
                text-transform: uppercase; 
                letter-spacing: 0.5px; 
                border-bottom: 2px solid #eee; 
                padding-bottom: 10px;
            ">{translate key="plugins.generic.issueSpotlight.explanation.geo.title"}</h4>
            
            <!-- Explanation Box -->
            <div class="pkp_notification" style="background:#f0f8ff; border: 1px solid #d1e6fa; border-left:4px solid #1E90FF; padding:15px; margin-bottom:20px; border-radius: 4px; font-size: 0.95em; color: #444;">
                <strong style="color: #004d71;">{translate key="plugins.generic.issueSpotlight.explanation.geo.reach"}</strong><br>
                {translate key="plugins.generic.issueSpotlight.explanation.geo.desc"}
            </div>

            <div style="background: #fafafa; padding: 10px; border-radius: 8px; border: 1px solid #eee;">
                <div id="geoAnalysisMap" style="width:100%; height:600px; border-radius: 6px; border: 1px solid #ddd; z-index: 1;"></div>
                
                {* Legend for Map *}
                <div style="display: flex; gap: 20px; justify-content: center; margin-top: 15px; font-size: 0.85em; color: #666; flex-wrap: wrap; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <span style="width: 12px; height: 12px; border-radius: 50%; background: rgba(30, 144, 255, 0.6); display: inline-block; border: 1px solid #1E90FF;"></span> {translate key="plugins.generic.issueSpotlight.geo.institution"}
                    </div>
                </div>

                {* Authors and Affiliations Table *}
                <div style="margin-top: 30px; padding: 10px;">
                    <h4 style="color: #006798; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 8px;">{translate key="plugins.generic.issueSpotlight.geo.authorsTitle"}</h4>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; background: white; border: 1px solid #eee; font-size: 0.9em;">
                            <thead>
                                <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6; text-align: left;">
                                    <th style="padding: 12px; font-weight: 600; color: #495057;">{translate key="plugins.generic.issueSpotlight.geo.author"}</th>
                                    <th style="padding: 12px; font-weight: 600; color: #495057;">{translate key="plugins.generic.issueSpotlight.geo.affiliation"}</th>
                                    <th style="padding: 12px; font-weight: 600; color: #495057;">{translate key="plugins.generic.issueSpotlight.geo.article"}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach from=$authorsData item=author}
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 10px 12px; color: #333;"><strong>{$author.name|escape}</strong></td>
                                        <td style="padding: 10px 12px; color: #666;">
                                            {if $author.affiliation}
                                                {$author.affiliation|escape}
                                            {else}
                                                <span style="color: #ccc; font-style: italic;">{translate key="plugins.generic.issueSpotlight.geo.noAffiliation"}</span>
                                            {/if}
                                        </td>
                                        <td style="padding: 10px 12px; color: #006798; font-size: 0.85em;">{$author.article|escape}</td>
                                    </tr>
                                {foreachelse}
                                    <tr>
                                        <td colspan="3" style="padding: 20px; text-align: center; color: #999;">{translate key="plugins.generic.issueSpotlight.geo.noAuthors"}</td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
	</div>

	<div style="margin-top: 40px; text-align: center;">
		<a href="{url page="issue" op="view" path=$issueId}" class="pkp_button">{translate key="plugins.generic.issueSpotlight.backToIssue"}</a>
	</div>
</div>

{* EXTERNAL LIBRARIES *}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
{* Library for curved/animated lines *}
<script src="https://cdn.jsdelivr.net/npm/leaflet-ant-path@1.3.0/dist/leaflet-ant-path.min.js"></script>

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
		document.getElementById('tab_content_geo').style.display = 'none';
		
		// Reset buttons
		document.querySelectorAll('.analysis_tab_btn').forEach(btn => btn.classList.remove('active'));
		
		// Show active
		document.getElementById('tab_content_' + tabName).style.display = 'block';
		document.getElementById('tab_btn_' + tabName).classList.add('active');
		
		// Force Highcharts reflow
		if (tabName === 'radar' && window.radarChart) {
			setTimeout(() => window.radarChart.reflow(), 10);
		}
		if (tabName === 'ods' && window.odsChart) {
			setTimeout(() => window.odsChart.reflow(), 10);
		}
        // Force Leaflet invalidateSize
        if (tabName === 'geo' && window.geoMap) {
            setTimeout(() => window.geoMap.invalidateSize(), 50);
        }
	}

	// Highcharts Logic
	document.addEventListener("DOMContentLoaded", function() {
{/literal}
		const rawData = {$thematicClusters|json_encode};
		const odsDataRaw = {$expertSuggestions|json_encode}; // ODS Data
        const geoData = {$geoAnalysis|json_encode}; // GEO Data
{literal}
		
		// --- RADAR CHART (PACKED BUBBLE) ---
		// Map new data structure (tag, count, trend)
		let filteredData = [];
		if (Array.isArray(rawData)) {
			filteredData = rawData
				.map(item => {
					// Define colors and display text based on trend
					let color = '#1E90FF'; // stable default
					let trendDisplay = 'Estable';
					
					if (item.trend === 'rising') { 
						color = '#FF4757'; 
						trendDisplay = 'En Alza'; 
					} else if (item.trend === 'new') { 
						color = '#2ED573'; 
						trendDisplay = 'Novedad'; 
					} else if (item.trend === 'stable') {
						color = '#1E90FF';
						trendDisplay = 'Consolidado';
					}

					return {
						name: item.tag || item.concept, // Fallback for old data
						value: parseInt(item.count || item.impact), // Fallback for old data
						trendDisplay: trendDisplay,
						color: color,
						trend: item.trend
					};
				})
				.filter(item => item.value > 0);
		}

		window.radarChart = Highcharts.chart('radarBubbleChart', {
			chart: {
				type: 'packedbubble',
				height: '600px',
				backgroundColor: 'transparent',
				spacing: [0, 0, 0, 0],
				animation: false
			},
			title: { text: '' },
			subtitle: { text: '' },
			tooltip: {
				useHTML: true,
				backgroundColor: 'rgba(255, 255, 255, 0.95)',
				borderRadius: 8,
				borderWidth: 1,
				borderColor: '#ddd',
				pointFormat: `
					<div style="padding: 10px; min-width: 150px; font-family: Arial, sans-serif;">
						<span style="color:{point.color}; font-size: 18px; font-weight: bold">{point.name}</span><br/>
						<div style="margin-top: 5px; font-size: 14px; color: #666">
							<b>Volumen:</b> {point.value}<br/>
							<b>Tendencia:</b> <span style="text-transform: capitalize">{point.trendDisplay}</span>
						</div>
					</div>
				`
			},
			plotOptions: {
				packedbubble: {
					minSize: '40%',
					maxSize: '160%',
					layoutAlgorithm: {
						splitSeries: false,
						gravitationalConstant: 0.005, // Slightly lower to let the larger bubbles find space
						seriesInteraction: true,
						dragBetweenSeries: true,
						parentNodeLimit: true,
						bubblePadding: 12, // Enough distance to be clean, but allowing bigger spheres
						enableSimulation: true,
						maxIterations: 2500,
						initialAnimation: true,
						friction: -0.95
					},
					dataLabels: {
						enabled: true,
						format: '{point.name}',
						style: {
							color: 'white',
							textOutline: '2px rgba(0,0,0,0.5)',
							weight: '600',
							fontSize: '11px',
							fontFamily: 'Arial, sans-serif'
						}
					}
				}
			},
			legend: { enabled: false },
			series: [{
				name: 'Conceptos',
				data: filteredData
			}],
			exporting: { enabled: false },
			credits: { enabled: false }
		});

        // --- RENDER TREND CARDS (3 COLUMNS) ---
        // 1. Get Containers
        const colStable = document.getElementById('radarList_stable');
        const colRising = document.getElementById('radarList_rising');
        const colNew    = document.getElementById('radarList_new');

        if (colStable && colRising && colNew && filteredData.length > 0) {
            
            // 2. Define Styles Wrapper
            const trendStyles = {
                'stable': { color: '#1E90FF', bg: '#1E90FF', label: 'CONSOLIDADO' },
                'rising': { color: '#FF4757', bg: '#FF4757', label: 'EN ALZA' },
                'new':    { color: '#2ED573', bg: '#2ED573', label: 'NUEVA / DISRUPTIVA' }
            };

            // 3. Sort Data by Value Descending
            const sortedData = [...filteredData].sort((a, b) => b.value - a.value);

            // 4. Distribute to Columns
            sortedData.forEach(item => {
                const trendKey = (item.trend || 'stable').toLowerCase();
                // Map 'new' to 'new' column regardless of exact string match if possible, or fallback
                let targetContainer = colStable; // Default
                let style = trendStyles['stable'];

                if (trendKey === 'rising') {
                    targetContainer = colRising;
                    style = trendStyles['rising'];
                } else if (trendKey === 'new') {
                    targetContainer = colNew;
                    style = trendStyles['new'];
                }

                // Lighter background colors for the badges/hover
                const lightBgs = {
                    'stable': '#e6f2ff', 
                    'rising': '#ffe6e9', 
                    'new':    '#e6fffa'
                };
                const badgeBg = lightBgs[trendKey] || '#f5f5f5';

                const cardHtml = `
                    <div style="
                        background: white; 
                        border-left: 5px solid ${style.color}; 
                        border-radius: 4px; 
                        border-top: 1px solid #eee; border-right: 1px solid #eee; border-bottom: 1px solid #eee;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.03); 
                        display:flex; 
                        justify-content: space-between; 
                        align-items: center;
                        padding: 12px 15px;
                        transition: transform 0.2s ease;
                    " onmouseover="this.style.transform='translateX(3px)'" onmouseout="this.style.transform='translateX(0)'">
                        
                        <div style="font-size: 13px; color: #333; line-height: 1.35; font-weight: 600; padding-right: 10px;">
                            ${item.name}
                        </div>
                        
                        <div style="
                            background-color: ${badgeBg}; 
                            color: ${style.color}; 
                            padding: 4px 8px; 
                            border-radius: 6px; 
                            font-size: 11px; 
                            font-weight: 700;
                            white-space: nowrap;
                            min-width: 24px;
                            text-align: center;
                        ">
                            ${item.value}
                        </div>
                    </div>
                `;
                
                // Append to specific container
                targetContainer.insertAdjacentHTML('beforeend', cardHtml);
            });

            // 5. Empty State Message for columns with no items
            [colStable, colRising, colNew].forEach(col => {
                if (col.children.length === 0) {
                    col.innerHTML = '<div style="color: #999; font-style: italic; font-size: 0.9em; text-align: center; padding: 20px;">Sin elementos en esta categoría</div>';
                }
            });
        }

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
							<p style="font-size: 0.95em; color: #444; margin: 0 0 10px 0; line-height: 1.5;">
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

        // --- GEO LOGIC: LEAFLET MAP ---
        if (geoData && geoData.institutions && geoData.institutions.length > 0) {
            
            // Initialize Map with zoom and navigation enabled
            window.geoMap = L.map('geoAnalysisMap', {
                scrollWheelZoom: true,
                dragging: true,
                touchZoom: true,
                doubleClickZoom: true,
                boxZoom: true,
                minZoom: 2
            }).setView([20, 0], 2);

            L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(window.geoMap);

            // Jitter and Coordinate Mapping
            const instCoords = {}; // Store mapped/jittered coords for lines
            const coordUsage = {};

            geoData.institutions.forEach(inst => {
                if (!inst.lat || !inst.lng) return;
                
                let lat = parseFloat(inst.lat);
                let lng = parseFloat(inst.lng);
                const key = `${lat.toFixed(3)},${lng.toFixed(3)}`;
                
                if (coordUsage[key]) {
                    // Apply small spiral jitter if coordinate is reused
                    const angle = coordUsage[key] * (Math.PI / 4);
                    const radius = 0.05 * coordUsage[key];
                    lat += radius * Math.cos(angle);
                    lng += radius * Math.sin(angle);
                    coordUsage[key]++;
                } else {
                    coordUsage[key] = 1;
                }

                instCoords[inst.name] = [lat, lng];

                // Radius based on count
                const radius = Math.min(20, 6 + (inst.count * 1.5));
                const marker = L.circleMarker([lat, lng], {
                    radius: radius,
                    fillColor: "#1E90FF",
                    color: "#004e92",
                    weight: 1.5,
                    opacity: 1,
                    fillOpacity: 0.5
                }).addTo(window.geoMap);

                marker.bindPopup(`
                    <div style="font-family: sans-serif; min-width: 200px;">
                        <strong style="color:#004e92; font-size: 1.1em;">${inst.name}</strong><br>
                        <div style="margin-top: 5px; color: #555;">
                            📍 ${inst.city}, ${inst.country}<br>
                            👥 <strong>${inst.count}</strong> autores en este número
                        </div>
                    </div>
                `);
            });

            // Fit map to markers
            const markerArray = [];
            for (let name in instCoords) {
                markerArray.push(L.marker(instCoords[name]));
            }
            if (markerArray.length > 0) {
                const group = L.featureGroup(markerArray);
                window.geoMap.fitBounds(group.getBounds().pad(0.2));
            }

        } else {
             document.getElementById('geoAnalysisMap').innerHTML = "<p style='text-align:center; padding-top:100px; color:#999;'>No hay datos geográficos disponibles en este análisis.</p>";
        }
	});
{/literal}
</script>

{include file="frontend/components/footer.tpl"}
