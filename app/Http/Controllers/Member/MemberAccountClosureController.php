<?php
namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\AccountClosureRequest;
use App\Models\LoanInstallment;
use Illuminate\Http\Request;

class MemberAccountClosureController extends Controller
{
    public function submit(Request $request)
    {
        $member = auth('member')->user();

        // Cek tunggakan pinjaman
        $hasUnpaid = LoanInstallment::whereHas('loanApplication', function ($q) use ($member) {
            $q->where('member_id', $member->id);
        })->where('status', '!=', 'paid')->exists();

        if ($hasUnpaid) {
            return response()->json(['error' => 'Tidak dapat menutup akun. Anda masih memiliki tunggakan pinjaman.'], 422);
        }

        $data = $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        $closure = AccountClosureRequest::create([
            'member_id' => $member->id,
            'reason' => $data['reason'],
            'status' => 'interview',
            'interview_sent_at' => now(),
        ]);

        return response()->json([
            'message' => 'Permintaan penutupan akun telah diajukan. Harap datang ke koperasi sebelum 5 hari.',
            'data' => $closure,
        ]);
    }
}
