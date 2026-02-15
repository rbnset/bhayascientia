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
        Schema::table('users', function (Blueprint $table) {
            // Profile photo - untuk foto upload manual
            if (!Schema::hasColumn('users', 'profile_photo')) {
                $table->string('profile_photo')->nullable()->after('email_verified_at');
            }

            // WhatsApp number
            if (!Schema::hasColumn('users', 'whatsapp_number')) {
                $table->string('whatsapp_number', 20)->nullable()->after('profile_photo');
            }

            // Job title / posisi
            if (!Schema::hasColumn('users', 'job_title')) {
                $table->string('job_title', 100)->nullable()->after('whatsapp_number');
            }

            // Username
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 50)->nullable()->after('job_title');
            }

            // Bio
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('username');
            }

            // Affiliation / institusi
            if (!Schema::hasColumn('users', 'affiliation')) {
                $table->string('affiliation', 255)->nullable()->after('bio');
            }

            // Google ID untuk social login
            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->after('affiliation');
            }

            // Facebook ID untuk social login
            if (!Schema::hasColumn('users', 'facebook_id')) {
                $table->string('facebook_id')->nullable()->after('google_id');
            }

            // Avatar - URL dari social login (Google/Facebook)
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('facebook_id');
            }

            // Provider - google, facebook, atau manual
            if (!Schema::hasColumn('users', 'provider')) {
                $table->string('provider', 20)->nullable()->default('manual')->after('avatar');
            }
        });

        // Tambahkan unique index untuk username jika kolom ada
        if (Schema::hasColumn('users', 'username')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->unique('username');
                });
            } catch (\Exception $e) {
                // Index sudah ada, skip
            }
        }

        // Tambahkan index untuk google_id dan facebook_id
        if (Schema::hasColumn('users', 'google_id')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->index('google_id');
                });
            } catch (\Exception $e) {
                // Index sudah ada, skip
            }
        }

        if (Schema::hasColumn('users', 'facebook_id')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->index('facebook_id');
                });
            } catch (\Exception $e) {
                // Index sudah ada, skip
            }
        }
    }

    /**
     * Rollback the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes terlebih dahulu (dengan try-catch untuk menghindari error)
            try {
                $table->dropUnique(['username']);
            } catch (\Exception $e) {
                // Index tidak ada, skip
            }

            try {
                $table->dropIndex(['google_id']);
            } catch (\Exception $e) {
                // Index tidak ada, skip
            }

            try {
                $table->dropIndex(['facebook_id']);
            } catch (\Exception $e) {
                // Index tidak ada, skip
            }

            // Drop kolom
            $columns = [
                'profile_photo',
                'whatsapp_number',
                'job_title',
                'username',
                'bio',
                'affiliation',
                'google_id',
                'facebook_id',
                'avatar',
                'provider',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
