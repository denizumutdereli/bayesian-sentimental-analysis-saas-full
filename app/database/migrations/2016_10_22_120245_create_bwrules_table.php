<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBwrulesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('bwrules', function($table) {
            $table->increments('id', true);
            $table->integer('account_id')->length(10)->unsigned();
            $table->integer('user_id')->length(10)->unsigned();
            $table->integer('bwatch_id')->length(10)->unsigned();
            $table->string('name');
            $table->text('bw_token');
            $table->integer('project_id')->length(10)->unsigned();
            $table->text('queries');
            $table->integer('domain_id')->length(10)->unsigned();
            $table->string('action');
            $table->string('datamark')->default('codexai');
            $table->string('fromdate')->default('1');
            $table->string('sentiment');
            $table->text('param1');
            $table->text('param2');
            $table->text('param3');
            $table->text('categories');
            $table->text('tags');
            $table->string('delete')->default('0');
            $table->text('rule');
            $table->string('expires_in');
            $table->string('status')->default('0');
            $table->string('is_active')->default('1');
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->dateTime('last_queue_time');
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
        Schemea::drop('bwrules');
    }

}
