<?php

namespace App\Http\Controllers;
use App\Models\Dosen;
use Illuminate\Support\Facades\Storage;
use App\Models\Rekognisi;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        // Cek apakah admin ditemukan dan password cocok
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'admin' => $admin
        ]);
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out from all sessions successfully']);
    }

    public function profile(Request $request)
    {
        // Mengembalikan data admin yang login berdasarkan token Sanctum
        return response()->json([
            'admin' => $request->user()
        ]);
    }
    public function index(Request $request)
    {
        $prodi = $request->query('program_studi');

        if ($prodi) {
            $data = Dosen::where('program_studi', $prodi)->get();
        } else {
            $data = Dosen::all();
        }

        return response()->json($data);
    }
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nidn' => 'required|string|unique:dosens,nidn',
            'jabatan' => 'required|string',
            'program_studi' => 'required|in:TI,PDSI',
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Mengecek apakah ada file foto_profil yang diupload
        if ($request->hasFile('foto_profil')) {
            // Simpan file foto_profil dan ambil path-nya
            $path = $request->file('foto_profil')->store('foto_dosen', 'public');
            $validated['foto_profil'] = $path;
        }

        // Simpan data dosen yang sudah divalidasi
        Dosen::create($validated);

        // Return response JSON jika berhasil
        return response()->json(['message' => 'Dosen berhasil ditambahkan'], 201);
    }
    public function update(Request $request, Dosen $dosen)
    {
        $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'nidn' => 'sometimes|required|string|unique:dosens,nidn,' . $dosen->id,
            'jabatan' => 'sometimes|required|string|max:255',
            'program_studi' => 'sometimes|required|in:TI,PDSI',
            'foto_profil' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['nama', 'nidn', 'jabatan', 'program_studi']);

        if ($request->hasFile('foto_profil')) {
            if ($dosen->foto_profil) {
                Storage::disk('public')->delete($dosen->foto_profil);
            }
            $data['foto_profil'] = $request->file('foto_profil')->store('foto_dosen', 'public');
        }

        $dosen->update($data);

        return response()->json([
            'message' => 'Data dosen berhasil diperbarui',
            'data' => $dosen
        ], 200);
    }
    public function destroy(Dosen $dosen)
    {
        $dosen->delete();
        return response()->json(['message' => 'Dosen berhasil dihapus']);
    }
    public function dosenPerProdi($prodi)
    {
        $data = Dosen::where('program_studi', $prodi)->get();
        return response()->json($data);
    }
    public function search(Request $request)
    {
        $keyword = $request->query('nama');

        if (!$keyword) {
            return response()->json(['message' => 'Parameter "nama" wajib diisi'], 400);
        }

        $data = Dosen::where('nama', 'like', '%' . $keyword . '%')->get();

        if ($data->isEmpty()) {
            return response()->json(['message' => 'Dosen dengan nama tersebut tidak ditemukan'], 404);
        }

        return response()->json($data);
    }
    public function indexRekognisi(Request $request)
    {
        $status = $request->query('status_rekognisi');
        $tahun = $request->query('tahun');
        $type_rekognisi_id = $request->query('type_rekognisi_id');
        $query = Rekognisi::with(relations: ['dosen', 'jenisRekognisi'])
            ->orderBy('created_at', 'desc');
        if ($status) {
            $query->where('status_rekognisi', $status);
        }

        if ($tahun) {
            $query->whereYear('created_at', $tahun);
        }
        if ($type_rekognisi_id) {
            $query->where('type_rekognisi_id', $type_rekognisi_id);
        }

        $data = $query->get();

        return response()->json($data);
    }
    public function rekognisiByKategori()
    {
        $rekap = DB::table('jenis_rekognisi')
            ->leftJoin('rekognisi_dosen', 'jenis_rekognisi.id', '=', 'rekognisi_dosen.type_rekognisi_id')
            ->select(
                'jenis_rekognisi.id as kategori_id',
                'jenis_rekognisi.nama as kategori_nama',
                DB::raw('COUNT(rekognisi_dosen.id) as total_rekognisi')
            )
            ->groupBy('jenis_rekognisi.id', 'jenis_rekognisi.nama')
            ->get();

        return response()->json([
            'rekap_per_kategori' => $rekap
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
    public function tolakRekognisi(Request $request, $id)
    {
        $rekognisi = Rekognisi::findOrFail($id);

        // Cek apakah status saat ini adalah 'diajukan'
        if ($rekognisi->status_rekognisi !== 'diajukan') {
            return response()->json([
                'message' => 'Rekognisi hanya bisa ditolak jika statusnya diajukan.'
            ], 422);
        }

        $request->validate([
            'id_admin_tolak' => 'required|integer|exists:admins,id',
            'alasan_tolak' => 'required|string|max:255',
        ]);

        $rekognisi->status_rekognisi = 'ditolak';
        $rekognisi->id_admin_tolak = $request->input('id_admin_tolak');
        $rekognisi->alasan_tolak = $request->input('alasan_tolak');
        $rekognisi->save();

        return response()->json([
            'message' => 'Rekognisi berhasil ditolak',
            'data' => $rekognisi
        ], 200);
    }
    public function hapusRekognisi(Request $request, $id)
    {
        $rekognisi = Rekognisi::findOrFail($id);

        if ($rekognisi->status_rekognisi !== 'ditolak') {
            return response()->json([
                'message' => 'Rekognisi hanya dapat dihapus jika statusnya sudah "ditolak".'
            ], 403);
        }

        $request->validate([
            'id_admin_hapus' => 'required|integer|exists:admins,id',
        ]);

        $rekognisi->update([
            'id_admin_hapus' => $request->id_admin_hapus,
        ]);

        $rekognisi->delete();

        return response()->json([
            'message' => 'Data rekognisi berhasil dihapus secara soft delete'
        ]);
    }
    public function terimaRekognisi(Request $request, $id)
    {
        $rekognisi = Rekognisi::findOrFail($id);
        // Cek apakah status saat ini adalah 'diajukan'
        if ($rekognisi->status_rekognisi !== 'diajukan') {
            return response()->json([
                'message' => 'Rekognisi hanya bisa diterima jika statusnya diajukan.'
            ], 422);
        }
        $request->validate([
            'id_admin_terima' => 'required|integer|exists:admins,id',
        ]);

        $rekognisi->status_rekognisi = 'diterima';
        $rekognisi->id_admin_terima = $request->input('id_admin_terima');
        $rekognisi->alasan_tolak = null; // Kosongkan alasan tolak kalau sebelumnya ditolak
        $rekognisi->id_admin_tolak = null;
        $rekognisi->save();

        return response()->json([
            'message' => 'Rekognisi berhasil diterima',
            'data' => $rekognisi
        ], 200);
    }
    public function dashboardGrafik(): JsonResponse
    {
        $tahun = 2025;
        $rekognisi = Rekognisi::select(
            DB::raw('MONTH(created_at) as bulan'),
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('created_at', $tahun)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get()
            ->pluck('total', 'bulan');

        // Nama bulan
        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        // Format hasil
        $data = [];
        foreach ($namaBulan as $i => $nama) {
            $data[] = [
                'bulan' => $nama,
                'jumlah' => $rekognisi->get($i, 0),
            ];
        }

        return response()->json([
            'tahun' => $tahun,
            'rekognisi_per_bulan' => $data,
        ]);
    }
    public function dashboardStatistik(): JsonResponse
    {
        return response()->json([
            'jumlah_dosen' => Dosen::count(),
            'jumlah_rekognisi' => Rekognisi::count(),
            'rekognisi_disetujui' => Rekognisi::where('status_rekognisi', 'Disetujui')->count(),
            'rekognisi_ditolak' => Rekognisi::where('status_rekognisi', 'Ditolak')->count(),
            // 'rekognisi_pending' => Rekognisi::where('status_rekognisi', 'Pending')->count(),
        ]);
    }
}
