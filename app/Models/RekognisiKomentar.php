<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekognisiKomentar extends Model
{
    protected $table = 'rekognisi_komentar';

    protected $fillable = [
        'rekognisi_dosen_id',
        'id_admin',
        'komentar',
    ];

    public function rekognisi()
    {
        return $this->belongsTo(Rekognisi::class, 'rekognisi_dosen_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin');
    }
}
