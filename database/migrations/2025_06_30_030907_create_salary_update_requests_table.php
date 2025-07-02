<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryUpdateRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('salary_update_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->decimal('new_salary', 15, 2);
            $table->string('slip_file'); // path ke file slip
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable(); // alasan tolak jika ada
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_update_requests');
    }
}
