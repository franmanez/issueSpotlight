<?php
/**
 * @file plugins/generic/issueSpotlight/index.php
 *
 * Copyright (c) 2026 UPC - Universitat Politècnica de Catalunya
 * Author: Fran Máñez <fran.upc@gmail.com>, <francisco.manez@upc.edu>
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Plugin entry point and loader for IssueSpotlight IA.
 */
require_once('IssueSpotlightPlugin.inc.php');
return new IssueSpotlightPlugin();
