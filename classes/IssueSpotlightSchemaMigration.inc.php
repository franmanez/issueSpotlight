<?php

/**
 * @file plugins/generic/issueSpotlight/classes/IssueSpotlightSchemaMigration.inc.php
 *
 * Copyright (c) 2026 UPC
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class IssueSpotlightSchemaMigration
 * @brief Database migration for creating the issue_ai_analysis table.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class IssueSpotlightSchemaMigration extends Migration {
    /**
     * Run the migrations.
     * @return void
     */
    public function up() {
        if (!Capsule::schema()->hasTable('issue_ai_analysis')) {
            Capsule::schema()->create('issue_ai_analysis', function (Blueprint $table) {
                $table->bigInteger('issue_id')->primary();
                $table->longText('editorial_draft')->nullable();
                $table->longText('radar_analysis')->nullable();
                $table->longText('ods_analysis')->nullable();
                $table->longText('geo_analysis')->nullable();
                $table->dateTime('date_generated')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down() {
        Capsule::schema()->dropIfExists('issue_ai_analysis');
    }
}
