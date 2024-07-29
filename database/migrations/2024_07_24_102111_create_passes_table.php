<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePassesTable extends Migration
{
    public function up()
    {
        Schema::create('passes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('visitor_id');
            $table->foreign('visitor_id')->references('id')->on('visitors');

            $table->unsignedBigInteger('approved_by');
            $table->foreign('approved_by')->references('id')->on('employees');

            $table->boolean('status');
            $table->string('vehicle_plate');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('passes');
    }
}
