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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->string('tipoTransaccion');
            $table->decimal('monto', 10, 2);
            $table->string('descripcion');
            $table->timestamp('fecha')->index(); // Index en fecha
            $table->string('categoria');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('materia_prima_id')->constrained('materia_primas')->onDelete('cascade');
            $table->timestamps();

            // Índices para claves foráneas
            $table->index('usuario_id');
            $table->index('materia_prima_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
