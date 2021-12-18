<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration {

   /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tickets', function($table) {
            $table->increments('id', true);
            $table->integer('account_id')->length(10)->unsigned();
            $table->integer('user_id')->length(10)->unsigned();
            $table->integer('parent_id')->length(10)->unsigned();
            $table->string('title');
            $table->text('description');
            $table->tinyInteger('status')->default('1');
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });
 
        Schema::table('tickets', function($table) {
            $table->index('account_id');
            $table->index('user_id');
            $table->index('parent_id');
            $table->index('status');
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
        Schemea::drop('tickets');
    }

}
