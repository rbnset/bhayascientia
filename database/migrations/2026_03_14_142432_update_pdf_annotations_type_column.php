<?php
// CARA PAKAI:
// php artisan make:migration update_pdf_annotations_type_column --table=pdf_annotations
// Lalu GANTI isi file yang dibuat dengan kode di bawah ini.
// Kemudian: php artisan migrate

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah kolom type agar mendukung semua tool baru
        // Juga tambah shape_type baru: line
        Schema::table('pdf_annotations', function (Blueprint $table) {
            // Ubah type menjadi string 30 (lebih panjang, tidak ada enum constraint)
            $table->string('type', 30)->default('highlight')->change();

            // Ubah shape_type menjadi string 30
            $table->string('shape_type', 30)->nullable()->change();

            // Ubah color menjadi string 30 (untuk pink, purple, cyan, dll)
            $table->string('color', 30)->default('yellow')->change();
        });
    }

    public function down(): void
    {
        Schema::table('pdf_annotations', function (Blueprint $table) {
            $table->string('type', 20)->change();
            $table->string('shape_type', 20)->nullable()->change();
            $table->string('color', 20)->default('yellow')->change();
        });
    }
};
