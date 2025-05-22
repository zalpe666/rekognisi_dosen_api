<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RekognisiFile;

class RekognisiFileController extends Controller
{
    public function store(Request $request)
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


}
