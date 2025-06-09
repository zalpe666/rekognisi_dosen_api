<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RekognisiDosenKolabolatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rekognisi_dosen_kolabolator')->insert([
            'rekognisi_dosen_id' => '1',
            'id_dosen_kolabolator' => '3',
            'created_at' => now(),
        ]);
    }
}
