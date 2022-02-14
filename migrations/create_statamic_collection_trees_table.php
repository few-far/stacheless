<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatamicCollectionTreesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statamic_collection_trees', function (Blueprint $table) {
            $table->string('handle')->index();
            $table->timestamps();
            $table->string('site');
            $table->jsonb('json')->nullable();
            $table->text('yaml')->nullable();

            $table->primary(['site', 'handle']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statamic_collection_trees');
    }
}
