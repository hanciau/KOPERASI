<?php
// app/Models/AppReport.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppReport extends Model
{
    protected $fillable = ['file_path', 'bulan', 'description'];
}
