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
        Schema::create('venta_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idventa')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('idProducto')->constrained('productos')->onDelete('cascade');
            $table->integer('cantidad');
            $table->timestamps();

            //Índices para claves foráneas
            $table->index('idventa');
            $table->index('idProducto');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venta_producto');
    }
};
