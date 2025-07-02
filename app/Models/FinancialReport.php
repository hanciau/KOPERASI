<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialReport extends Model
{
    protected $fillable = ['year', 'file_path', 'total_income', 'description'];

    public function profitDistributions()
    {
        return $this->hasMany(ProfitDistribution::class);
    }
}
