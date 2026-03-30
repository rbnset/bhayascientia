<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table) {

            // ── Apakah sudah pernah diterbitkan di platform/tempat lain? ──
            $table->boolean('is_previously_published')
                ->default(false)
                ->after('cover_image_path')
                ->comment('Apakah karya ini sudah pernah diterbitkan di platform/tempat lain?');

            // ── Di mana diterbitkan sebelumnya? (nama platform/penerbit) ──
            $table->string('prior_publisher_name', 255)
                ->nullable()
                ->after('is_previously_published')
                ->comment('Nama platform/penerbit sebelumnya, mis: ResearchGate, Google Scholar, Elsevier');

            // ── URL/Link platform sebelumnya ──
            $table->string('prior_publisher_url', 500)
                ->nullable()
                ->after('prior_publisher_name')
                ->comment('URL lengkap publikasi sebelumnya');

            // ── Identifier spesifik per tipe (DOI / ISBN / Nomor Opini) ──
            // Jurnal  → DOI  (mis: 10.1016/j.xxx.2024.01.001)
            // Buku    → ISBN (mis: 978-602-XXXX-XX-X)
            // Opini   → Nama media tempat terbit (mis: Kompas, Tempo)
            $table->string('prior_identifier_type', 50)
                ->nullable()
                ->after('prior_publisher_url')
                ->comment('Tipe identifier: doi | isbn | media_name');

            $table->string('prior_identifier_value', 255)
                ->nullable()
                ->after('prior_identifier_type')
                ->comment('Nilai identifier, mis: 10.xxxx/yyy atau 978-xxx-xxx');

            // ── Apakah karya ini berstatus Open Access di tempat sebelumnya? ──
            $table->boolean('is_open_access_origin')
                ->default(false)
                ->nullable()
                ->after('prior_identifier_value')
                ->comment('Apakah di tempat asalnya sudah Open Access?');

            // ── Lisensi Open Access dari sumber asli (mis: CC BY 4.0) ──
            $table->string('origin_license', 100)
                ->nullable()
                ->after('is_open_access_origin')
                ->comment('Lisensi OA dari sumber asli, mis: CC BY 4.0, CC BY-NC 4.0');

            // ── Tanggal pertama kali diterbitkan di tempat sebelumnya ──
            $table->date('prior_published_date')
                ->nullable()
                ->after('origin_license')
                ->comment('Tanggal pertama terbit di platform sebelumnya');

            // ══════════════════════════════════════════════════════════════
            // LOA — Letter of Agreement / Pernyataan Hak Cipta
            // ══════════════════════════════════════════════════════════════

            // Penulis menyatakan bahwa dia adalah pemilik hak cipta
            $table->boolean('loa_is_original_work')
                ->default(false)
                ->after('prior_published_date')
                ->comment('Penulis menyatakan ini adalah karya orisinal miliknya');

            // Penulis memberikan izin platform untuk menampilkan karya
            $table->boolean('loa_grants_display_rights')
                ->default(false)
                ->after('loa_is_original_work')
                ->comment('Penulis memberikan izin platform untuk menampilkan karya');

            // Penulis memahami platform tidak bertanggung jawab atas klaim pihak ketiga
            $table->boolean('loa_platform_not_liable')
                ->default(false)
                ->after('loa_grants_display_rights')
                ->comment('Penulis memahami platform tidak bertanggung jawab atas klaim pihak ketiga');

            // Penulis menyetujui bahwa karya dapat dihapus admin kapan saja jika ada laporan
            $table->boolean('loa_agrees_takedown_policy')
                ->default(false)
                ->after('loa_platform_not_liable')
                ->comment('Penulis menyetujui kebijakan takedown jika ada laporan pelanggaran');

            // Penulis menyetujui seluruh isi LOA
            $table->boolean('loa_agreed')
                ->default(false)
                ->after('loa_agrees_takedown_policy')
                ->comment('Penulis telah membaca dan menyetujui seluruh LOA');

            // Timestamp kapan LOA disetujui (untuk bukti hukum)
            $table->timestamp('loa_agreed_at')
                ->nullable()
                ->after('loa_agreed')
                ->comment('Waktu persetujuan LOA — disimpan sebagai bukti hukum');

            // IP address saat menyetujui LOA (untuk bukti hukum)
            $table->string('loa_agreed_ip', 45)
                ->nullable()
                ->after('loa_agreed_at')
                ->comment('IP address saat menyetujui LOA — IPv4/IPv6');

            // User agent browser saat menyetujui LOA
            $table->string('loa_agreed_user_agent', 500)
                ->nullable()
                ->after('loa_agreed_ip')
                ->comment('User agent browser saat menyetujui LOA');
        });
    }

    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropColumn([
                'is_previously_published',
                'prior_publisher_name',
                'prior_publisher_url',
                'prior_identifier_type',
                'prior_identifier_value',
                'is_open_access_origin',
                'origin_license',
                'prior_published_date',
                'loa_is_original_work',
                'loa_grants_display_rights',
                'loa_platform_not_liable',
                'loa_agrees_takedown_policy',
                'loa_agreed',
                'loa_agreed_at',
                'loa_agreed_ip',
                'loa_agreed_user_agent',
            ]);
        });
    }
};
