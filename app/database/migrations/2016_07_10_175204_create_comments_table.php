<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('comments', function($table) {
            $table->increments('id', true);
            $table->integer('account_id')->length(10)->unsigned();
            $table->integer('user_id')->length(10)->unsigned();
            $table->integer('source_id')->length(10)->unsigned();
            $table->integer('file_id')->length(10)->unsigned();
            $table->string('post_id')->nullable();
            $table->string('post_title')->nullable();
            $table->string('post_url')->nullable();
            $table->string('post_type')->nullable();
            $table->string('comment_id')->nullable();
            $table->text('text');
            $table->string('username')->nullable();
            $table->string('url')->nullable();
            $table->tinyInteger('is_published')->default('0');
            $table->tinyInteger('is_processed')->default('0');
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('ALTER TABLE comments ADD FULLTEXT search(text)');

        Schema::table('comments', function($table) {
            $table->index('account_id');
            $table->index('user_id');
            $table->index('source_id');
            $table->index('file_id');
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
        Schemea::drop('comments');
    }

}
