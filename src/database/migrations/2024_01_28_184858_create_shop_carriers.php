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
        Schema::connection('shop')->dropIfExists('shop_carriers');
        Schema::connection('shop')->create('shop_carriers', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 16);
            $table->string('carrierName', 40);
            $table->string('carrierDelay');
            $table->float('carrierPrice');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('shop')->dropIfExists('shop_carriers');
    }
};
