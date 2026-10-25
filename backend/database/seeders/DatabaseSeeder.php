<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ecommerce.test'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password123'),
                'is_admin' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'cliente@ecommerce.test'],
            [
                'name' => 'Cliente de Prueba',
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ]
        );

        $this->call([
            ProductSeeder::class,
        ]);
    }
}