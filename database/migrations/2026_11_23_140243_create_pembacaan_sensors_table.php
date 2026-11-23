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
        Schema::create('pembacaan_sensor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perangkat_id')->constrained('perangkat')->cascadeOnDelete();
            $table->unsignedTinyInteger('kelembapan_tanah');
            $table->decimal('suhu', 4, 1);
            $table->unsignedTinyInteger('kelembapan_udara');
            $table->timestamp('dibaca_pada');
            $table->index(['perangkat_id', 'dibaca_pada']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembacaan_sensor');
    }
};
