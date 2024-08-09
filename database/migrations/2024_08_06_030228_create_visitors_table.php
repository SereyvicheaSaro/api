<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact');
            $table->string('purpose');
            $table->time('entry_time');
            $table->time('exit_time');
            $table->integer('scan_count');
            $table->date('date')->nullable();
            $table->string('approver')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->string('qr_code')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
