<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Dosen extends Model
{
    use SoftDeletes;
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'nama',
        'nidn',
        'jabatan',
        'foto_profil',
        'program_studi',
        'email',
        'password',
    ];
}
