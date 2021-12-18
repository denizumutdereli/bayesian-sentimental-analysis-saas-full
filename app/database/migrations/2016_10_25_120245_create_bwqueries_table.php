<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBwQueriesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('bwqueries', function($table) {
            $table->increments('id', true);
            $table->integer('account_id')->length(10)->unsigned();
            $table->integer('bwatch_id')->length(10)->unsigned();
            $table->integer('project_id')->length(10)->unsigned();
            $table->integer('query_id')->length(10)->unsigned();
            $table->string('name');
            $table->string('cycle')->default('0');
            $table->string('error');
            $table->string('errorCode');
            $table->text('errorMessage');
            $table->text('request');
            $table->string('resultsPage');
            $table->string('resultsPageSize');
            $table->string('resultsTotal');
            $table->string('pulledData');
            $table->string('startDate');
            $table->string('endDate');
            $table->string('maximumIdInResult');
            $table->string('maximumId');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schemea::drop('bwqueries');
    }

}
