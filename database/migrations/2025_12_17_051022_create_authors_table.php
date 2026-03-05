<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // ✅ Semua nullable — jika linked ke user, baca dari tabel users
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('affiliation')->nullable();
            $table->text('bio')->nullable();
            $table->string('photo_path')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
