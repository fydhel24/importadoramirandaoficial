<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleReportesSeeder::class); 
         // User::factory(10)->create();
        //$this->call(RoleSeeder::class);
        //$this->call(RoleNuevoSeeder::class);
        //User::create([
         //   'name' => 'Fidel',
          //  'email' => 'fidel@gmail.com',
        //    'password' => Hash::make('fidel@gmail.com'),
        //])->assignRole('Admin');
        /* $faker = Faker::create();

        // Sembrar categorías
        foreach (range(1, 10) as $index) {
            DB::table('categorias')->insert([
                'categoria' => $faker->word,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Sembrar marcas
        foreach (range(1, 10) as $index) {
            DB::table('marcas')->insert([
                'marca' => $faker->company,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Sembrar tipos
        foreach (range(1, 10) as $index) {
            DB::table('tipos')->insert([
                'tipo' => $faker->word,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Sembrar cupos
        foreach (range(1, 10) as $index) {
            DB::table('cupos')->insert([
                'codigo' => $faker->word,
                'porcentaje' => $faker->randomFloat(2, 0, 100), // Porcentaje entre 0 y 100
                'created_at' => now(),
                'updated_at' => now(),
            ]); */
        //}
    }
}
