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
		// 1. Manual Login
		cy.visit('http://localhost/ojs/index.php/ACE/login');
		cy.get('#username').type('admin');
		cy.get('#password').type('XXXX');
		cy.get('button.submit').click();

		// Verify login success
		cy.get('.app__nav').should('exist');

		// 2. Go to Website Settings > Plugins
		cy.get('a[href*="management/settings/website"]').click();
		cy.get('button[id="plugins-button"]').click();

		// 3. Verify the plugin exists by its name
		// This is the most robust way to check if the plugin is installed
		cy.contains('IssueSpotlight IA').should('exist').scrollIntoView();

		// Test finished successfully
	});
})
