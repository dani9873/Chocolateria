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
        Schema::create('producto_materia_prima', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idmateriaPrima')->constrained('materia_primas')->onDelete('cascade');
            $table->foreignId('idProducto')->constrained('productos')->onDelete('cascade');
            $table->integer('cantidad_por_unidad');
            $table->timestamps();
              // Índices para mejorar búsquedas por claves foráneas
            $table->index('idmateriaPrima');
            $table->index('idProducto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_materia_prima');
    }
};
