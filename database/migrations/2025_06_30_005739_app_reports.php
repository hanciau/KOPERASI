<?php
// database/migrations/xxxx_xx_xx_create_app_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AppReports extends Migration
{
    public function up()
    {
        Schema::create('app_reports', function (Blueprint $table) {
            $table->id();
            $table->string('file_path'); // path ke file PDF
            $table->string('year'); // tahun laporan
            $table->string('description')->nullable(); // deskripsi singkat
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('app_reports');
    }
}
