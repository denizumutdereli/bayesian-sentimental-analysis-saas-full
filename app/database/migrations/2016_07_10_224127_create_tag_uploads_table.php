<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagUploadsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tag_uploads', function($table) {
            $table->increments('id', true);
            $table->integer('account_id')->length(10)->unsigned();
            $table->integer('user_id')->length(10)->unsigned();
            $table->integer('tag_id')->length(10)->unsigned();
            $table->integer('file_id')->length(10)->unsigned();
            $table->string('tag');
            $table->enum('type', array('manual', 'upload'))->default('manual');
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });
 
        Schema::table('tag_uploads', function($table) {
            $table->index('account_id');
            $table->index('user_id');
            $table->index('tag_id');
            $table->index('file_id');
            $table->index('tag');
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
        Schemea::drop('tag_uploads');
    }

}
