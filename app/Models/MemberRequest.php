<?php
// app/Models/MemberRequest.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'nama',
        'nip',
        'nik',
        'jabatan',
        'slip_gaji_path',
        'salary',
        'attempts',
        'status',
        'interview_sent_at',
        'interview_completed',
        'reason',
    ];

    protected $casts = [
        'interview_sent_at' => 'datetime',
        'interview_completed' => 'boolean',
    ];
}