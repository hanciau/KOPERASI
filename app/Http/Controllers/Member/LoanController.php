<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoanApplication;
use App\Models\LoanInstallment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoanController extends Controller
{
    // [1] Cek limit pinjaman
public function limit()
{
    $member = auth('member')->user();
    $today = Carbon::today();
    $joinedAt = $member->joined_at ? Carbon::parse($member->joined_at) : null;
    $slipVerifiedAt = $member->slip_verified_at ? Carbon::parse($member->slip_verified_at) : null;

    $lamaGabung = $joinedAt ? $joinedAt->diffInMonths($today) : 0;
    $lamaSlip = $slipVerifiedAt ? $slipVerifiedAt->diffInMonths($today) : 0;

    $eligible = $lamaGabung >= 12 && $lamaSlip >= 1;
    $limit = $eligible ? $member->salary * 0.6 : 0;

    return response()->json([
        'salary' => $member->salary,
        'joined_months' => $lamaGabung,
        'last_slip_updated_months' => $lamaSlip,
        'eligible' => $eligible,
        'limit' => $limit,
    ]);
}




    // [2] Ajukan pinjaman baru
    public function store(Request $request)
    {
        $member = auth('member')->user();

        $hasActiveLoan = LoanApplication::where('member_id', $member->id)
            ->whereIn('status', ['approved', 'interview'])
            ->exists();

        if ($hasActiveLoan) {
            return response()->json(['message' => 'Anda masih memiliki pinjaman yang belum selesai.'], 403);
        }

        if (now()->diffInMonths($member->joined_at) < 12 || now()->diffInMonths($member->slip_verified_at) < 1) {
            return response()->json(['message' => 'Belum memenuhi syarat pinjaman.'], 403);
        }

        $max_limit = $member->salary * 0.6;

        $data = $request->validate([
            'amount' => "required|numeric|min:500000|max:$max_limit",
            'tenor' => 'required|in:10,11,12',
            'purpose' => 'required|in:konsumtif,produktif,multiguna',
            'description' => 'nullable|string',
        ]);

        $fee_percent = 0.10;
        $total = $data['amount'] + ($data['amount'] * $fee_percent);
        $monthly_amount = round($total / $data['tenor'], 2);

        $application = \App\Models\LoanApplication::create([
            'member_id' => $member->id,
            'amount' => $data['amount'],
            'tenor' => $data['tenor'],
            'total_amount' => $total,
            'purpose' => $data['purpose'],
            'description' => $data['description'],
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Pengajuan pinjaman dikirim. Menunggu konfirmasi admin.',
            'data' => $application,
        ]);
    }


    // [3] Lihat semua pinjaman member
    public function index()
    {
        $member = auth('member')->user();

        $loans = LoanApplication::with('installments')
            ->where('member_id', $member->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($loans);
    }

    // [4] Detail pinjaman dan cicilan
    public function show($id)
    {
        $member = auth('member')->user();

        $loan = LoanApplication::with('installments')
            ->where('member_id', $member->id)
            ->findOrFail($id);

        return response()->json($loan);
    }
}
