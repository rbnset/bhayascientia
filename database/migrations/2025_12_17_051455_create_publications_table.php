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
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_type_id')
                ->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('abstract')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->foreignId('category_id')->nullable()
                ->constrained()->nullOnDelete();
            $table->foreignId('method_id')->nullable()
                ->constrained()->nullOnDelete();
            $table->enum('status', [
                'draft',
                'submitted',
                'in_review',
                'revision_required',
                'accepted',
                'rejected',
                'published'
            ])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
