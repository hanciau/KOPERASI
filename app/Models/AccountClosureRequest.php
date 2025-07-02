<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountClosureRequest extends Model
{
    protected $fillable = [
        'member_id',
        'status',
        'reason',
        'admin_reason',
        'handover_proof',
        'final_balance',
        'interview_sent_at',
        'approved_at',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
