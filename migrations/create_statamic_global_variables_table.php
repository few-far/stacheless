<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatamicGlobalVariablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statamic_global_variables', function (Blueprint $table) {
            $table->string('handle')->index();
            $table->timestamps();
            $table->text('site');
            $table->jsonb('json')->nullable();
            $table->text('yaml')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statamic_global_variables');
    }
}
