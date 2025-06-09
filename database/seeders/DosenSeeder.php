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
            'email' => 'elan@gmail.com',
            'password' => Hash::make('elan123'),
            'nama' => 'Elan Suherlan',
            'nidn' => '1234567890',
            'Jabatan' => 'Dosen Tetap',
            'program_studi' => 'TI',
        ]);
        Dosen::create([
            'email' => 'ujang@gmail.com',
            'password' => Hash::make('ujang123'),
            'nama' => 'Ujang Muhammad',
            'nidn' => '1234567888',
            'Jabatan' => 'Dosen Tetap',
            'program_studi' => 'TI',
        ]);

        Dosen::create([
            'email' => 'zalpe@gmail.com',
            'password' => Hash::make('zalpe123'),
            'nama' => 'Zalpe Rabbani',
            'nidn' => '0987654321',
            'Jabatan' => 'Asisten Ahli',
            'program_studi' => 'PDSI',
        ]);
    }
}
