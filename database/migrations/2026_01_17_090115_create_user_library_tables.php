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

        // Favorites
        Schema::create('user_favorite_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('publication_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'publication_id']);
        });

        // History
        Schema::create('user_read_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('publication_id')->constrained()->onDelete('cascade');
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'publication_id']);
        });

        // Saved
        Schema::create('user_saved_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('publication_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'publication_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_library_tables');
    }
};
