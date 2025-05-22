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
        // 1. Penelitian Dosen
        'jabatan_penelitian',
        'judul_penelitian',
        'besaran_dana_penelitian',
        'sumber_dana_penelitian',
        // 2. Penghargaan Dosen
        'tingkat_penghargaan',
        'judul_penghargaan',
        'instansi_pemberi_penghargaan',
        // 3. Pengabdian dosen
        'jenis_kegiatan_pengabdian',
        'judul_pengabdian',
        'lokasi_pengabdian',
        'sumber_dana_pengabdian',
        // 4. Publikasi Dosen
        'jenis_publikasi',
        'judul_publikasi',
        'nama_jurnal_publikasi',
        'tahun_terbit_issn_publikasi',
        // 5. HKI Dosen    
        'nama_pemilik_hki',
        'judul_karya_hki',
        'jenis_hki',
        'tanggal_pengajuan_penerbitan_hki',
        // end
        'judul_penelitian',
        'bukti',
        'status_rekognisi',
        'umpan_balik',
        'id_admin_umpan_balik',
        'alasan_tolak',
        'id_admin_tolak',
        'id_admin_hapus'
    ];
    protected $dates = ['deleted_at'];

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, foreignKey: 'id_dosen')
            ->select('id', 'nama');
    }
    public function jenisRekognisi()
    {
        return $this->belongsTo(JenisRekognisi::class, 'type_rekognisi_id')->select('id', 'nama');
    }
    public function adminUmpanBalik()
    {
        return $this->belongsTo(Admin::class, 'id_admin_umpan_balik')->select('id', 'name');
    }

    public function adminHapus()
    {
        return $this->belongsTo(Admin::class, 'id_admin_hapus')->select('id', 'name');
    }
    public function kolaborator()
    {
        return $this->hasMany(RekognisiKolaborator::class, 'rekognisi_dosen_id');
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
