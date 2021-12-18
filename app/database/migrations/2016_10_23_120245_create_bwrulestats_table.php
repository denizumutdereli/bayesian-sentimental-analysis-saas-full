<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBwruleStatsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('bwrulestats', function($table) {
            $table->increments('id', true);
            $table->integer('account_id')->length(10)->unsigned();
            $table->integer('user_id')->length(10)->unsigned();
            $table->integer('bwatch_id')->length(10)->unsigned();
            $table->integer('bwrule_id')->length(10)->unsigned();
            $table->integer('project_id')->length(10)->unsigned();
            $table->text('queries');
            $table->integer('domain_id')->length(10)->unsigned();
            $table->string('totalMentions')->default('0');
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
        Schemea::drop('bwrulestats');
    }

}
