<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_redirects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->boolean('enabled')->default(false);
            $table->string('source_type');
            $table->string('source', 2048);
            $table->text('target');
            $table->smallInteger('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cms_redirects');
    }
};
