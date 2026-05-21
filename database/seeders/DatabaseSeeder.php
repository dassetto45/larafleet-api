<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vehicle;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        User::factory()->create([
            'name' => 'Admin LaraFleet',
            'email' => 'admin@larafleet.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Utenti normali
        User::factory(5)->create(['role' => 'user']);

        // Veicoli
        Vehicle::factory(10)->create();
        Vehicle::factory(3)->unavailable()->create();
        Vehicle::factory(2)->inMaintenance()->create();
    }
}
