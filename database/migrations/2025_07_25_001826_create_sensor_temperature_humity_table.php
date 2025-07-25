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
        Schema::create('sensor_temperature_humity', function (Blueprint $table) {
            $table->id();
            $table->string('temperature'); // Para guardar valores como "18.10"
            $table->string('humity'); // Para guardar valores como "67.80"
            $table->timestamp('created_at')->useCurrent(); // Solo created_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_temperature_humity');
    }
};
