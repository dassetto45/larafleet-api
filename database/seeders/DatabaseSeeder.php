<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'     => 'Admin LaraFleet',
            'email'    => 'admin@larafleet.test',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        // Utenti statici
        foreach (range(1, 5) as $i) {
            User::create([
                'name'     => "Utente $i",
                'email'    => "utente$i@larafleet.test",
                'password' => bcrypt('password'),
                'role'     => 'user',
            ]);
        }

        // Veicoli statici
        $vehicles = [
            ['plate' => 'AB123CD', 'brand' => 'Fiat',       'model' => 'Panda',    'year' => 2020, 'km' => 45000,  'type' => 'car',     'status' => 'available'],
            ['plate' => 'EF456GH', 'brand' => 'Ford',       'model' => 'Transit',  'year' => 2021, 'km' => 80000,  'type' => 'van',     'status' => 'available'],
            ['plate' => 'IL789MN', 'brand' => 'BMW',        'model' => 'X3',       'year' => 2022, 'km' => 30000,  'type' => 'car',     'status' => 'available'],
            ['plate' => 'OP012QR', 'brand' => 'Volkswagen', 'model' => 'Crafter',  'year' => 2019, 'km' => 120000, 'type' => 'truck',   'status' => 'available'],
            ['plate' => 'ST345UV', 'brand' => 'Toyota',     'model' => 'Yaris',    'year' => 2023, 'km' => 15000,  'type' => 'car',     'status' => 'available'],
            ['plate' => 'WX678YZ', 'brand' => 'Piaggio',    'model' => 'Liberty',  'year' => 2021, 'km' => 22000,  'type' => 'scooter', 'status' => 'available'],
            ['plate' => 'AA111BB', 'brand' => 'Mercedes',   'model' => 'Sprinter', 'year' => 2020, 'km' => 95000,  'type' => 'van',     'status' => 'in_use'],
            ['plate' => 'CC222DD', 'brand' => 'Iveco',      'model' => 'Daily',    'year' => 2018, 'km' => 150000, 'type' => 'truck',   'status' => 'in_use'],
            ['plate' => 'EE333FF', 'brand' => 'Fiat',       'model' => 'Ducato',   'year' => 2022, 'km' => 40000,  'type' => 'van',     'status' => 'maintenance'],
            ['plate' => 'GG444HH', 'brand' => 'Honda',      'model' => 'SH 125',   'year' => 2023, 'km' => 8000,   'type' => 'scooter', 'status' => 'maintenance'],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::create($vehicle);
        }
    }
}
