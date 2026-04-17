/**
 * @file cypress/tests/functional/IssueSpotlightIA.spec.js
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 * @brief Functional tests for the IssueSpotlight AI plugin (Official Gallery Version).
 */

describe('IssueSpotlight AI plugin tests', function() {
	it('Verifies the plugin is installed and listed in the management interface', function() {
		// 1. Login using PKP core helper (Updated for OJS 3.4 JPK context)
		cy.login('admin', 'admin', 'jpk');

		// 2. Go to Website Settings > Plugins
		// In OJS 3.4, the Sidebar is more structured but URLs are preserved
		cy.get('a[href*="management/settings/website"]', {timeout: 10000}).click();
		
		// The "Plugins" tab uses the ID "plugins-button"
		cy.get('button#plugins-button', {timeout: 10000}).click();

		// 3. Verify the plugin exists by its name
		cy.contains('IssueSpotlight IA').should('exist').scrollIntoView();
	});
})
