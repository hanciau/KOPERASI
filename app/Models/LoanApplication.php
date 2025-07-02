<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'amount',
        'tenor',
        'purpose',
        'fee_percentage',
        'total_with_fee',
        'status',
        'reason',
        'interview_scheduled_at',
        'approved_at',
        'rejected_at',
        'transfer_proof',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function installments()
    {
        return $this->hasMany(LoanInstallment::class);
    }

    public function manualPayments()
    {
        return $this->hasMany(ManualPayment::class);
    }
}
