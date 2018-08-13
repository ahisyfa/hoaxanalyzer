<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTdmTrigramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tdm_trigrams', function (Blueprint $table) {
            $table->string('term');
            $table->integer('frequency')->unsigned();
            $table->integer('document');
            $table->string('class');
            $table->boolean('test_data')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tdm_trigrams');
    }
}
