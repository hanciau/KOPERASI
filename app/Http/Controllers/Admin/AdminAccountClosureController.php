<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountClosureRequest;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminAccountClosureController extends Controller
{
    public function index()
    {
        return AccountClosureRequest::with('member')->latest()->get();
    }

    public function approve($id, Request $request)
    {
        $closure = AccountClosureRequest::findOrFail($id);

        $data = $request->validate([
            'admin_reason' => 'required|string|min:10',
            'handover_proof' => 'required|image|max:2048',
        ]);

        $path = $request->file('handover_proof')->store('handover_proofs');

        $closure->update([
            'status' => 'approved',
            'admin_reason' => $data['admin_reason'],
            'handover_proof' => $path,
            'final_balance' => $closure->member->current_balance,
            'approved_at' => now(),
        ]);

        // Tutup akun member
        $closure->member->update(['status' => 'closed']);

        return response()->json(['message' => 'Penutupan akun disetujui dan status member telah ditutup.']);
    }

    public function reject($id)
    {
        $closure = AccountClosureRequest::findOrFail($id);
        $closure->update(['status' => 'rejected']);

        return response()->json(['message' => 'Permintaan penutupan akun telah ditolak.']);
    }
}
