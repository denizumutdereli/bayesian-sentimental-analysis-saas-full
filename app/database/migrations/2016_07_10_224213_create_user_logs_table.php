<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLogsTable extends Migration {

   /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('user_logs', function($table) {
            $table->increments('id', true);
            $table->integer('account_id')->length(10)->unsigned();
            $table->integer('user_id')->length(10)->unsigned();
            $table->string('source');
            $table->text('log');
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });
 
        Schema::table('user_logs', function($table) {
            $table->index('account_id');
            $table->index('user_id');
            $table->index('created_by');
            $table->index('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schemea::drop('user_logs');
    }

}
