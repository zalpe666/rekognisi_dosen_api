<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Rekognisi;
use App\Models\RekognisiFile;
use App\Models\RekognisiKolabolator;
use \Mpdf\Mpdf;
use Carbon\Carbon;

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
    public function rekognisiSaya(Request $request)
    {
        $id = auth()->user()->id;
        $status = $request->status;
        $tahun = $request->tahun;
        $type_rekognisi_id = $request->type_rekognisi_id;

        $rekognisi = Rekognisi::with(['dosen', 'jenisRekognisi'])

            ->where(function ($query) use ($id) {
                $query->where('id_dosen', $id)
                    // Perbaikan di sini: 'kolabolator' seharusnya 'kolaborator'
                    ->orWhereHas('kolabolator', function ($subQuery) use ($id) {
                        // Perbaikan di sini: 'id_dosen_kolabolator' seharusnya 'id_dosen_kolabolator'
                        $subQuery->where('id_dosen_kolabolator', $id);
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

    public function showRekognisi($id) // $id adalah id rekognisi
    {
        $userId = auth()->user()->id;

        $rekognisi = Rekognisi::with([
            'dosen',
            'kolabolator.dosenKolabolator',
            'komentar.admin',
            'files'
        ])->find($id);

        if (!$rekognisi) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $isOwner = $rekognisi->id_dosen === $userId;

        $isCollaborator = $rekognisi->kolabolator?->contains(function ($kolabolator) use ($userId) {
            return $kolabolator->id_dosen_kolabolator === $userId;
        }) ?? false;

        if (!($isOwner || $isCollaborator)) {
            return response()->json(['message' => 'Akses ditolak. Anda bukan pemilik atau kolabolator rekognisi ini.'], 403);
        }

        return response()->json($rekognisi);
    }

    public function searchRekognisi(Request $request)
    {
        /** @var \App\Models\Dosen $user */
        $id = auth()->user()->id;

        $keyword = $request->keyword;
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
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->Where('judul_penelitian', 'like', '%' . $keyword . '%')
                        ->orWhere('judul_penghargaan', 'like', '%' . $keyword . '%')
                        ->orWhere('judul_pengabdian', 'like', '%' . $keyword . '%')
                        ->orWhere('judul_publikasi', 'like', '%' . $keyword . '%')
                        ->orWhere('judul_karya_hki', 'like', '%' . $keyword . '%');
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
    public function storeFile(Request $request)
    {
        $request->validate([
            'rekognisi_dosen_id' => 'required|exists:rekognisi_dosen,id',
            'file' => 'required|file|mimes:pdf|max:5120',
            'nama_file' => 'required|string|max:255',
        ]);

        $userId = auth()->user()->id;

        $rekognisi = Rekognisi::with('kolabolator')
            ->where('id', $request->rekognisi_dosen_id)
            ->where(function ($query) use ($userId) {
                $query->where('id_dosen', $userId)
                    ->orWhereHas('kolabolator', function ($subQuery) use ($userId) {
                        $subQuery->where('id_dosen_kolabolator', $userId);
                    });
            })
            ->first();

        if (!$rekognisi) {
            return response()->json([
                'message' => 'Akses ditolak. Anda bukan pemilik atau kolaborator rekognisi ini.'
            ], 403);
        }

        $fileCount = RekognisiFile::where('rekognisi_dosen_id', $request->rekognisi_dosen_id)->count();

        if ($fileCount >= 3) {
            return response()->json([
                'message' => 'Maksimal 3 file diperbolehkan untuk satu rekognisi.'
            ], 422);
        }

        $file = $request->file('file');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads'), $filename);

        $rekognisiFile = RekognisiFile::create([
            'rekognisi_dosen_id' => $request->rekognisi_dosen_id,
            'id_dosen' => $userId,
            'file' => $filename,
            'nama_file' => $request->nama_file,
        ]);

        return response()->json([
            'message' => 'Rekognisi dosen berhasil ditambahkan',
            'data' => $rekognisiFile,
            'url' => asset('uploads/' . $filename),
        ], 201);
    }
    public function tambahKolabolator(Request $request)
    {
        $request->validate([
            'rekognisi_dosen_id' => 'required|exists:rekognisi_dosen,id',
            'id_dosen_kolabolator' => 'required|exists:dosens,id',
        ]);

        $kolaborator = RekognisiKolabolator::create([
            'rekognisi_dosen_id' => $request->rekognisi_dosen_id,
            'id_dosen_kolaborator' => $request->id_dosen_kolabolator,
        ]);

        return response()->json([
            'message' => 'Kolaborator berhasil ditambahkan',
            'data' => $kolaborator,
        ], 201);
    }
    public function store(Request $request)
    {
        $user = auth()->user(); // ambil dosen yang login

        $validated = $request->validate([

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
            'status_rekognisi' => 'nullable|string|in:disimpan,diajukan',
        ]);
        $validated['id_dosen'] = $user->id;

        // Jika tidak ada status_rekognisi, biarkan default di database
        if (empty($validated['status_rekognisi'])) {
            unset($validated['status_rekognisi']);
        }

        $rekognisiDosen = Rekognisi::create($validated);

        $message = (isset($validated['status_rekognisi']) && $validated['status_rekognisi'] === 'diajukan')
            ? 'Rekognisi dosen berhasil diajukan'
            : 'Rekognisi dosen berhasil disimpan';

        return response()->json([
            'message' => $message,
            'data' => $rekognisiDosen
        ], 201);
    }
    public function buatSuratTugas(Request $request)
    {
        $data = $request->validate([
            'nama_lengkap' => 'required|string',
            'nip' => 'required|string',
            'pangkat' => 'required|string',
            'jabatan' => 'required|string',
            'unit_kerja' => 'required|string',
            'tempat_kegiatan' => 'required|string',
            'tanggal_kegiatan' => 'required|date',
            'keterangan_acara' => 'required|string',
            'penugasan' => 'required|string',
        ]);

        $mpdf = new Mpdf();

        $html = '
        <div style="display: flex; align-items: flex-start;">
            <div style="width: 100px;">
                <img src="https://static.wikia.nocookie.net/logopedia/images/a/ad/Rumah_Sakit_YARSI.png/revision/latest/scale-to-width-down/1200?cb=20191109072420" style="height: 60px;">
            </div>
        <div style="flex: 1; padding-left: 20px; line-height: 1.2; text-align: center;">
            <h3 style="margin: 0; font-size: 20px;">UNIVERSITAS YARSI</h3>
            <p style="margin: 2px 0; font-size: 12px;">Jl. Letjen Suprapto, Cempaka Putih, Jakarta Pusat</p>
            <p style="margin: 2px 0; font-size: 12px;">Telepon: (021) 4206675 | Website: www.yarsi.ac.id</p>
        </div>
        <hr style="margin-top: 10px; margin-bottom: 20px;">

        <h2 style="text-align:center;">SURAT TUGAS</h2>

        <p>Yang bertanda tangan di bawah ini:</p>
        <p>
            Nama: <strong>' . $data['nama_lengkap'] . '</strong><br>
            NIP: <strong>' . $data['nip'] . '</strong><br>
            Pangkat/Golongan: <strong>' . $data['pangkat'] . '</strong><br>
            Jabatan: <strong>' . $data['jabatan'] . '</strong><br>
            Unit Kerja: <strong>' . $data['unit_kerja'] . '</strong>
        </p>

        <p>Dengan ini menugaskan kepada:</p>
        <p>
            Nama: <strong>' . $data['penugasan'] . '</strong>
        </p>

        <p>
            Untuk mengikuti kegiatan <strong>' . $data['keterangan_acara'] . '</strong> yang diselenggarakan di <strong>' . $data['tempat_kegiatan'] . '</strong> pada tanggal <strong>' . Carbon::parse($data['tanggal_kegiatan'])->translatedFormat('d F Y') . '</strong>.
        </p>

        <p>Demikian surat ini dibuat untuk digunakan sebagaimana mestinya.</p>

        <br><br>
        <p style="text-align:right;">
            Jakarta, ' . Carbon::now()->translatedFormat('d F Y') . '<br><br>
            <strong>' . $data['nama_lengkap'] . '</strong><br>
            ' . $data['jabatan'] . '
        </p>
        ';

        $mpdf->WriteHTML($html);
        return $mpdf->Output('surat_tugas.pdf', 'I');
    }
    public function addKolabolator(Request $request)
    {
        $request->validate([
            'rekognisi_dosen_id' => 'required|exists:rekognisi_dosen,id',
            'id_dosen_kolabolator' => 'required|exists:dosens,id',
        ]);

        $userId = auth()->user()->id;

        // Ambil data rekognisi
        $rekognisi = Rekognisi::with('kolabolator')
            ->where('id', $request->rekognisi_dosen_id)
            ->first();

        if (!$rekognisi) {
            return response()->json(['message' => 'Rekognisi tidak ditemukan'], 404);
        }

        // Pastikan yang menambahkan adalah pemilik rekognisi
        if ($rekognisi->id_dosen !== $userId) {
            return response()->json(['message' => 'Hanya pemilik rekognisi yang dapat menambahkan kolaborator'], 403);
        }

        $kolaboratorId = $request->id_dosen_kolabolator;

        // Cek apakah id kolaborator sama dengan pemilik
        if ($kolaboratorId == $rekognisi->id_dosen) {
            return response()->json(['message' => 'Tidak dapat menambahkan dosen pemilik sebagai kolaborator'], 422);
        }

        // Cek apakah kolaborator sudah ada
        $sudahAda = RekognisiKolabolator::where('rekognisi_dosen_id', $rekognisi->id)
            ->where('id_dosen_kolaborator', $kolaboratorId)
            ->exists();

        if ($sudahAda) {
            return response()->json(['message' => 'Kolaborator sudah ditambahkan sebelumnya'], 422);
        }

        // Cek apakah jumlah kolaborator sudah 3
        $jumlahKolaborator = RekognisiKolabolator::where('rekognisi_dosen_id', $rekognisi->id)->count();
        if ($jumlahKolaborator >= 3) {
            return response()->json(['message' => 'Maksimal 3 dosen kolaborator diperbolehkan'], 422);
        }

        // Simpan kolaborator baru
        $kolaborator = RekognisiKolabolator::create([
            'rekognisi_dosen_id' => $rekognisi->id,
            'id_dosen_kolaborator' => $kolaboratorId,
        ]);

        return response()->json([
            'message' => 'Kolaborator berhasil ditambahkan',
            'data' => $kolaborator
        ], 201);
    }

}
