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
        Schema::create('author_publication', function (Blueprint $table) {
            $table->foreignId('publication_id')
                ->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')
                ->constrained()->cascadeOnDelete();
            $table->unsignedInteger('order')->default(1);
            $table->boolean('is_corresponding')->default(false);
            $table->primary(['publication_id', 'author_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('author_publication');
    }
};
