<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('publication_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('page');

            // Tool type: highlight | freehand | comment | sticky | shape
            $table->string('type', 20);

            // Color: yellow | green | red | blue | orange | black | white
            $table->string('color', 20)->default('yellow');

            // Bounding rect (normalized 0–1 relative to CSS canvas size)
            $table->float('rect_x')->nullable();
            $table->float('rect_y')->nullable();
            $table->float('rect_w')->nullable();
            $table->float('rect_h')->nullable();

            // For highlight: selected text
            $table->text('selected_text')->nullable();

            // For comment / sticky note
            $table->text('comment')->nullable();

            // For freehand: JSON array of {x,y} points (normalized)
            $table->json('path_points')->nullable();

            // For shape: 'arrow' | 'rect' | 'ellipse'
            $table->string('shape_type', 20)->nullable();

            // Stroke width (px at scale=1)
            $table->float('stroke_width')->default(2.0);

            // Fill opacity for shapes
            $table->float('fill_opacity')->default(0.0);

            $table->timestamps();

            $table->index(['user_id', 'publication_id', 'page']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_annotations');
    }
};
