<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ManualPaymentInstallment extends Pivot
{
    protected $table = 'manual_payment_installment';

    protected $fillable = [
        'manual_payment_id',
        'loan_installment_id',
    ];
}
