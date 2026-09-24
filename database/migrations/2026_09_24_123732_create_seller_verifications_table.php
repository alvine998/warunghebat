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
        Schema::create('seller_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            // NIK KTP 16 digit + nama sesuai KTP.
            $table->string('nik', 16);
            $table->string('full_name', 120);
            // Bukti kepemilikan: foto KTP, selfie pegang KTP, foto depan warung.
            $table->string('ktp_path', 255);
            $table->string('selfie_path', 255);
            $table->string('storefront_path', 255);
            // pending = menunggu admin, verified = boleh jualan, rejected = perbaiki & kirim ulang.
            $table->string('status', 20)->default('pending');
            $table->string('rejection_reason', 1000)->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_verifications');
    }
};
