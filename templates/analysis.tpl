{**
 * plugins/generic/issueSpotlight/templates/analysis.tpl
 *
 * Copyright (c) 2026 UPC
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Analysis results view with TOC inclusion.
 *}
<style>
    /* Ocultar acciones de edición/borrado en el grid de TOC dentro de nuestra modal */
    #issueSpotlightAnalysis .pkp_linkaction_edit,
    #issueSpotlightAnalysis .pkp_linkaction_delete,
    #issueSpotlightAnalysis .pkp_linkaction_removeArticle,
    #issueSpotlightAnalysis .pkp_linkaction_setAccessStatus {
        display: none !important;
    }
    #issueSpotlightAnalysis .pkp_controllers_grid .grid_controls {
        display: none !important;
    }
</style>

<div id="issueSpotlightAnalysis">
	<h3>{translate key="plugins.generic.issueSpotlight.displayName"} - {$issue->getIssueIdentification()}</h3>
	
    <div id="tocPreviewContainer" style="margin-bottom: 20px; border: 1px solid #ddd; padding: 10px; background: #fff;">
        <h4>{translate key="editor.issues.tableOfContents"}</h4>
        {capture assign=issueTocGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="grid.toc.TocGridHandler" op="fetchGrid" issueId=$issueId escape=false}{/capture}
        {load_url_in_div id="issueTocGridContainer" url=$issueTocGridUrl}
    </div>

	<div id="spotlightControls" style="text-align: center; margin-bottom: 20px;">
		<button class="pkp_button" id="startAnalysisBtn" style="background: #007bff; color: white; padding: 10px 20px;">
            <span class="fa fa-magic"></span> {translate key="plugins.generic.issueSpotlight.startAnalysis"}
        </button>
	</div>

	<div id="spotlightLoader" style="display:none; margin: 20px 0; text-align: center;">
		<span class="pkp_spinner"></span> {translate key="plugins.generic.issueSpotlight.analyzing"}...
	</div>

	<div id="spotlightResults" style="display:none; margin-top: 20px;">
		<div class="pkp_controllers_tab">
			<ul>
				<li><a href="#radarTab">Radar de Tendencias</a></li>
				<li><a href="#editorialTab">Borrador Editorial</a></li>
				<li><a href="#expertsTab">Sugerencias Expertos</a></li>
			</ul>
			<div id="radarTab">
				<div id="radarChartContainer" style="min-height: 200px; border: 1px solid #ddd; padding: 15px; background: #f9f9f9;">
					<div id="radarData"></div>
				</div>
			</div>
			<div id="editorialTab">
				<div id="editorialContent" style="padding: 15px; border: 1px solid #ddd; background: #fff; line-height: 1.6;"></div>
			</div>
			<div id="expertsTab">
				<div id="expertsContent" style="padding: 15px; border: 1px solid #ddd; background: #fff;"></div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(function() {ldelim}
		// Initialize tabs
		$('#issueSpotlightAnalysis .pkp_controllers_tab').tabs();

		$('#startAnalysisBtn').click(function() {ldelim}
			var $btn = $(this);
			$btn.hide();
            $('#tocPreviewContainer').slideUp();
			$('#spotlightLoader').show();
			$('#spotlightResults').hide();
			
			$.ajax({ldelim}
				url: '{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.plugins.SettingsPluginGridHandler" op="manage" verb="runAnalysis" issueId=$issueId plugin="issueSpotlight" category="generic"}',
				dataType: 'json',
				success: function(response) {ldelim}
					$('#spotlightLoader').hide();
					if (response && response.status === true) {ldelim}
						var data = response.content;
						$('#spotlightResults').show();
						
						// Fill Editorial
						$('#editorialContent').html(data.editorial);
						
						// Fill Experts
						$('#expertsContent').html(data.experts);
						
						// Fill Radar Data
						var radarHtml = '<ul style="list-style: none; padding: 0;">';
						if (data.radar && Array.isArray(data.radar) && data.radar.length > 0) {ldelim}
							data.radar.forEach(function(item) {ldelim}
								var color = '#999';
								var status = item.status || '';
								if (status.toLowerCase().indexOf('novedoso') !== -1) color = '#2c832c';
								else if (status.toLowerCase().indexOf('auge') !== -1) color = '#d9534f';
								else if (status.toLowerCase().indexOf('estable') !== -1) color = '#5bc0de';
								
								radarHtml += '<li style="margin-bottom: 10px; padding: 8px; border-left: 4px solid ' + color + '; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">' +
											 '<span style="color:' + color + '; font-weight:bold; text-transform: uppercase; font-size: 0.8em;">' + status + '</span><br>' +
											 '<strong>' + item.tag + '</strong> (' + item.count + ')</li>';
							{rdelim});
						{rdelim} else {ldelim}
							radarHtml += '<li>Análisis completado (puedes ver el borrador en la pestaña editorial).</li>';
						{rdelim}
						radarHtml += '</ul>';
						$('#radarData').html(radarHtml);
						
					{rdelim} else {ldelim}
						var errorMsg = (response && response.content) ? response.content : 'Error en la respuesta del servidor.';
						alert('Error: ' + errorMsg);
						$btn.show();
                        $('#tocPreviewContainer').show();
					{rdelim}
				{rdelim},
				error: function(xhr, status, error) {ldelim}
					$('#spotlightLoader').hide();
					alert('Error de red: ' + error);
					$btn.show();
                    $('#tocPreviewContainer').show();
				{rdelim}
			{rdelim});
		{rdelim});
	{rdelim});
</script>
