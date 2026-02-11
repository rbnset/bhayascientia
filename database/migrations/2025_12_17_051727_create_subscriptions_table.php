<?php
// database/migrations/2026_02_11_create_subscriptions_table.php (Updated)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->json('types')->nullable(); // ['jurnal', 'opini', 'buku']
            $table->json('categories')->nullable(); // Array of category IDs

            $table->enum('notification_type', [
                'instant',
                'daily',
                'weekly_new',
                'weekly_popular',
                'monthly_popular'
            ])->default('weekly_new');

            // ✅ Anti-Spam Protection
            $table->integer('max_emails_per_day')->default(3);
            $table->integer('emails_sent_today')->default(0);
            $table->date('last_email_date')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('subscribed_at')->useCurrent();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
