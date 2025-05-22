<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RekognisiKomentar;

class RekognisiKolaboratorController extends Controller
{
    public function storeKomentar(Request $request)
    {
        $request->validate([
            'rekognisi_dosen_id' => 'required|exists:rekognisi_dosen,id',
            'id_admin' => 'required|exists:admins,id',
            'komentar' => 'required|string|max:1000',
        ]);

        $komentar = RekognisiKomentar::create([
            'rekognisi_dosen_id' => $request->rekognisi_dosen_id,
            'id_admin' => $request->id_admin,
            'komentar' => $request->komentar,
        ]);

        return response()->json([
            'message' => 'Komentar berhasil ditambahkan.',
            'data' => $komentar,
        ], 201);
    }
    
}
