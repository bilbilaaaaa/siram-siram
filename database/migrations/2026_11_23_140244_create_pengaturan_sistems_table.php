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
        Schema::create('pengaturan_sistem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perangkat_id')->unique()->constrained('perangkat');
            $table->string('mode')->default('otomatis');
            $table->boolean('pompa_manual')->default(false);
            $table->unsignedTinyInteger('batas_kering')->default(30);
            $table->unsignedTinyInteger('batas_basah')->default(70);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_sistem');
    }
};
