<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('download_logs', function (Blueprint $table) {
            // ✅ Cek dan tambah ip_address jika belum ada
            if (!Schema::hasColumn('download_logs', 'ip_address')) {
                $table->string('ip_address', 45)->after('user_id')->nullable();
            }

            // ✅ Cek dan tambah user_agent jika belum ada
            if (!Schema::hasColumn('download_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }

            // ✅ Cek dan tambah updated_at jika belum ada
            if (!Schema::hasColumn('download_logs', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        // ✅ Hapus kolom downloaded_at jika masih ada (karena sudah ada created_at)
        if (Schema::hasColumn('download_logs', 'downloaded_at')) {
            // Copy data dari downloaded_at ke created_at jika created_at masih null
            DB::statement('UPDATE download_logs SET created_at = downloaded_at WHERE created_at IS NULL');

            Schema::table('download_logs', function (Blueprint $table) {
                $table->dropColumn('downloaded_at');
            });
        }

        // ✅ Tambah indexes untuk performa
        Schema::table('download_logs', function (Blueprint $table) {
            if (!$this->indexExists('download_logs', 'download_logs_publication_id_created_at_index')) {
                $table->index(['publication_id', 'created_at']);
            }
            if (!$this->indexExists('download_logs', 'download_logs_user_id_index')) {
                $table->index('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('download_logs', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['publication_id', 'created_at']);
            $table->dropIndex(['user_id']);

            // Restore downloaded_at column
            if (!Schema::hasColumn('download_logs', 'downloaded_at')) {
                $table->timestamp('downloaded_at')->nullable();
            }
        });

        // Copy data back
        DB::statement('UPDATE download_logs SET downloaded_at = created_at WHERE downloaded_at IS NULL');
    }

    /**
     * ✅ Helper: Check if index exists
     */
    private function indexExists($table, $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");
        return !empty($indexes);
    }
};
