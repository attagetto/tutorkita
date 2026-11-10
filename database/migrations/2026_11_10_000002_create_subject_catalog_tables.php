<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->string('category', 60);
            $table->text('description');
            $table->string('icon', 40)->default('book');
            $table->string('tone', 20)->default('blue');
            $table->timestamps();
        });

        Schema::create('grade_levels', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 40)->unique();
            $table->string('slug', 50)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('grade_level_subject', function (Blueprint $table): void {
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained()->cascadeOnDelete();
            $table->primary(['subject_id', 'grade_level_id']);
            $table->timestamps();
        });

        Schema::create('student_subjects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            // Accounts are stored in LocalUserStore (JSON), so there is no users FK.
            $table->string('student_id')->index();
            $table->timestamps();
            $table->unique(['student_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_subjects');
        Schema::dropIfExists('grade_level_subject');
        Schema::dropIfExists('grade_levels');
        Schema::dropIfExists('subjects');
    }
};
