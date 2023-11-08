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
        Schema::create('cms_redirect_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamp('created_at')->nullable();
            $table->string('url', 2048);
            $table->string('path', 2048);
            $table->uuid('redirect')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cms_redirect_logs');
    }
};
