<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Rekognisi;
use App\Models\RekognisiFile;
use App\Models\RekognisiKolaborator;
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
    public function rekognisiSaya(Request $request, $id)
    {
        $status = $request->status;
        $tahun = $request->tahun;
        $type_rekognisi_id = $request->type_rekognisi_id;

        $rekognisi = Rekognisi::with(['dosen'])
            ->where(function ($query) use ($id) {
                $query->where('id_dosen', $id)
                    ->orWhereHas('kolaborator', function ($subQuery) use ($id) {
                        $subQuery->where('id_dosen_kolaborator', $id);
                    });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status_rekognisi', $status);
            })
            ->when($tahun, function ($query) use ($tahun) {
                $query->whereYear('created_at', $tahun);
            })
            ->when($type_rekognisi_id, function ($query) use ($type_rekognisi_id) {
                $query->where('type_rekognisi_id', $type_rekognisi_id);
            })
            ->get();

        return response()->json([
            'rekognisi' => $rekognisi
        ]);
    }
    public function showRekognisi($id)
    {
        $rekognisi = Rekognisi::with([
            'dosen',
            'kolaborator.dosenKolaborator',
            'komentar.admin',
            'files'
        ])->findOrFail($id);

        return response()->json($rekognisi);

    }
    public function storeFile(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'rekognisi_dosen_id' => 'required|exists:rekognisi_dosen,id',
            'id_dosen' => 'required|exists:dosens,id',
            'file' => 'required|file|mimes:pdf|max:5120',
            'nama_file' => 'required|string|max:255', // tambahkan ini

        ]);

        $file = $request->file('file');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads'), $filename);

        $rekognisiFile = RekognisiFile::create([
            'rekognisi_dosen_id' => $request->rekognisi_dosen_id,
            'id_dosen' => $request->id_dosen,
            'file' => $filename,
            'nama_file' => $request->nama_file, // simpan ke DB
        ]);

        return response()->json([
            'message' => 'Rekognisi dosen berhasil ditambahkan',
            'data' => $rekognisiFile,
            'url' => asset('uploads/' . $filename),
        ], 201);
    }
    public function tambahKolaborator(Request $request)
    {
        $request->validate([
            'rekognisi_dosen_id' => 'required|exists:rekognisi_dosen,id',
            'id_dosen_kolaborator' => 'required|exists:dosens,id',
        ]);

        $kolaborator = RekognisiKolaborator::create([
            'rekognisi_dosen_id' => $request->rekognisi_dosen_id,
            'id_dosen_kolaborator' => $request->id_dosen_kolaborator,
        ]);

        return response()->json([
            'message' => 'Kolaborator berhasil ditambahkan',
            'data' => $kolaborator,
        ], 201);
    }




}
