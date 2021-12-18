<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDomainsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('domains', function($table) {
            $table->increments('id', true);
            $table->integer('user_id')->length(10)->unsigned();
            $table->integer('account_id')->length(10)->unsigned();
            $table->string('name');
            $table->text('settings');
            $table->tinyInteger('sense')->default('0');;
            $table->string('domain_secret');
            $table->tinyInteger('is_active')->default('1');
            $table->tinyInteger('is_default')->default('0');
            $table->tinyInteger('is_private')->default('0');
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('domains', function($table) {
            $table->index('account_id');
            $table->index('user_id');
            $table->index('is_default');
            $table->index('is_private');
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
        Schemea::drop('domains');
    }

}
