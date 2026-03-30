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
        Schema::table('authors', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('orcid_id');
        });

        // ── Isi slug untuk semua author yang sudah ada ──────────────────
        // Ambil dari users.name jika linked, fallback ke authors.name
        $authors = DB::table('authors')
            ->leftJoin('users', 'authors.user_id', '=', 'users.id')
            ->select(
                'authors.id',
                DB::raw('COALESCE(users.name, authors.name, "author") as resolved_name')
            )
            ->whereNull('authors.deleted_at')
            ->get();

        foreach ($authors as $author) {
            $base = Str::slug($author->resolved_name);
            if (empty($base)) $base = 'author';

            $slug      = $base;
            $counter   = 1;

            // Pastikan slug unik
            while (DB::table('authors')->where('slug', $slug)->where('id', '!=', $author->id)->exists()) {
                $slug = $base . '-' . $counter++;
            }

            DB::table('authors')->where('id', $author->id)->update(['slug' => $slug]);
        }

        // Setelah semua terisi, jadikan NOT NULL
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
