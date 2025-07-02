<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_members_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// database/migrations/xxxx_xx_xx_xxxxxx_create_members_table.php
// ...
public function up(): void
{
    Schema::create('members', function (Blueprint $table) {
        $table->id();
        $table->string('email')->unique();
        $table->string('nip', 50)->nullable();
        $table->string('nik', 50)->nullable();
        $table->string('jabatan', 100)->nullable();
        $table->date('joined_at')->nullable();
        $table->enum('status', ['pending', 'active', 'closed'])->default('pending');
        $table->bigInteger('current_balance')->unsigned()->default(0);
        $table->bigInteger('salary')->unsigned()->nullable();
        $table->dateTime('slip_verified_at')->nullable();
        $table->string('password')->nullable(); // Tambahkan kolom password, bisa null atau string random hash
        $table->rememberToken();
        $table->timestamps();
    });
}
// ...

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};