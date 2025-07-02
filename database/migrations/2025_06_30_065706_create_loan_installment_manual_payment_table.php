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
Schema::create('loan_installment_manual_payment', function (Blueprint $table) {
    $table->id();
    $table->foreignId('manual_payment_id')->constrained()->onDelete('cascade');
    $table->foreignId('loan_installment_id')->constrained()->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_installment_manual_payment');
    }
};
