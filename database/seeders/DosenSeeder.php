<?php

namespace Database\Seeders;
use App\Models\Dosen;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Dosen::create([
            'email' => 'dosen@gmail.com',
            'password' => Hash::make('dosen123'),
            'nama' => 'Dosen Admin',
            'nidn'  => '1234567890',
            'Jabatan' => 'Dosen Tetap',
            'program_studi' => 'TI',
        ]);
    }
}
