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
        Schema::create('estado_venta_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idventa')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('estado_venta_id')->constrained('estados_ventas')->onDelete('cascade');
            $table->timestamps();

            // Índices para claves foráneas
            $table->index('idventa');
            $table->index('estado_venta_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estado_venta_venta');
    }
};
