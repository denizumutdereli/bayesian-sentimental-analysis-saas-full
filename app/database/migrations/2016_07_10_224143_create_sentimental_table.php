<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSentimentalTable extends Migration {

   /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('sentimental', function($table) {
            $table->increments('id', true);
            $table->integer('user_id')->length(10)->unsigned();
            $table->integer('account_id')->length(10)->unsigned();
            $table->integer('domain_id')->length(10)->unsigned();
            $table->integer('source_id')->length(10)->unsigned();
            $table->text('text');
            $table->tinyInteger('state')->default('0');
            $table->string('source');
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('ALTER TABLE sentimental ADD FULLTEXT search(text)');

        Schema::table('sentimental', function($table) {
            $table->index('account_id');
            $table->index('user_id');
            $table->index('domain_id');
            $table->index('state');
            $table->index('source');
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
        Schemea::drop('sentimental');
    }

}
