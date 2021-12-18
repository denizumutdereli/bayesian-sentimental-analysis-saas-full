<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('users', function($table) {
            $table->increments('id', true)->length(10)->unsigned();
            $table->integer('account_id')->length(10)->unsigned();
            $table->string('email');
            $table->string('password');
            $table->string('remember_token');
            $table->string('role');
            $table->text('permissions');
            $table->tinyInteger('is_active')->default('1');
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        }); 

        Schema::table('users', function($table) {
            $table->index('account_id');
            $table->unique('email');
            $table->index('role');
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
        Schemea::drop('users');
    }

}
