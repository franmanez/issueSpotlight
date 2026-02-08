/**
 * @file plugins/generic/issueSpotlight/schema.sql
 *
 * Copyright (c) 2026 UPC
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Manual database creation script for IssueSpotlight plugin.
 */

CREATE TABLE issue_ai_analysis (
    issue_id BIGINT NOT NULL,
    editorial_draft LONGTEXT,
    radar_analysis LONGTEXT,
    ods_analysis LONGTEXT,
    geo_analysis LONGTEXT,
    date_generated DATETIME,
    UNIQUE KEY issue_ai_analysis_issue_id (issue_id)
);
