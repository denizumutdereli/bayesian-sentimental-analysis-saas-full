<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUploadsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('uploads', function($table) {
            $table->increments('id', true)->length(10)->unsigned();
            $table->string('name');
            $table->integer('account_id')->length(10)->unsigned();
            $table->integer('user_id')->length(10)->unsigned();
            $table->integer('source_id')->length(10)->unsigned();
            $table->integer('count');
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('uploads', function($table) {
            $table->index('account_id');
            $table->index('user_id');
            $table->index('source_id');
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
        Schemea::drop('uploads');
    }

}
