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
        Schema::create('book_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')
                ->constrained()->cascadeOnDelete();
            $table->string('isbn')->nullable();
            $table->string('publisher')->nullable();
            $table->integer('total_pages')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_metadata');
    }
};
