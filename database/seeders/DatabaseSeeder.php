<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Database\Seeders\AdminSeeder;
use Database\Seeders\DosenSeeder;
use Database\Seeders\JenisRekognisiSeeder;
use Database\Seeders\RekognisiDosenKolabolatorSeeder;
use Database\Seeders\RekognisiFileSeeder;
use Database\Seeders\RekognisiKomentarSeeder;
use Database\Seeders\RekognisiSeeder;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            DosenSeeder::class,
            JenisRekognisiSeeder::class,
            RekognisiSeeder::class, // RekognisiSeeder harus dijalankan sebelum Kolaborator, File, Komentar
            RekognisiDosenKolabolatorSeeder::class, // Membutuhkan data dari RekognisiSeeder
            RekognisiFileSeeder::class,             // Membutuhkan data dari RekognisiSeeder
            RekognisiKomentarSeeder::class,         // Membutuhkan data dari RekognisiSeeder
        ]);
    }
}
