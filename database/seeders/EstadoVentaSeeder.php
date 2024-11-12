<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoVentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estados = [
            ['nombre' => 'Pendiente'],
            ['nombre' => 'Procesando'],
            ['nombre' => 'Completada'],
            ['nombre' => 'Cancelada'],
            ['nombre' => '  Reembolsada'],
        ];

        DB::table('estados_ventas')->insert($estados);
    }
}
