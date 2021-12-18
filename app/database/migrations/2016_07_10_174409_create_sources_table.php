<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSourcesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('sources', function($table) {
            $table->increments('id', true);
            $table->string('name');
            $table->integer('account_id')->length(10)->unsigned();
            $table->integer('user_id')->length(10)->unsigned();
            $table->text('about')->nullable();
            $table->tinyInteger('is_published')->default('0');
            $table->tinyInteger('is_processed')->default('0');
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('sources', function($table) {
            $table->index('account_id');
            $table->index('user_id');
            $table->index('is_published');
            $table->index('is_processed');
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
        Schemea::drop('sources');
    }

}
