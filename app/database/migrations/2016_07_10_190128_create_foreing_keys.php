<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForeingKeys extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        //Accounts
        Schema::table('accounts', function($table) {
            //$table->foreign('id')->references('account_id')->on('users')->onDelete('cascade');
        });

        //Users
        Schema::table('users', function($table) {
            //$table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        //Sources        
        Schema::table('sources', function($table) {
            //$table->foreign('id')->references('source_id')->on('uploads')->onDelete('cascade');
            //$table->foreign('id')->references('source_id')->on('comments')->onDelete('cascade');
        });

        //Uploads       
        Schema::table('uploads', function($table) {
            //$table->foreign('id')->references('file_id')->on('comments')->onDelete('cascade');
        });

        //Comments       
        Schema::table('comments', function($table) {
            //$table->foreign('file_id')->references('in')->on('uploads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }

}
