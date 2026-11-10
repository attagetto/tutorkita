<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_materials', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('subject', 100)->index();
            $table->string('topic', 120)->nullable();
            $table->string('grade_level', 30)->index();
            $table->string('tutor_name', 120);
            $table->string('material_type', 30);
            $table->text('description');
            $table->string('illustration', 40);
            $table->string('file_path')->nullable();
            $table->string('video_url')->nullable();
            $table->unsignedSmallInteger('page_count')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_published')->default(true)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('material_bookmarks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learning_material_id')->constrained('learning_materials')->cascadeOnDelete();
            // App accounts currently live in a JSON store, so owner identity is kept without a users FK.
            $table->string('student_id')->index();
            $table->timestamps();
            $table->unique(['learning_material_id', 'student_id']);
        });

        Schema::create('material_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learning_material_id')->constrained('learning_materials')->cascadeOnDelete();
            $table->string('student_id')->index();
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['learning_material_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_progress');
        Schema::dropIfExists('material_bookmarks');
        Schema::dropIfExists('learning_materials');
    }
};
