<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingTransaction extends Model
{
    protected $fillable = [
        'member_id', 'type', 'amount', 'transaction_date',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
