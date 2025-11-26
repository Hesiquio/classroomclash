<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Docente Test',
            'email' => 'docente@example.com',
            'password' => Hash::make('password'),
            'role' => 'docente',
        ]);

        User::create([
            'name' => 'Estudiante Test',
            'email' => 'estudiante@example.com',
            'password' => Hash::make('password'),
            'role' => 'estudiante',
        ]);
    }
}
