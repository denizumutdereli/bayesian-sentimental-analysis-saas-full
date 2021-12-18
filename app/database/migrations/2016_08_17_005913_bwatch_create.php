<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BwatchCreate extends Migration {

   /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('bwatchs', function($table) {
            $table->increments('id', true);
            $table->integer('account_id')->length(10)->unsigned();
            $table->integer('user_id')->length(10)->unsigned();
            $table->string('username');
            $table->integer('client_id')->length(10)->unsigned();
            $table->string('client_name');
            $table->text('client_json');
            $table->text('bw_token');
            $table->string('expires_in');
            $table->string('status')->default('0');
            $table->string('is_active')->default('1');
            $table->string('token_type');
            $table->string('scope');
            $table->text('json');
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
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
        Schemea::drop('bwatchs');
    }

}
