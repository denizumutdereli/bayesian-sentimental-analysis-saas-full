<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('accounts', function($table) {
            $table->increments('id', true)->length(10)->unsigned();
            $table->string('accountType');
            $table->string('package');
            $table->tinyInteger('api')->default('1');
            $table->string('api_key');
            $table->string('api_secret');
            $table->string('access_token');
            $table->string('name');
            $table->text('about')->nullable();
            $table->string('logo')->nullable();
            $table->tinyInteger('is_active')->default('1');
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::table('accounts', function($table) {
            $table->index('api');
            $table->index('api_key');
            $table->index('api_secret');
            $table->index('is_active');
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
        Schemea::drop('accounts');
    }

}
