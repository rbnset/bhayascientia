<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambahkan kolom arrow_x1/y1/x2/y2 ke tabel pdf_annotations.
 *
 * Cara pakai:
 *   php artisan make:migration add_arrow_columns_to_pdf_annotations --table=pdf_annotations
 *   Ganti isi file yang dibuat dengan file ini, lalu:
 *   php artisan migrate
 *
 * Kolom ini menyimpan titik awal/akhir shape arrow & line secara eksplisit,
 * sehingga JS tidak perlu baca ulang dari path_points setiap render.
 * Tanpa kolom ini anotasi arrow/line tidak muncul setelah reload.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pdf_annotations', function (Blueprint $table) {
            if (! Schema::hasColumn('pdf_annotations', 'arrow_x1')) {
                $table->float('arrow_x1')->nullable()->after('fill_opacity');
            }
            if (! Schema::hasColumn('pdf_annotations', 'arrow_y1')) {
                $table->float('arrow_y1')->nullable()->after('arrow_x1');
            }
            if (! Schema::hasColumn('pdf_annotations', 'arrow_x2')) {
                $table->float('arrow_x2')->nullable()->after('arrow_y1');
            }
            if (! Schema::hasColumn('pdf_annotations', 'arrow_y2')) {
                $table->float('arrow_y2')->nullable()->after('arrow_x2');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pdf_annotations', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['arrow_x1', 'arrow_y1', 'arrow_x2', 'arrow_y2'],
                fn($col) => Schema::hasColumn('pdf_annotations', $col)
            ));
        });
    }
};
