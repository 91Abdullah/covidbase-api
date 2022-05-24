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
        Schema::create('rnas', function (Blueprint $table) {
            $table->id();
            $table->string('disease');
            $table->string('RNA');
            $table->double('cosine')->default(0);
            $table->string('title');
            $table->text('abstract');
            $table->string('doi');
            $table->boolean('found_flag')->default(0);
            $table->string('association')->default('');
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
        Schema::dropIfExists('rnas');
    }
};
