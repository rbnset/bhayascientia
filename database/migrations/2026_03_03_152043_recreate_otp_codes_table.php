<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop dulu jika sudah ada (struktur lama)
        Schema::dropIfExists('otp_codes');

        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');
            $table->string('code');          // bcrypt hash dari 6 digit OTP
            $table->timestamp('expires_at'); // kadaluarsa 10 menit
            $table->boolean('is_used')->default(false);
            $table->timestamps();

            // Index untuk query cepat
            $table->index(['user_id', 'is_used']);
            $table->index('expires_at'); // untuk cleanup job nanti
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
