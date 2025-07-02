<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalaryUpdateRequest;
use App\Models\Member;
use Illuminate\Http\Request;

class AdminSalaryUpdateController extends Controller
{
    public function index()
    {
        return SalaryUpdateRequest::with('member')->latest()->get();
    }

    public function approve($id)
    {
        $request = SalaryUpdateRequest::findOrFail($id);

        $member = $request->member;
        $member->salary = $request->new_salary;
        $member->slip_verified_at = now();
        $member->save();

        $request->status = 'approved';
        $request->save();

        return response()->json(['message' => 'Slip gaji berhasil diperbarui.']);
    }

    public function reject(Request $request, $id)
    {
        $data = $request->validate([
            'admin_note' => 'required|string',
        ]);

        $req = SalaryUpdateRequest::findOrFail($id);
        $req->status = 'rejected';
        $req->admin_note = $data['admin_note'];
        $req->save();

        return response()->json(['message' => 'Pengajuan slip gaji ditolak.']);
    }
}
