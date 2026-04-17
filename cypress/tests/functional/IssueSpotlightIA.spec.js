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
		// 1. Login using PKP core helper (standard for official tests)
		cy.login('admin', 'admin', 'publicknowledge');

		// 2. Go to Website Settings > Plugins
		// Using language-agnostic href selector
		cy.get('a[href*="management/settings/website"]').click();
		cy.get('button[id="plugins-button"]').click();

		// 3. Verify the plugin exists by its name
		// This confirms the plugin is properly registered and visible to the admin
		cy.contains('IssueSpotlight IA').should('exist').scrollIntoView();
	});
})
