<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class JenisRekognisi extends Model
{
    use HasFactory;
    protected $table = 'jenis_rekognisi';

    protected $fillable = ['nama'];

    public function rekognisiDosen()
    {
        return $this->hasMany(Rekognisi::class, 'type_rekognisi_id');
    }

    public function jenisRekognisi()
    {
        return $this->belongsTo(JenisRekognisi::class, 'type_rekognisi_id');
    }

}
