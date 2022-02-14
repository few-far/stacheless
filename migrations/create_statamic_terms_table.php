<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatamicTermsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statamic_terms', function (Blueprint $table) {
            $table->string('handle')->index();
            $table->string('taxonomy');
            $table->timestamps();
            $table->jsonb('json')->nullable();
            $table->text('yaml')->nullable();

            $table->primary(['taxonomy', 'handle']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statamic_terms');
    }
}
