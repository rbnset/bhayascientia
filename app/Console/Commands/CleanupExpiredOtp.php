<?php

namespace App\Console\Commands;

use App\Models\OtpCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredOtp extends Command
{
    protected $signature   = 'otp:cleanup';
    protected $description = 'Hapus semua OTP yang sudah kadaluarsa atau sudah dipakai';

    public function handle(): void
    {
        $deleted = OtpCode::where(function ($query) {
            $query->where('expires_at', '<', now())  // sudah kadaluarsa
                ->orWhere('is_used', true);         // sudah dipakai
        })->delete();

        Log::info("OTP Cleanup: {$deleted} record dihapus.", ['time' => now()]);

        $this->info("✅ {$deleted} OTP kadaluarsa berhasil dihapus.");
    }
}
