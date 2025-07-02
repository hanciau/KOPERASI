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
Schema::create('loan_installments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('loan_application_id')->constrained()->onDelete('cascade');
    $table->integer('month_number');
    $table->date('due_date');
    $table->decimal('amount', 15, 2);
    $table->enum('status', ['unpaid', 'waiting', 'paid'])->default('unpaid');
    $table->string('payment_proof')->nullable();
    $table->string('admin_note')->nullable(); // bukti mutasi rekening atau catatan
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_installments');
    }
};
