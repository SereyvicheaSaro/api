<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('purpose');
            $table->string('contact');
            $table->time('entry_time');
            $table->time('exit_time');
            
            $table->unsignedBigInteger('approver_id');
            $table->foreign('approver_id')->references('id')->on('employees');

            $table->boolean('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
