<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisRekognisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('jenis_rekognisi')->insert([
            'nama' => 'Rekognisi Dosen',
        ]);
        DB::table('jenis_rekognisi')->insert([
            'nama' => 'Penelitian Dosen',
        ]);
        DB::table('jenis_rekognisi')->insert([
            'nama' => 'Pengabdian Dosen',
        ]);
        DB::table('jenis_rekognisi')->insert([
            'nama' => 'Publikasi Dosen',
        ]);
        DB::table('jenis_rekognisi')->insert([
            'nama' => 'HKI Dosen',
        ]);
    }
}
