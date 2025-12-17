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
        Schema::create('publication_versions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('publication_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('pdf_file_path');

            $table->unsignedInteger('version_number')->default(1);

            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['publication_id', 'version_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publication_versions');
    }
};
