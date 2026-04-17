/**
 * @file cypress/tests/functional/IssueSpotlightIA_standalone.spec.js
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 * @brief Standalone functional tests for the IssueSpotlight AI plugin (Ultra-Simplified).
 */

describe('IssueSpotlight AI plugin tests (Standalone)', function () {
	it('Verifies the plugin exists in the management interface', function () {
		// 1. Manual Login (Updated for OJS 3.4)
		// Assuming http://localhost/ojs34 as base
		cy.visit('http://localhost/ojs34/index.php/JPK/login');

		// Use specific selectors for the login form in 3.4
		cy.get('input#username').type('admin');
		cy.get('input#password').type('idpupcB1bl10'); // Assuming 'admin' as default password for testing
		cy.get('button.submit').click();

		// Verify login success - Sidebar exists in 3.4
		cy.get('.app__nav', { timeout: 15000 }).should('exist');

		// 2. Go to Website Settings > Plugins
		// In OJS 3.4, the URL remains similar for compatibility
		cy.get('a[href*="management/settings/website"]').click();

		// The "Plugins" tab in 3.4 uses the ID "plugins-button" for its trigger
		cy.get('button#plugins-button', { timeout: 10000 }).click();

		// 3. Verify the plugin exists by its name
		// Language-agnostic check
		cy.contains('IssueSpotlight IA').should('exist').scrollIntoView();

		// Test finished successfully
	});
})
