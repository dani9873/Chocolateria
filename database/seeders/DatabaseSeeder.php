<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Daniel',
            'email' => 'danielescobar.987@gmail.com',
            'password' => bcrypt('Accesssss.987'),
        ]);
        $this->call([
            EstadoVentaSeeder::class,
        ]);
    }
}
