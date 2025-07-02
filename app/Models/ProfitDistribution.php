<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfitDistribution extends Model
{
    protected $fillable = ['member_id', 'financial_report_id', 'amount', 'status'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function financialReport()
    {
        return $this->belongsTo(FinancialReport::class);
    }
}
