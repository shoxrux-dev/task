<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            ['id' => 1, 'name' => 'Karakalpakstan', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Andijon', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Bukhara', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Jizzakh', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'Qashqadaryo', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'name' => 'Navoiy', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'name' => 'Namangan', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'name' => 'Samarqand', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'name' => 'Surxondaryo', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'name' => 'Sirdaryo', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'name' => 'Tashkent', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'name' => 'Fergana', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'name' => 'Khorezm', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 14, 'name' => 'Tashkent City', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('regions')->insert($regions);
    }
}
