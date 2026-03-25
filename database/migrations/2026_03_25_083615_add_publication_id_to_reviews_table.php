<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('publication_id')
                ->nullable()
                ->after('publication_version_id');

            $table->foreign('publication_id')
                ->references('id')
                ->on('publications')
                ->nullOnDelete();
        });

        // Backfill: isi publication_id dari publication_versions
        // untuk review yang sudah ada (yang punya publication_version_id)
        DB::statement('
            UPDATE reviews r
            INNER JOIN publication_versions pv ON pv.id = r.publication_version_id
            SET r.publication_id = pv.publication_id
            WHERE r.publication_version_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['publication_id']);
            $table->dropColumn('publication_id');
        });
    }
};
