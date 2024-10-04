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
        Schema::create('estados_ventas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->timestamps();

            // Índices
            $table->index('venta_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estados_ventas');
    }
};
