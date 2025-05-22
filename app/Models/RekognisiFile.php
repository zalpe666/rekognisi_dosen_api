<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RekognisiFile extends Model
{
    use SoftDeletes;

    protected $table = 'rekognisi_file';

    protected $fillable = [
        'rekognisi_dosen_id',
        'id_dosen',
        'file',
        'nama_file',
    ];

    public function rekognisi()
    {
        return $this->belongsTo(Rekognisi::class, 'rekognisi_dosen_id');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }
}
