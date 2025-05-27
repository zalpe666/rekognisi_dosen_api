<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rekognisi;
use App\Models\Dosen;
use App\Models\RekognisiKomentar;
use Illuminate\Http\JsonResponse;

class RekognisiController extends Controller
{
    public function index(Request $request)
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_dosen' => 'required|integer|exists:dosens,id',
            'type_rekognisi_id' => 'required|exists:jenis_rekognisi,id',
            // 1.Penelitian Dosen
                'jabatan_penelitian' => 'nullable|string',
                'judul_penelitian' => 'nullable|string',
                'besaran_dana_penelitian' => 'nullable|integer',
                'sumber_dana_penelitian' => 'nullable|string',
            // 2.Penghargaan Dosen
                'tingkat_penghargaan' => 'nullable|string',
                'judul_penghargaan' => 'nullable|string',
                'instansi_pemberi_penghargaan' => 'nullable|string',
            // 3.Pengabdian Dosen
                'jenis_kegiatan_pengabdian' => 'nullable|string',
                'judul_pengabdian' => 'nullable|string',
                'lokasi_pengabdian' => 'nullable|string',
                'sumber_dana_pengabdian' => 'nullable|string',
            // 4.Publikasi Dosen
                'jenis_publikasi' => 'nullable|string',
                'judul_publikasi' => 'nullable|string',
                'nama_jurnal_publikasi' => 'nullable|string',
                'tahun_terbit_issn_publikasi' => 'nullable|string',
            // 5.HKI Dosen
                'nama_pemilik_hki' => 'nullable|string',
                'judul_karya_hki' => 'nullable|string',
                'jenis_hki' => 'nullable|string',
                'tanggal_pengajuan_penerbitan_hki' => 'nullable|string',
            // end
                'bukti' => 'nullable|file|max:2048',
        ]);

        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            // pake original name
            // Log::info('File info', [
            //     'original_name' => $file->getClientOriginalName(),
            //     'mime_type' => $file->getMimeType(),
            //     'extension' => $file->getClientOriginalExtension(),
            //     'is_valid' => $file->isValid(),
            // ]);

            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move(public_path('uploads'), $filename);

            $validated['bukti'] = $filename;
        }

        if (empty($validated['status_rekognisi'])) {
            unset($validated['status_rekognisi']);
        }

        $rekognisiDosen = Rekognisi::create($validated);

        return response()->json([
            'message' => 'Rekognisi dosen berhasil ditambahkan',
            'data' => $rekognisiDosen
        ], 201);
    }
    public function show($id)
    {
        $rekognisi = Rekognisi::with([
            'dosen',
            'kolaborator.dosenKolaborator',
            'komentar.admin',
            'files'
        ])->findOrFail($id);

        return response()->json($rekognisi);

    }
    public function destroy(Request $request, $id)
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
    public function tolak(Request $request, $id)
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
    public function statistikRekognisi()
    {
        $statistik = [
            'pending' => Rekognisi::where('status_rekognisi', 'pending')->count(),
            'disetujui' => Rekognisi::where('status_rekognisi', 'disetujui')->count(),
            'ditolak' => Rekognisi::where('status_rekognisi', 'ditolak')->count(),
        ];

        return response()->json([
            'message' => 'Statistik rekognisi berhasil diambil',
            'data' => $statistik
        ]);
    }
    public function beriUmpanBalik(Request $request, $id)
    {
        $rekognisi = Rekognisi::findOrFail($id);

        if ($rekognisi->status_rekognisi !== 'pending') {
            return response()->json([
                'message' => 'Umpan balik hanya dapat diberikan pada rekognisi yang berstatus "pending".'
            ], 403);
        }
        $request->validate([
            'id_admin_umpan_balik' => 'required|integer|exists:admins,id',
            'umpan_balik' => 'required|string|max:1000',
        ]);

        // Simpan umpan balik
        $rekognisi->update([
            'id_admin_umpan_balik' => $request->id_admin_umpan_balik,
            'umpan_balik' => $request->umpan_balik,
        ]);

        return response()->json([
            'message' => 'Umpan balik berhasil diberikan.'
        ]);
    }
    public function rekognisiPerluValidasi()
    {
        $jumlah = Rekognisi::where('status_rekognisi', 'pending')->count();

        return response()->json([
            'message' => 'Jumlah permohonan rekognisi yang perlu divalidasi.',
            'jumlah' => $jumlah
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
    public function terima(Request $request, $id)
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

}
