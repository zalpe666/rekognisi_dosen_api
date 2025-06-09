<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekognisiKolabolator extends Model
{
    protected $table = 'rekognisi_dosen_kolabolator';
    public $timestamps = true;

    protected $fillable = [
        'rekognisi_dosen_id',
        'id_dosen_kolabolator',
    ];

    public function rekognisi()
    {
        return $this->belongsTo(Rekognisi::class, 'rekognisi_dosen_id');
    }

    public function dosenKolabolator()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen_kolabolator')->select('id', 'nama');
    }

}
