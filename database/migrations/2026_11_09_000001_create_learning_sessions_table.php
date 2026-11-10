<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_sessions', function (Blueprint $table): void {
            $table->id();
            // Auth accounts are currently stored in LocalUserStore, so there is no users FK.
            $table->string('student_id')->index();
            $table->string('tutor_name');
            $table->string('tutor_university')->nullable();
            $table->string('tutor_major')->nullable();
            $table->string('subject', 100);
            $table->string('topic', 180);
            $table->string('grade_level', 30);
            $table->dateTime('starts_at')->index();
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->string('meeting_platform', 60)->default('Google Meet');
            $table->text('meeting_url')->nullable();
            $table->string('status', 24)->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_sessions');
    }
};
