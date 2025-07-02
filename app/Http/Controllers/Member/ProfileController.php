<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\SavingTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Ambil data profil member yang sedang login
     */
    public function getProfile(Request $request): JsonResponse
    {
        $member = $request->user('member');

        return response()->json([
            'member' => [
                'email' => $member->email,
                'nama' => $member->nama,
                'nip' => $member->nip,
                'nik' => $member->nik,
                'jabatan' => $member->jabatan,
                'joined_at' => $member->joined_at,
                'status' => $member->status,
                'current_balance' => $member->current_balance,
                'salary' => $member->salary,
                'slip_verified_at' => $member->slip_verified_at,
            ],
        ]);
    }

    /**
     * Ambil riwayat transaksi simpanan milik member
     */
public function getSavingTransactions(Request $request): JsonResponse
{
    $member = $request->user('member');

    $transactions = SavingTransaction::where('member_id', $member->id)
        ->orderBy('transaction_date', 'desc')
        ->get()
        ->map(function ($trx) {
            return [
                'id' => $trx->id,
                'member_id' => $trx->member_id,
                'amount' => (int) $trx->amount, 
                'type' => $trx->type,
                'transaction_date' => $trx->transaction_date,
            ];
        });

    return response()->json([
        'transactions' => $transactions,
    ]);
}

}
