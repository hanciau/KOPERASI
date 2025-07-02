<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_member_requests_table.php
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
        Schema::create('member_requests', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique(); // Unique karena satu email hanya bisa punya satu request pending
            $table->string('nip', 50);
            $table->string('nik', 50);
            $table->string('jabatan', 100);
            $table->string('slip_gaji_path'); // Path penyimpanan file slip gaji
            $table->unsignedInteger('attempts')->default(1); // Batasan 3x pengajuan
            $table->enum('status', ['pending','approved','interview','rejected'])->default('pending');
            $table->dateTime('interview_sent_at')->nullable(); // Waktu notifikasi wawancara dikirim
            $table->boolean('interview_completed')->default(false); // Apakah wawancara sudah dilakukan
            $table->text('reason')->nullable(); // Alasan penolakan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_requests');
    }
};