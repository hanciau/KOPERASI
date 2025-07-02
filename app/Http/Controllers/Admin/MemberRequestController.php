<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MemberRequest;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Mail\MemberStatusUpdateMail;
use App\Mail\InterviewInvitationMail;

class MemberRequestController extends Controller
{
    // Menampilkan semua pengajuan registrasi (dengan filter optional)
    public function index(Request $request)
    {
        $query = MemberRequest::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%$search%")
                    ->orWhere('nama', 'like', "%$search%")
                    ->orWhere('nip', 'like', "%$search%")
                    ->orWhere('nik', 'like', "%$search%")
                    ->orWhere('jabatan', 'like', "%$search%")
                    ->orWhere('reason', 'like', "%$search%");
            });
        }

        return response()->json($query->latest()->get());
    }

    // Menampilkan detail satu pengajuan berdasarkan ID
    public function show($id)
    {
        $request = MemberRequest::findOrFail($id);

        return response()->json([
            'id' => $request->id,
            'email' => $request->email,
            'nama' => $request->nama,
            'nip' => $request->nip,
            'nik' => $request->nik,
            'jabatan' => $request->jabatan,
            'status' => $request->status,
            'reason' => $request->reason,
            'interview_sent_at' => $request->interview_sent_at,
            'interview_completed' => $request->interview_completed,
            'salary' => $request->salary,
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
        ]);
    }

    // Mengirim notifikasi interview dan ubah status
    public function sendInterview($id)
    {
        $request = MemberRequest::findOrFail($id);

        $request->status = 'interview';
        $request->interview_sent_at = now();
        $request->save();

        // Kirim email
        $messageText = "Pengajuan Anda telah dijadwalkan untuk interview. Silakan hadir dalam 5 hari ke koperasi.";
        Mail::to($request->email)->send(new InterviewInvitationMail($request, $messageText));

        return response()->json(['message' => 'Notifikasi interview telah dikirim.']);
    }

    // Menolak pengajuan registrasi
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string'
        ]);

        $memberRequest = MemberRequest::findOrFail($id);
        $memberRequest->status = 'rejected';
        $memberRequest->reason = $validated['reason'];
        $memberRequest->save();

        // Kirim email
        $messageText = "Mohon maaf, pengajuan Anda ditolak. Alasan: " . $validated['reason'];
        Mail::to($memberRequest->email)->send(new MemberStatusUpdateMail($memberRequest, $messageText));

        return response()->json(['message' => 'Pengajuan telah ditolak.']);
    }

    // Menyelesaikan interview dan menyetujui jadi member aktif
    // Menyelesaikan interview dan menyetujui jadi member aktif
    public function completeInterview($id)
    {
        DB::beginTransaction();

        try {
            $request = MemberRequest::findOrFail($id);

            if ($request->status !== 'interview') {
                return response()->json(['message' => 'Status belum interview.'], 400);
            }

            // Buat akun member baru + saldo awal Rp 50.000
            $member = Member::create([
                'email' => $request->email,
                'nama' => $request->nama,
                'nip' => $request->nip,
                'nik' => $request->nik,
                'jabatan' => $request->jabatan,
                'joined_at' => now(),
                'status' => 'active',
                'salary' => $request->salary,
                'slip_verified_at' => now(),
                'current_balance' => 50000, // <- SALDO AWAL 50.000
            ]);

            // Update status request
            $request->status = 'approved';
            $request->interview_completed = true;
            $request->save();

            // Kirim email
            $messageText = "Selamat! Pengajuan Anda telah disetujui dan akun member telah dibuat. Anda menerima saldo awal Rp 50.000.";
            Mail::to($request->email)->send(new MemberStatusUpdateMail($request, $messageText));

            DB::commit();
            return response()->json(['message' => 'Interview selesai dan member telah disetujui.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyelesaikan interview: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyelesaikan interview.'], 500);
        }
    }

    // Menampilkan hanya status dari pengajuan
    public function showStatus($id)
    {
        $request = MemberRequest::findOrFail($id);
        return response()->json([
            'status' => $request->status,
            'reason' => $request->reason,
        ]);
    }
}
