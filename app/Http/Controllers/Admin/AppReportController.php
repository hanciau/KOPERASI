<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppReport;
use Illuminate\Support\Facades\Storage;

class AppReportController extends Controller
{
    /**
     * Menampilkan daftar semua laporan aplikasi.
     */
    public function index()
    {
        $reports = AppReport::latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $reports,
        ]);
    }

    /**
     * Mengunduh file laporan berdasarkan ID.
     */
    public function download($id)
    {
        $report = AppReport::findOrFail($id);

        if (!Storage::exists($report->file_path)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File laporan tidak ditemukan di storage.',
            ], 404);
        }

        return Storage::download($report->file_path);
    }
}
