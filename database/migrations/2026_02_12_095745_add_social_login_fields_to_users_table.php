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
            // ✅ Social Login Fields
            $table->string('google_id')->nullable()->unique()->after('email');
            $table->string('facebook_id')->nullable()->unique()->after('google_id');
            $table->string('avatar')->nullable()->after('profile_photo'); // Avatar dari social media
            $table->string('provider')->nullable()->after('avatar'); // 'google', 'facebook', 'manual'

            // ✅ Make password nullable (untuk user yang login via social media)
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ✅ Remove social login fields
            $table->dropColumn([
                'google_id',
                'facebook_id',
                'avatar',
                'provider',
            ]);

            // ✅ Make password required again
            $table->string('password')->nullable(false)->change();
        });
    }
};
