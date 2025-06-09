<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RekognisiFileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rekognisi_file')->insert([
            'rekognisi_dosen_id' => '1',
            'id_dosen' => '3',
            'file' => 'https://example.com/file1.pdf',
            'nama_file' => 'Penghargaan Terbaik',
            'created_at' => now(),
        ]);

    }
}
