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
        Schema::table('users', function (Blueprint $table) {
            $table->string('orcid_id', 19)
                ->nullable()
                ->unique()
                ->after('facebook_id')
                ->comment('Format: 0000-0000-0000-0000');

            $table->timestamp('orcid_verified_at')
                ->nullable()
                ->after('orcid_id')
                ->comment('Kapan ORCID diverifikasi via OAuth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['orcid_id', 'orcid_verified_at']);
        });
    }
};
