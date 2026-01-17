<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_read_publications', function (Blueprint $table) {
            // ✅ Ubah last_read_at menjadi timestamp
            $table->timestamp('last_read_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('user_read_publications', function (Blueprint $table) {
            $table->string('last_read_at')->nullable()->change();
        });
    }
};
