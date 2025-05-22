<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Rekognisi;

class DosenController extends Controller
{
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $dosen = Dosen::where('email', $request->email)->first();

        // Cek apakah admin ditemukan dan password cocok
        if (!$dosen || !Hash::check($request->password, $dosen->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $dosen->createToken('dosen-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'dosen' => $dosen
        ]);
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out from all sessions successfully']);
    }
    public function dashboardStatistik($id)
    {
        $total = DB::table('rekognisi_dosen')->where('id_dosen', $id)->count();

        $diajukan = DB::table('rekognisi_dosen')
            ->where('id_dosen', $id)
            ->where('status_rekognisi', 'diajukan')
            ->count();

        $ditolak = DB::table('rekognisi_dosen')
            ->where('id_dosen', $id)
            ->where('status_rekognisi', 'ditolak')
            ->count();

        $diterima = DB::table('rekognisi_dosen')
            ->where('id_dosen', $id)
            ->where('status_rekognisi', 'diterima')
            ->count();

        return response()->json([
            'total_pengajuan' => $total,
            'diajukan' => $diajukan,
            'ditolak' => $ditolak,
            'diterima' => $diterima,
        ]);
    }
    public function rekognisiSaya($id)
    {
        $rekognisi = Rekognisi::with(['dosen', 'kolaborator.dosenKolaborator'])
            ->where('id_dosen', $id)
            ->orWhereHas('kolaborator', function ($query) use ($id) {
                $query->where('id_dosen_kolaborator', $id);
            })
            ->get();

        return response()->json([
            'rekognisi' => $rekognisi
        ]);
    }


}
