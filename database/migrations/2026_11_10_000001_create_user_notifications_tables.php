<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table): void {
            $table->id();
            // Authentication is currently backed by LocalUserStore, so account IDs are strings without a users FK.
            $table->string('user_id')->index();
            $table->string('notification_key', 190);
            $table->string('category', 30)->index();
            $table->string('title', 180);
            $table->text('body');
            $table->string('action_url', 255)->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
            $table->unique(['user_id', 'notification_key']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('user_notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->string('user_id')->unique();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
        Schema::dropIfExists('user_notifications');
    }
};
