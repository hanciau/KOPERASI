<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_application_id',
        'month_number',
        'due_date',
        'amount',
        'status',
        'payment_proof',
        'admin_note',
        'paid_at',
    ];

    public function loanApplication()
    {
        return $this->belongsTo(LoanApplication::class);
    }

    public function manualPayments()
    {
        return $this->belongsToMany(ManualPayment::class, 'manual_payment_installment');
    }
}
