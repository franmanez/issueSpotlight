{**
 * plugins/generic/issueSpotlight/templates/settingsForm.tpl
 *
 * Copyright (c) 2026 UPC - Universitat Politècnica de Catalunya
 * Author: Fran Máñez <fran.upc@gmail.com>, <francisco.manez@upc.edu>
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Plugin settings form for Google Gemini API key configuration.
 *}
<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#issueSpotlightSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="issueSpotlightSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="issueSpotlightSettingsFormNotification"}

	<div id="issueSpotlightSettings">
		{fbvFormArea id="settings" title="plugins.generic.issueSpotlight.settings"}
			{fbvFormSection}
				{fbvElement type="text" id="apiKey" value=$apiKey label="plugins.generic.issueSpotlight.settings.apiKey"}
                <br>
				<span class="instruction">
                    {translate key="plugins.generic.issueSpotlight.settings.apiKeyDescription"}
                </span>
			{/fbvFormSection}
		{/fbvFormArea}

		{fbvFormButtons id="issueSpotlightSettingsFormButtons" submitText="common.save" hideCancel=true}
	</div>
</form>
