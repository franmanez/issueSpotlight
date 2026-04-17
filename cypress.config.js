module.exports = {
  e2e: {
    baseUrl: 'http://localhost/ojs34/index.php/jpk',
    specPattern: 'cypress/tests/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: false,
    video: false,
    screenshotOnRunFailure: true,
  },
  env: {
    // Add any environment variables here if needed
  }
}
