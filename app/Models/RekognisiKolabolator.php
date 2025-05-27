<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekognisiKolabolator extends Model
{
    protected $table = 'rekognisi_dosen_kolaborator';
    public $timestamps = true;

    protected $fillable = [
        'rekognisi_dosen_id',
        'id_dosen_kolaborator',
    ];

    public function rekognisi()
    {
        return $this->belongsTo(Rekognisi::class, 'rekognisi_dosen_id');
    }

    public function dosenKolaborator()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen_kolaborator')->select('id', 'nama');
    }

}
