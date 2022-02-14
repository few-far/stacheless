<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatamicAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statamic_assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('container');
            $table->string('path', 1024);
            $table->string('folder', 1024);
            $table->timestamps();
            $table->jsonb('json')->nullable();
            $table->text('yaml')->nullable();

            $table->index(['container', 'path']);
            $table->index(['container', 'folder']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statamic_assets');
    }
}
