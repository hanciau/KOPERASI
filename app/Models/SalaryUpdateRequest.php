<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryUpdateRequest extends Model
{
    protected $fillable = [
        'member_id',
        'new_salary',
        'slip_file',
        'status',
        'admin_note',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
