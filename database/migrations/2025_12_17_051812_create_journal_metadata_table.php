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
        Schema::create('journal_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')
                ->constrained()->cascadeOnDelete();
            $table->string('journal_name');
            $table->string('issn')->nullable();
            $table->integer('volume')->nullable();
            $table->integer('issue')->nullable();
            $table->year('year')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_metadata');
    }
};
