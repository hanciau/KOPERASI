<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Loanapplication;
use App\Models\MemberRequest;
use Illuminate\Http\JsonResponse;

class AdminSummaryController extends Controller
{
    /**
     * Return summary data for the admin dashboard.
     */
    public function index(): JsonResponse
    {
        try {
            $activeMembers = Member::where('status', 'active')->count();
            $pendingMembers = MemberRequest::where('status', 'pending')->count();
            $interviewMembers = MemberRequest::where('status', 'interview')->count();

            $totalBalance = member::sum('current_balance');

            $totalLoanAmount = Loanapplication::sum('amount');
            $totalWithFee = Loanapplication::sum('total_with_fee');

            return response()->json([
                'active_members' => $activeMembers,
                'pending_members' => $pendingMembers,
                'interview_members' => $interviewMembers,
                'total_balance' => (int) $totalBalance,
                'total_loan_amount' => (int) $totalLoanAmount,
                'total_with_fee' => (int) $totalWithFee,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal memuat data ringkasan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
