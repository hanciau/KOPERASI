<?php

// database/migrations/xxxx_xx_xx_create_account_closure_requests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountClosureRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('account_closure_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'interview', 'rejected', 'approved'])->default('pending');
            $table->text('reason')->nullable(); // alasan penutupan dari member
            $table->text('admin_reason')->nullable(); // alasan dari admin
            $table->string('handover_proof')->nullable(); // bukti serah simpanan (admin)
            $table->decimal('final_balance', 15, 2)->nullable(); // saldo akhir
            $table->timestamp('interview_sent_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_closure_requests');
    }
}
