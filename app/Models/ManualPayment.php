<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_application_id',
        'member_id',
        'payment_date',
        'payment_proof',
        'admin_note',
        'status',
        'confirmed_at',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function loanApplication()
    {
        return $this->belongsTo(LoanApplication::class);
    }

    public function installments()
    {
        return $this->belongsToMany(LoanInstallment::class, 'manual_payment_installment');
    }
}
