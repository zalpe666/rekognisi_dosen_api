<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RekognisiKomentarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rekognisi_komentar')->insert([
            'rekognisi_dosen_id' => '1',
            'id_admin' => '1',
            'komentar' => 'Komentar tentang penelitian ini sangat baik dan memberikan kontribusi yang signifikan terhadap bidang teknologi informasi.',
            'created_at' => now(),
        ]);
    }
}
