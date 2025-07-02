<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('loan_applications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('member_id')->constrained()->onDelete('cascade');
    $table->decimal('amount', 15, 2);
    $table->integer('tenor'); // dalam bulan
    $table->decimal('monthly_installment', 15, 2);
    $table->decimal('total_with_fee', 15, 2);
    $table->enum('purpose', ['konsumtif', 'produktif', 'multi_guna']);
    $table->enum('status', ['pending', 'interview', 'approved', 'rejected'])->default('pending');
    $table->string('reject_reason')->nullable();
    $table->timestamp('interview_at')->nullable();
    $table->timestamp('approved_at')->nullable();
    $table->string('disbursement_proof')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};
