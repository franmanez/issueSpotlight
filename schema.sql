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
    editorial_draft TEXT,
    thematic_clusters TEXT,
    expert_suggestions TEXT,
    global_seo_description TEXT,
    tokens_consumed INT,
    date_generated DATETIME,
    UNIQUE KEY issue_ai_analysis_issue_id (issue_id)
);
