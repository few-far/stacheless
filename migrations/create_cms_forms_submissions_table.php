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
        Schema::create('cms_forms_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->uuid('entry_id')->index();
            $table->json('payload');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cms_forms_submissions');
    }
};
