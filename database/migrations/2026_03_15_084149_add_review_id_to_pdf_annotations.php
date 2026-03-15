<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pdf_annotations', function (Blueprint $table) {
            // Nullable agar annotasi publik (publication_id only) tetap valid
            $table->unsignedBigInteger('review_id')
                ->nullable()
                ->after('publication_id')
                ->index();

            $table->foreign('review_id')
                ->references('id')
                ->on('reviews')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pdf_annotations', function (Blueprint $table) {
            $table->dropForeign(['review_id']);
            $table->dropColumn('review_id');
        });
    }
};
