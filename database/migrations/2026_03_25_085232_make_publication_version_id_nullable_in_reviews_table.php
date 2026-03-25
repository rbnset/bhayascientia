<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Ubah publication_version_id menjadi nullable
            // agar review opini tanpa manuskrip bisa disimpan
            $table->unsignedBigInteger('publication_version_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('publication_version_id')
                ->nullable(false)
                ->change();
        });
    }
};
