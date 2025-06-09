<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rekognisi extends Model
{
    use SoftDeletes;

    protected $table = 'rekognisi_dosen';

    protected $fillable = [
        'id_dosen',
        'type_rekognisi_id',
        'status_rekognisi',
        // Rekognisi Dosen 
        'nama_rekognisi',
        'tempat_rekognisi',
        'waktu_rekognisi',
        'keterangan_rekognisi',
        // Penelitian Dosen
        'judul_penelitian',
        'jabatan_penelitian',
        'besaran_dana_penelitian',
        'sumber_dana_penelitian',
        // Pengabdian dosen
        'judul_pengabdian',
        'jenis_kegiatan_pengabdian',
        'lokasi_pengabdian',
        'sumber_dana_pengabdian',
        // Publikasi Dosen
        'judul_publikasi',
        'jenis_publikasi',
        'nama_jurnal_publikasi',
        'tahun_terbit_issn_publikasi',
        // HKI Dosen    
        'judul_karya_hki',
        'nama_pemilik_hki',
        'jenis_hki',
        'tanggal_pengajuan_penerbitan_hki',
        // All
        'id_admin_terima',
        'id_admin_tolak',
        'id_admin_hapus',
        'alasan_tolak',
    ];
    protected $dates = ['deleted_at'];

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen')
            ->select('id', 'nama', 'jabatan');
    }

    public function jenisRekognisi()
    {
        return $this->belongsTo(JenisRekognisi::class, 'type_rekognisi_id')->select('id', 'nama');
    }
    public function adminHapus()
    {
        return $this->belongsTo(Admin::class, 'id_admin_hapus')->select('id', 'name');
    }
    public function kolabolator()
    {
        return $this->hasMany(RekognisiKolabolator::class, 'rekognisi_dosen_id');
    }
    public function komentar()
    {
        return $this->hasMany(RekognisiKomentar::class, 'rekognisi_dosen_id');
    }
    public function files()
    {
        return $this->hasMany(RekognisiFile::class, 'rekognisi_dosen_id');
    }





}
