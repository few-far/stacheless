<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatamicEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statamic_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->string('site');
            $table->uuid('origin_id')->nullable();
            $table->boolean('published')->default(false);
            $table->string('blueprint')->nullable();
            $table->string('slug')->nullable();
            $table->string('uri')->nullable()->index();
            $table->string('date')->nullable();
            $table->string('collection');
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
        Schema::dropIfExists('statamic_entries');
    }
}
