<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDfBigramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('df_bigrams', function (Blueprint $table) {
            $table->string('term');
            $table->double('df');
            $table->double('idf');
            $table->boolean('feature_selection')->default(false);
        });

        Schema::table('df_bigrams', function($table){
            $table->primary('term');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('df_bigrams');
    }
}
