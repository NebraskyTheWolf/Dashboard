<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calendar_event', function (Blueprint $table) {
            $table->id();
            $table->string('calendar_id');

            $table->string('title', 80);
            $table->string('description')->nullable();

            $table->timestamp('start');
            $table->timestamp('end');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_event');
    }
};
