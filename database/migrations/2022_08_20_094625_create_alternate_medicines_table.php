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
        Schema::create('alternate_medicines', function (Blueprint $table) {
            $table->id();
            $table->string('cord_uid');
            $table->string('sentence');
            $table->string('drug');
            $table->string('disease');
            $table->string('label');
            $table->string('confidence');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alternate_medicines');
    }
};
