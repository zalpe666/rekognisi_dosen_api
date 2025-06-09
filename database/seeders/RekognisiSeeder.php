<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RekognisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tambah Rekognisi Dosen
        DB::table('rekognisi_dosen')->insert([
            'id_dosen' => '1',
            'type_rekognisi_id' => '1',
            'status_rekognisi' => 'diajukan',

            'nama_rekognisi' => 'Penelitian Terbaik',
            'tempat_rekognisi' => 'Internasional',
            'waktu_rekognisi' => 'MIT Technology Review',
            'keterangan_rekognisi' => 'MIT Technology Review',
            'created_at' => now(),
        ]);
        // Tambah Rekognisi Penelitian Dosen
        DB::table('rekognisi_dosen')->insert([
            'id_dosen' => '1',
            'type_rekognisi_id' => '2',
            'status_rekognisi' => 'diajukan',

            'judul_penelitian' => 'Penelitian Teknologi Informasi',
            'jabatan_penelitian' => 'Ketua Penelitian',
            'besaran_dana_penelitian' => '10000000',
            'sumber_dana_penelitian' => 'Universitas Yarsi',
            'created_at' => now(),
        ]);
        // Tambah Pengabdian Dosen
        DB::table('rekognisi_dosen')->insert([
            'id_dosen' => '1',
            'type_rekognisi_id' => '3',
            'status_rekognisi' => 'draft',

            'judul_pengabdian' => 'KKN Teknologi Informasi',
            'jenis_kegiatan_pengabdian' => 'KKN',
            'lokasi_pengabdian' => 'Desa Sukamaju',
            'sumber_dana_pengabdian' => 'Internal Kampus',
            'created_at' => now(),
        ]);
        // Tambah Publikasi Dosen
        DB::table('rekognisi_dosen')->insert([
            'id_dosen' => '1',
            'type_rekognisi_id' => '4',
            'status_rekognisi' => 'draft',

            'judul_publikasi' => 'Analisis Data Besar',
            'jenis_publikasi' => 'Jurnal Ilmiah',
            'nama_jurnal_publikasi' => 'Jurnal Teknologi Informasi',
            'tahun_terbit_iss_publikasi' => '2025 Juni 2026',
            'created_at' => now(),
        ]);
        // Tambah HKI Dosen
        DB::table('rekognisi_dosen')->insert([
            'id_dosen' => '1',
            'type_rekognisi_id' => '5',
            'status_rekognisi' => 'draft',

            'nama_pemilik_hki' => 'Elan Pratama',
            'judul_karya_hki' => 'Analisis Data Besar',
            'jenis_hki' => 'Hak Cipta',
            'tanggal_pengajuan_penerbitan_hki' => '2025-06-01',
            'created_at' => now(),
        ]);

    }
}
