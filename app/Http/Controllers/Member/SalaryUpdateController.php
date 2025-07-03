<?php
namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\SalaryUpdateRequest;

class SalaryUpdateController extends Controller
{
public function store(Request $request)
{
    $member = auth('member')->user();

    // Cek apakah sudah ada pengajuan dengan status "pending"
    $existing = SalaryUpdateRequest::where('member_id', $member->id)
        ->where('status', 'pending')
        ->first();

    if ($existing) {
        return response()->json([
            'message' => 'Kamu sedang memiliki pengajuan salary update yang masih pending. Mohon menunggu hingga diproses.'
        ], 400);
    }

    // Validasi input
    $data = $request->validate([
        'new_salary' => 'required|numeric|min:0',
        'slip_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
    ]);

    // Simpan file
    $path = $request->file('slip_file')->store('salary_slips', 'public');
    $url = asset("https://ta.sunnysideup.my.id/storage/app/public/{$path}");
    // Simpan pengajuan
    \App\Models\SalaryUpdateRequest::create([
        'member_id' => $member->id,
        'new_salary' => $data['new_salary'],
        'slip_file' => $url,
        'status' => 'pending', // pastikan ada kolom status di tabel
    ]);

    return response()->json([
        'message' => 'Pengajuan pembaruan slip gaji berhasil dikirim.'
    ]);
}

    public function myRequests()
    {
        $member = auth('member')->user();

        return SalaryUpdateRequest::where('member_id', $member->id)->latest()->get();
    }
}
