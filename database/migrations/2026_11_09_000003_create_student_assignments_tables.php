<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_assignments', function (Blueprint $table): void {
            $table->id();
            // Student and tutor accounts currently live in the protected JSON user store.
            $table->string('student_id')->index();
            $table->string('tutor_id')->nullable()->index();
            $table->string('tutor_name', 120);
            $table->string('title', 180);
            $table->string('subject', 100)->index();
            $table->text('instructions');
            $table->timestamp('due_at')->index();
            $table->boolean('accepts_text')->default(true);
            $table->boolean('accepts_file')->default(true);
            $table->boolean('allow_resubmission')->default(false);
            $table->unsignedInteger('max_file_kilobytes')->default(10240);
            $table->timestamps();
        });

        Schema::create('assignment_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_assignment_id')->constrained('student_assignments')->cascadeOnDelete();
            $table->string('student_id')->index();
            $table->text('answer_text')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->boolean('is_late')->default(false);
            $table->decimal('score', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->string('graded_by_tutor_id')->nullable()->index();
            $table->timestamps();
            $table->unique(['student_assignment_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('student_assignments');
    }
};
