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
			<a href="#" onclick="switchTab('radar'); return false;" class="pkp_button" id="tab_btn_radar">Radar Temático</a>
		</li>
		<li style="display:inline-block;">
			<a href="#" onclick="switchTab('experts'); return false;" class="pkp_button" id="tab_btn_experts">Expertos</a>
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

	{* RADAR CONTENT *}
	<div id="tab_content_radar" class="analysis_tab_content" style="display:none;">
		<div class="radar_grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
			{foreach from=$thematicClusters item=item}
				{assign var="color" value="#999"}
				{assign var="bg" value="#eee"}
				{if $item.status|lower|strstr:"novedoso"}{assign var="color" value="#2c832c"}{assign var="bg" value="#e6fffa"}{/if}
				{if $item.status|lower|strstr:"auge"}{assign var="color" value="#d9534f"}{assign var="bg" value="#ffe6e6"}{/if}
				{if $item.status|lower|strstr:"estable"}{assign var="color" value="#5bc0de"}{assign var="bg" value="#f0f9ff"}{/if}

				<div class="radar_card" style="border-top: 4px solid {$color}; background: #fff; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
					<span style="display:block; color:{$color}; font-weight:bold; text-transform:uppercase; font-size:0.8em; margin-bottom:10px;">
						{$item.status}
					</span>
					<h3 style="margin:0; font-size:1.2em;">{$item.tag}</h3>
					<div style="margin-top:10px; font-size:0.9em; color:#666;">
						Relevancia: <strong>{$item.count}</strong>
					</div>
				</div>
			{/foreach}
		</div>
	</div>

	{* EXPERTS CONTENT *}
	<div id="tab_content_experts" class="analysis_tab_content" style="display:none;">
		<h3>Sugerencias de Revisores Expertos</h3>
		<div class="content_body">
			{$expertSuggestions|strip_unsafe_html}
		</div>
	</div>

	<div style="margin-top: 40px; text-align: center;">
		<a href="{url page="issue" op="view" path=$issueId}" class="pkp_button">Volver al Número</a>
	</div>
</div>

<script>
	function switchTab(tabName) {
		// Hide all
		document.getElementById('tab_content_editorial').style.display = 'none';
		document.getElementById('tab_content_radar').style.display = 'none';
		document.getElementById('tab_content_experts').style.display = 'none';
		
		// Reset buttons
		document.getElementById('tab_btn_editorial').className = 'pkp_button';
		document.getElementById('tab_btn_radar').className = 'pkp_button';
		document.getElementById('tab_btn_experts').className = 'pkp_button';
		
		// Show active
		document.getElementById('tab_content_' + tabName).style.display = 'block';
		document.getElementById('tab_btn_' + tabName).className = 'pkp_button pkp_button_primary';
	}
</script>

{include file="frontend/components/footer.tpl"}
