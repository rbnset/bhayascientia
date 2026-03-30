<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // ── Tambah kolom slug (nullable dulu) ────────────────────────────
        Schema::table('authors', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('orcid_id');
        });

        // ── Isi slug untuk SEMUA author (termasuk soft-deleted) ──────────
        $authors = DB::table('authors')
            ->leftJoin('users', 'authors.user_id', '=', 'users.id')
            ->select(
                'authors.id',
                DB::raw("COALESCE(NULLIF(TRIM(users.name), ''), NULLIF(TRIM(authors.name), ''), 'author') as resolved_name")
            )
            ->get(); // ← tanpa whereNull deleted_at → include soft-deleted

        foreach ($authors as $author) {
            $base = Str::slug($author->resolved_name);
            if (empty($base)) $base = 'author';

            $slug    = $base;
            $counter = 1;

            // Pastikan slug unik di seluruh tabel (termasuk soft-deleted)
            while (
                DB::table('authors')
                ->where('slug', $slug)
                ->where('id', '!=', $author->id)
                ->exists()
            ) {
                $slug = $base . '-' . $counter++;
            }

            DB::table('authors')
                ->where('id', $author->id)
                ->update(['slug' => $slug]);
        }

        // ── Pastikan tidak ada NULL tersisa sebelum NOT NULL ─────────────
        // Fallback darurat: isi dengan 'author-{id}' jika masih NULL
        DB::table('authors')
            ->whereNull('slug')
            ->get()
            ->each(function ($row) {
                DB::table('authors')
                    ->where('id', $row->id)
                    ->update(['slug' => 'author-' . $row->id]);
            });

        // ── Baru jadikan NOT NULL ────────────────────────────────────────
        Schema::table('authors', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
